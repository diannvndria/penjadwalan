<?php

namespace App\Http\Controllers;

use App\Models\HistoriMunaqosah;       // Import model Munaqosah
use App\Models\JadwalPenguji;      // Import model Mahasiswa
use App\Models\Mahasiswa;        // Import model Penguji
use App\Models\Munaqosah;  // Import model JadwalPenguji (untuk cek bentrok non-munaqosah)
use App\Models\Penguji; // Import model HistoriMunaqosah
use App\Models\RuangUjian;
use Carbon\Carbon;
use Illuminate\Http\Request;  // Untuk manipulasi tanggal dan waktu
use Illuminate\Support\Facades\DB;
use PDF;  // Untuk transaksi database (DB::transaction)

class MunaqosahController extends Controller
{
    /**
     * Menampilkan daftar jadwal munaqosah.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        // Sorting parameters
        $sortField = $request->input('sort');
        $sortDirection = $request->input('direction', 'asc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['mahasiswa_nama', 'tanggal_munaqosah', 'waktu_mulai'];
        if ($sortField && ! in_array($sortField, $allowedSortFields)) {
            $sortField = null;
        }

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        // Build query with proper join for sorting
        $query = Munaqosah::query();

        // Apply filters
        if ($startDate) {
            $query->whereDate('tanggal_munaqosah', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_munaqosah', '<=', $endDate);
        }

        if ($status) {
            $query->where('status_konfirmasi', $status);
        }

        // Apply sorting with join if needed
        if ($sortField === 'mahasiswa_nama') {
            $query->leftJoin('mahasiswa', 'munaqosah.id_mahasiswa', '=', 'mahasiswa.id')
                ->select('munaqosah.*')
                ->orderBy('mahasiswa.nama', $sortDirection)
                ->orderBy('munaqosah.tanggal_munaqosah', 'asc')
                ->orderBy('munaqosah.waktu_mulai', 'asc');
        } elseif ($sortField === 'tanggal_munaqosah') {
            $query->orderBy('tanggal_munaqosah', $sortDirection)
                ->orderBy('waktu_mulai', 'asc');
        } elseif ($sortField === 'waktu_mulai') {
            $query->orderBy('waktu_mulai', $sortDirection)
                ->orderBy('tanggal_munaqosah', 'asc');
        } else {
            // Default ordering when no sort is applied
            $query->orderBy('tanggal_munaqosah', 'asc')
                ->orderBy('waktu_mulai', 'asc');
        }

        // Get all matching IDs before pagination for "Select All" functionality
        $allIds = (clone $query)->pluck('munaqosah.id')->map(fn ($id) => (string) $id)->toArray();

        // Paginate and load relationships
        $munaqosahs = $query->paginate(10);
        $munaqosahs->load('mahasiswa', 'penguji1', 'penguji2', 'ruangUjian');

        return view('munaqosah.index', [
            'munaqosahs' => $munaqosahs,
            'allIds' => $allIds,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * Download laporan jadwal sidang sebagai PDF
     */
    public function downloadReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Munaqosah::with('mahasiswa', 'penguji1', 'penguji2', 'ruangUjian');

        if ($startDate) {
            $query->whereDate('tanggal_munaqosah', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_munaqosah', '<=', $endDate);
        }

        $munaqosahs = $query->orderBy('tanggal_munaqosah')->orderBy('waktu_mulai')->get();
        $totalJadwal = $munaqosahs->count();

        $pdf = PDF::loadView('munaqosah.laporan', [
            'munaqosahs' => $munaqosahs,
            'totalJadwal' => $totalJadwal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now(),
        ]);

        $filename = 'Laporan_Jadwal_Sidang_'.Carbon::now()->format('d-m-Y_H-i-s').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Menampilkan form untuk membuat jadwal munaqosah baru.
     */
    public function create()
    {
        $mahasiswasSiapSidang = Mahasiswa::where('siap_sidang', true)
            ->doesntHave('munaqosah')
            ->with('dospem')
            ->get();
        $pengujis = Penguji::all();
        $ruangUjians = RuangUjian::where('is_aktif', true)->orderBy('nama')->get();

        return view('munaqosah.create', compact('mahasiswasSiapSidang', 'pengujis', 'ruangUjians'));
    }

    /**
     * Menyimpan data jadwal munaqosah baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_mahasiswa' => 'required|exists:mahasiswa,id|unique:munaqosah,id_mahasiswa',
            'tanggal_munaqosah' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'id_penguji1' => 'required|exists:penguji,id',
            'id_penguji2' => 'nullable|exists:penguji,id|different:id_penguji1',
            'id_ruang_ujian' => 'required|exists:ruang_ujian,id',
        ]);

        $tanggal = Carbon::parse($request->tanggal_munaqosah)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        $pengujiIds = array_filter(array_unique([
            $request->id_penguji1,
            $request->id_penguji2,
        ]));

        // OPTIMIZATION: Fetch all pengujis at once instead of one by one
        $pengujis = Penguji::whereIn('id', $pengujiIds)->pluck('nama', 'id');

        foreach ($pengujiIds as $pengujiId) {
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)) {
                $pengujiNama = $pengujis[$pengujiId] ?? 'Penguji';

                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        if ($this->checkRuangConflict($request->id_ruang_ujian, $tanggal, $waktuMulai, $waktuSelesai)) {
            // OPTIMIZATION: Only fetch room name if there's a conflict
            $ruangNama = RuangUjian::where('id', $request->id_ruang_ujian)->value('nama') ?? 'Ruang';

            return back()->withInput()->withErrors(['bentrok' => "{$ruangNama} sudah terpakai pada waktu tersebut."]);
        }

        DB::transaction(function () use ($request) {
            $munaqosah = Munaqosah::create([
                'id_mahasiswa' => $request->id_mahasiswa,
                'tanggal_munaqosah' => $request->tanggal_munaqosah,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'id_penguji1' => $request->id_penguji1,
                'id_penguji2' => $request->id_penguji2,
                'id_ruang_ujian' => $request->id_ruang_ujian,
                'status_konfirmasi' => 'pending',
            ]);

            HistoriMunaqosah::create([
                'id_munaqosah' => $munaqosah->id,
                'perubahan' => 'Jadwal munaqosah baru dibuat.',
                'dilakukan_oleh' => auth()->id(),
            ]);
        });

        return redirect()->route('munaqosah.index')->with('success', 'Jadwal munaqosah berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit jadwal munaqosah yang sudah ada.
     */
    public function edit(Munaqosah $munaqosah)
    {
        $mahasiswasSiapSidang = Mahasiswa::where('siap_sidang', true)
            ->orWhere('id', $munaqosah->id_mahasiswa)
            ->with('dospem')
            ->get();
        $pengujis = Penguji::all();
        $ruangUjians = RuangUjian::where('is_aktif', true)->orderBy('nama')->get();

        return view('munaqosah.edit', compact('munaqosah', 'mahasiswasSiapSidang', 'pengujis', 'ruangUjians'));
    }

    /**
     * Memperbarui data jadwal munaqosah di database.
     */
    public function update(Request $request, Munaqosah $munaqosah)
    {
        $originalData = $munaqosah->getOriginal();

        $request->validate([
            'id_mahasiswa' => 'required|exists:mahasiswa,id|unique:munaqosah,id_mahasiswa,'.$munaqosah->id,
            'tanggal_munaqosah' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'id_penguji1' => 'required|exists:penguji,id',
            'id_penguji2' => 'nullable|exists:penguji,id|different:id_penguji1',
            'id_ruang_ujian' => 'required|exists:ruang_ujian,id',
            'status_konfirmasi' => 'required|in:pending,dikonfirmasi,ditolak',
        ]);

        $tanggal = Carbon::parse($request->tanggal_munaqosah)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        $pengujiIds = array_filter(array_unique([
            $request->id_penguji1,
            $request->id_penguji2,
        ]));

        // OPTIMIZATION: Fetch all pengujis at once instead of one by one
        $pengujis = Penguji::whereIn('id', $pengujiIds)->pluck('nama', 'id');

        foreach ($pengujiIds as $pengujiId) {
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $munaqosah->id)) {
                $pengujiNama = $pengujis[$pengujiId] ?? 'Penguji';

                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        if ($this->checkRuangConflict($request->id_ruang_ujian, $tanggal, $waktuMulai, $waktuSelesai, $munaqosah->id)) {
            // OPTIMIZATION: Only fetch room name if there's a conflict
            $ruangNama = RuangUjian::where('id', $request->id_ruang_ujian)->value('nama') ?? 'Ruang';

            return back()->withInput()->withErrors(['bentrok' => "{$ruangNama} sudah terpakai pada waktu tersebut."]);
        }

        DB::transaction(function () use ($request, $munaqosah, $originalData) {
            $munaqosah->update([
                'id_mahasiswa' => $request->id_mahasiswa,
                'tanggal_munaqosah' => $request->tanggal_munaqosah,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'id_penguji1' => $request->id_penguji1,
                'id_penguji2' => $request->id_penguji2,
                'id_ruang_ujian' => $request->id_ruang_ujian,
                'status_konfirmasi' => $request->status_konfirmasi,
            ]);

            $changes = [];
            foreach ($request->except('_token', '_method') as $key => $value) {
                if (array_key_exists($key, $originalData) && $value != $originalData[$key]) {
                    if (in_array($key, ['id_mahasiswa', 'id_penguji1', 'id_penguji2', 'id_ruang_ujian'])) {
                        // Convert snake_case to PascalCase (e.g., 'ruang_ujian' -> 'RuangUjian')
                        // Also strip trailing numbers (e.g., 'penguji1' -> 'penguji' -> 'Penguji')
                        $modelName = str_replace('id_', '', $key);
                        $modelName = preg_replace('/\d+$/', '', $modelName); // Remove trailing numbers
                        $modelClass = 'App\\Models\\'.str_replace('_', '', ucwords($modelName, '_'));
                        $oldValueName = $originalData[$key] ? ($modelClass::find($originalData[$key])->nama ?? 'N/A') : 'Kosong';
                        $newValueName = $value ? ($modelClass::find($value)->nama ?? 'N/A') : 'Kosong';
                        $changes[] = ucfirst(str_replace('id_', '', $key)).": '$oldValueName' menjadi '$newValueName'";
                    } elseif ($key === 'status_konfirmasi') {
                        $changes[] = ucfirst(str_replace('_', ' ', $key)).": '".ucfirst($originalData[$key])."' menjadi '".ucfirst($value)."'";
                    } else {
                        $changes[] = ucfirst(str_replace('_', ' ', $key)).": '$originalData[$key]' menjadi '$value'";
                    }
                }
            }

            if (! empty($changes)) {
                HistoriMunaqosah::create([
                    'id_munaqosah' => $munaqosah->id,
                    'perubahan' => 'Jadwal munaqosah diperbarui: '.implode(', ', $changes),
                    'dilakukan_oleh' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('munaqosah.index')->with('success', 'Jadwal munaqosah berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal munaqosah dari database.
     */
    public function destroy(Munaqosah $munaqosah)
    {
        DB::transaction(function () use ($munaqosah) {
            $mahasiswaNama = $munaqosah->mahasiswa->nama ?? 'Nama tidak diketahui';
            $mahasiswaNIM = $munaqosah->mahasiswa->nim ?? 'NIM tidak diketahui';

            $munaqosah->delete();

            HistoriMunaqosah::create([
                'id_munaqosah' => null,
                'perubahan' => 'Jadwal munaqosah untuk mahasiswa '.$mahasiswaNama.' (NIM: '.$mahasiswaNIM.') telah dihapus.',
                'dilakukan_oleh' => auth()->id(),
            ]);
        });

        return redirect()->route('munaqosah.index')->with('success', 'Jadwal munaqosah berhasil dihapus.');
    }

    /**
     * Menghapus beberapa jadwal munaqosah sekaligus (Bulk Delete).
     */
    public function bulkDestroy(Request $request)
    {
        $ids = explode(',', $request->input('ids'));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        DB::transaction(function () use ($ids) {
            $munaqosahs = Munaqosah::whereIn('id', $ids)->with('mahasiswa')->get();

            foreach ($munaqosahs as $munaqosah) {
                $mahasiswaNama = $munaqosah->mahasiswa->nama ?? 'Nama tidak diketahui';
                $mahasiswaNIM = $munaqosah->mahasiswa->nim ?? 'NIM tidak diketahui';

                $munaqosah->delete();

                HistoriMunaqosah::create([
                    'id_munaqosah' => null,
                    'perubahan' => 'Jadwal munaqosah untuk mahasiswa '.$mahasiswaNama.' (NIM: '.$mahasiswaNIM.') telah dihapus (Bulk Delete).',
                    'dilakukan_oleh' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('munaqosah.index')->with('success', count($ids).' Jadwal munaqosah berhasil dihapus.');
    }

    /**
     * Export beberapa jadwal munaqosah ke Excel (CSV).
     */
    public function bulkExport(Request $request)
    {
        $ids = explode(',', $request->input('ids'));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $munaqosahs = Munaqosah::whereIn('id', $ids)
            ->with(['mahasiswa', 'penguji1', 'penguji2', 'ruangUjian'])
            ->orderBy('tanggal_munaqosah')
            ->orderBy('waktu_mulai')
            ->get();

        $filename = 'Jadwal_Sidang_'.Carbon::now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($munaqosahs) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'Nama Mahasiswa',
                'NIM',
                'Tanggal',
                'Waktu Mulai',
                'Waktu Selesai',
                'Penguji 1',
                'Penguji 2',
                'Ruang',
                'Status',
            ]);

            foreach ($munaqosahs as $item) {
                fputcsv($file, [
                    $item->mahasiswa->nama ?? '-',
                    $item->mahasiswa->nim ?? '-',
                    Carbon::parse($item->tanggal_munaqosah)->format('d-m-Y'),
                    substr($item->waktu_mulai, 0, 5),
                    substr($item->waktu_selesai, 0, 5),
                    $item->penguji1->nama ?? '-',
                    $item->penguji2->nama ?? '-',
                    $item->ruangUjian->nama ?? '-',
                    ucfirst($item->status_konfirmasi),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menampilkan histori perubahan untuk jadwal munaqosah tertentu.
     */
    public function histori(Munaqosah $munaqosah)
    {
        $histories = $munaqosah->historiPerubahan()->with('user')->orderBy('created_at', 'desc')->get();

        return view('munaqosah.histori', compact('munaqosah', 'histories'));
    }

    /**
     * Metode pembantu (private) untuk mengecek bentrok jadwal penguji.
     * OPTIMIZED: Combines both queries into a single query using UNION
     */
    private function checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $excludeMunaqosahId = null)
    {
        // Combine both queries into a single optimized query with UNION
        // IMPORTANT: Must select same columns in both UNION queries
        $query = DB::table('jadwal_penguji')
            ->select(DB::raw('1'))
            ->where('id_penguji', $pengujiId)
            ->whereDate('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->union(
                DB::table('munaqosah')
                    ->select(DB::raw('1'))
                    ->where(function ($query) use ($pengujiId) {
                        $query->where('id_penguji1', $pengujiId)
                            ->orWhere('id_penguji2', $pengujiId);
                    })
                    ->whereDate('tanggal_munaqosah', $tanggal)
                    ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                        $query->where('waktu_mulai', '<', $waktuSelesai)
                            ->where('waktu_selesai', '>', $waktuMulai);
                    })
                    ->when($excludeMunaqosahId, function ($query) use ($excludeMunaqosahId) {
                        $query->where('id', '!=', $excludeMunaqosahId);
                    })
            );

        return $query->exists();
    }

    private function checkRuangConflict($ruangId, $tanggal, $waktuMulai, $waktuSelesai, $excludeMunaqosahId = null)
    {
        $query = Munaqosah::where('id_ruang_ujian', $ruangId)
            ->whereDate('tanggal_munaqosah', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        if ($excludeMunaqosahId) {
            $query->where('id', '!=', $excludeMunaqosahId);
        }

        return $query->exists();
    }
}
