<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\JadwalPenguji;
use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa, dengan opsi filter angkatan.
     */
    public function index(Request $request)
    {
        // Ambil semua data dosen untuk dropdown filter
        $dosens = Dosen::orderBy('nama')->get();

        // 1. PERBAIKAN NAMA VARIABEL: Ubah nama variabel menjadi 'angkatans_tersedia'
        $angkatans_tersedia = Mahasiswa::select('angkatan')->distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

        // 2. PERBAIKAN LOGIKA: Ambil nilai angkatan yang sedang difilter dari request
        $angkatan = $request->input('angkatan');

        // Sorting parameters
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['nim', 'nama', 'angkatan', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $query = Mahasiswa::with('dospem');

        // Apply sorting
        $query->orderBy($sortField, $sortDirection);

        // Terapkan filter pencarian jika ada
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('nim', 'like', '%'.$request->search.'%');
            });
        }

        // Terapkan filter angkatan jika ada (menggunakan variabel $angkatan)
        if ($angkatan) {
            $query->where('angkatan', $angkatan);
        }

        // Terapkan filter dosen pembimbing jika ada
        if ($request->filled('dospem_id')) {
            $query->where('id_dospem', $request->dospem_id);
        }

        // Terapkan filter status sidang jika ada (siap_sidang is boolean: 1=Siap, 0=Belum)
        if ($request->filled('status_sidang')) {
            $siapSidang = $request->status_sidang === 'Siap' ? true : false;
            $query->where('siap_sidang', $siapSidang);
        }

        // Get all IDs for bulk operations (before pagination)
        $allIds = (clone $query)->pluck('id')->toArray();

        // Then paginate
        $mahasiswas = $query->paginate(10);

        // 3. PERBAIKAN PENGIRIMAN DATA: Kirim semua variabel yang dibutuhkan oleh view
        return view('mahasiswa.index', compact(
            'mahasiswas',
            'angkatans_tersedia',
            'dosens',
            'angkatan',
            'sortField',
            'sortDirection',
            'allIds'
        ));
    }

    /**
     * Menampilkan form untuk membuat mahasiswa baru.
     */
    public function create()
    {
        $dosens = Dosen::all(); // Ambil semua dosen untuk dropdown pilihan dosen pembimbing

        return view('mahasiswa.create', compact('dosens'));
    }

    /**
     * Menyimpan data mahasiswa baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:'.date('Y'),
            'judul_skripsi' => 'required|string',
            'profil_lulusan' => 'nullable|string|in:Ilmuwan,Wirausaha,Profesional', // Validasi profil lulusan
            'penjurusan' => 'nullable|string|in:Sistem Informasi,Perekayasa Perangkat Lunak,Perekayasa Jaringan Komputer,Sistem Cerdas', // Validasi penjurusan
            'id_dospem' => 'required|exists:dosen,id',
            'siap_sidang' => 'boolean',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
        ]);

        // Pastikan nilai boolean selalu terisi meskipun checkbox tidak dicentang
        $validated['siap_sidang'] = $request->boolean('siap_sidang');
        $validated['is_prioritas'] = $request->boolean('is_prioritas');

        $dosen = Dosen::find($request->id_dospem);
        if ($dosen->kapasitas_ampu > 0 && $dosen->mahasiswas()->count() >= $dosen->kapasitas_ampu) {
            return back()->withInput()->withErrors(['id_dospem' => 'Dosen pembimbing ini sudah mencapai kapasitas maksimal. Pilih dosen lain atau perbarui kapasitas dosen.']);
        }

        Mahasiswa::create($validated);

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data mahasiswa.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $dosens = Dosen::all();

        return view('mahasiswa.edit', compact('mahasiswa', 'dosens'));
    }

    /**
     * Memperbarui data mahasiswa di database.
     * Termasuk logika pemicu penjadwalan otomatis jika status siap_sidang berubah (jika diaktifkan).
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $oldSiapSidang = $mahasiswa->siap_sidang;

        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswa,nim,'.$mahasiswa->id,
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:'.date('Y'),
            'judul_skripsi' => 'required|string',
            'profil_lulusan' => 'nullable|string|in:Ilmuwan,Wirausaha,Profesional',
            'penjurusan' => 'nullable|string|in:Sistem Informasi,Perekayasa Perangkat Lunak,Perekayasa Jaringan Komputer,Sistem Cerdas',
            'id_dospem' => 'required|exists:dosen,id',
            'siap_sidang' => 'boolean',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
        ]);

        // Pastikan nilai boolean selalu terisi meskipun checkbox tidak dicentang
        $validated['siap_sidang'] = $request->boolean('siap_sidang');
        $validated['is_prioritas'] = $request->boolean('is_prioritas');

        if ($mahasiswa->id_dospem != $request->id_dospem) {
            $newDospem = Dosen::find($request->id_dospem);
            if ($newDospem->kapasitas_ampu > 0 && $newDospem->mahasiswas()->count() >= $newDospem->kapasitas_ampu) {
                return back()->withInput()->withErrors(['id_dospem' => 'Dosen pembimbing baru ini sudah mencapai kapasitas maksimal. Pilih dosen lain atau perbarui kapasitas dosen.']);
            }
        }

        DB::transaction(function () use ($validated, $mahasiswa) {
            $mahasiswa->update($validated);

            // Logika penjadwalan otomatis (jika ada):
            // Pastikan Anda telah mengimplementasikan metode findAvailableMunaqosahSlot dan checkPengujiConflict di controller ini
            // atau di service terpisah.
            /*
            if ($request->boolean('siap_sidang') && !$oldSiapSidang && !$mahasiswa->munaqosah) {
                $pesanOtomatisasi = '';
                try {
                    $jadwalTersedia = $this->findAvailableMunaqosahSlot($mahasiswa);

                    if ($jadwalTersedia) {
                        Munaqosah::create([
                            'id_mahasiswa' => $mahasiswa->id,
                            'tanggal_munaqosah' => $jadwalTersedia['tanggal'],
                            'waktu_mulai' => $jadwalTersedia['waktu_mulai'],
                            'waktu_selesai' => $jadwalTersedia['waktu_selesai'],
                            'id_penguji1' => $jadwalTersedia['penguji1']->id,
                            'id_penguji2' => $jadwalTersedia['penguji2']->id,
                            'status_konfirmasi' => 'pending',
                        ]);
                        $pesanOtomatisasi = ' Jadwal munaqosah otomatis berhasil dibuat.';
                    } else {
                        $pesanOtomatisasi = ' Tidak dapat menemukan slot jadwal munaqosah otomatis yang tersedia. Silakan jadwalkan secara manual.';
                    }
                } catch (\Exception $e) {
                    $pesanOtomatisasi = ' Gagal membuat jadwal munaqosah otomatis: ' . $e->getMessage();
                    \Log::error('Error penjadwalan otomatis mahasiswa ' . $mahasiswa->id . ': ' . $e->getMessage());
                }
                session()->flash('info', $pesanOtomatisasi);
            }
            */
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.'.(session('info') ?? ''));
    }

    /**
     * Menghapus data mahasiswa dari database.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        try {
            $mahasiswa->delete();

            return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('mahasiswa.index')->with('error', 'Tidak dapat menghapus mahasiswa karena masih memiliki jadwal munaqosah.');
        }
    }

    // Metode findAvailableMunaqosahSlot dan checkPengujiConflict harus ada jika auto-schedule diaktifkan
    // Saya akan menyertakan kode metode ini di bagian bawah, di luar konteks store/update/destroy
    // untuk menjaga modularitas.

    /**
     * Metode pembantu (private) untuk menemukan slot munaqosah yang tersedia secara otomatis.
     * Algoritma mencari kombinasi tanggal, waktu, dan 2 penguji yang tidak bentrok.
     */
    private function findAvailableMunaqosahSlot(Mahasiswa $mahasiswa)
    {
        $durationInMinutes = 90;
        $maxDaysAhead = 30;
        $startTimeLimit = '08:00';
        $endTimeLimit = '17:00';

        $allPengujis = Penguji::all();
        if ($allPengujis->count() < 2) {
            throw new \Exception('Tidak cukup penguji terdaftar (minimal 2) untuk menjadwalkan munaqosah otomatis.');
        }

        for ($i = 0; $i <= $maxDaysAhead; $i++) {
            $currentDate = Carbon::now()->addDays($i)->format('Y-m-d');

            $currentTime = Carbon::parse($currentDate.' '.$startTimeLimit);
            $endOfDay = Carbon::parse($currentDate.' '.$endTimeLimit);

            while ($currentTime->addMinutes($durationInMinutes)->lessThanOrEqualTo($endOfDay)) {
                $slotStart = $currentTime->copy()->subMinutes($durationInMinutes);
                $slotEnd = $currentTime->copy();

                $availablePengujisForSlot = collect();

                foreach ($allPengujis as $penguji) {
                    $isBentrok = $this->checkPengujiConflict(
                        $penguji->id,
                        $currentDate,
                        $slotStart->format('H:i'),
                        $slotEnd->format('H:i')
                    );

                    if (! $isBentrok) {
                        $availablePengujisForSlot->push($penguji);
                    }
                }

                if ($availablePengujisForSlot->count() >= 2) {
                    $penguji1 = $availablePengujisForSlot->shift();
                    $penguji2 = $availablePengujisForSlot->shift();

                    return [
                        'tanggal' => $currentDate,
                        'waktu_mulai' => $slotStart->format('H:i'),
                        'waktu_selesai' => $slotEnd->format('H:i'),
                        'penguji1' => $penguji1,
                        'penguji2' => $penguji2,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Metode pembantu (private) untuk mengecek bentrok jadwal penguji.
     */
    private function checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $excludeMunaqosahId = null)
    {
        // Cek bentrok di tabel jadwal_pengujis (jadwal non-munaqosah penguji)
        $isBentrokJadwalPenguji = JadwalPenguji::where('id_penguji', $pengujiId)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->exists();

        if ($isBentrokJadwalPenguji) {
            return true;
        }

        // Cek bentrok di tabel munaqosahs (jadwal munaqosah penguji lain)
        $queryMunaqosah = Munaqosah::where(function ($query) use ($pengujiId) {
            $query->where('id_penguji1', $pengujiId)
                ->orWhere('id_penguji2', $pengujiId);
        })
            ->where('tanggal_munaqosah', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        if ($excludeMunaqosahId) {
            $queryMunaqosah->where('id', '!=', $excludeMunaqosahId);
        }

        if ($queryMunaqosah->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Bulk delete multiple mahasiswa.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $validated['ids']);

        try {
            $count = Mahasiswa::whereIn('id', $ids)->delete();

            return redirect()->route('mahasiswa.index')->with('success', "Berhasil menghapus {$count} data mahasiswa.");
        } catch (\Exception $e) {
            return redirect()->route('mahasiswa.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Bulk export multiple mahasiswa to CSV.
     */
    public function bulkExport(Request $request)
    {
        $ids = explode(',', $request->input('ids'));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $mahasiswas = Mahasiswa::whereIn('id', $ids)
            ->with('dospem')
            ->orderBy('nama')
            ->get();

        $filename = 'Data_Mahasiswa_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($mahasiswas) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'NIM',
                'Nama',
                'Angkatan',
                'Dosen Pembimbing',
                'Judul Skripsi',
                'Profil Lulusan',
                'Penjurusan',
                'Status Sidang',
                'Prioritas',
            ]);

            foreach ($mahasiswas as $mahasiswa) {
                fputcsv($file, [
                    $mahasiswa->nim,
                    $mahasiswa->nama,
                    $mahasiswa->angkatan,
                    $mahasiswa->dospem->nama ?? 'N/A',
                    $mahasiswa->judul_skripsi,
                    $mahasiswa->profil_lulusan ?? '-',
                    $mahasiswa->penjurusan ?? '-',
                    $mahasiswa->siap_sidang ? 'Siap' : 'Belum',
                    $mahasiswa->is_prioritas ? 'Ya' : 'Tidak',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download CSV template for importing mahasiswa.
     */
    public function downloadTemplate()
    {
        $filename = 'Template_Import_Mahasiswa.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'NIM',
                'Nama',
                'Angkatan',
                'NIP Dospem',
                'Judul Skripsi',
                'Profil Lulusan',
                'Penjurusan',
                'Siap Sidang',
                'Prioritas',
                'Keterangan Prioritas',
            ]);

            // Sample data row
            fputcsv($file, [
                '123456789',
                'Contoh Mahasiswa',
                '2020',
                '198001012000011001',
                'Sistem Informasi Berbasis Web',
                'Ilmuwan',
                'Sistem Informasi',
                '0',
                '0',
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import mahasiswa from CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));

            // Remove UTF-8 BOM if present
            if (isset($data[0][0])) {
                $data[0][0] = str_replace("\xEF\xBB\xBF", '', $data[0][0]);
            }

            // Get header row
            $header = array_shift($data);

            // Validate header
            $requiredColumns = ['NIM', 'Nama', 'Angkatan', 'NIP Dospem', 'Judul Skripsi'];
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $header)) {
                    return redirect()->route('mahasiswa.index')
                        ->with('error', "Kolom '$column' tidak ditemukan dalam file CSV. Silakan download template untuk format yang benar.");
                }
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($data as $rowIndex => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Create associative array from row data
                    $rowData = array_combine($header, $row);

                    // Trim all values
                    $rowData = array_map('trim', $rowData);

                    // Validate required fields
                    if (empty($rowData['NIM']) || empty($rowData['Nama']) || empty($rowData['Angkatan']) || empty($rowData['NIP Dospem']) || empty($rowData['Judul Skripsi'])) {
                        $errors[] = "Baris " . ($rowIndex + 2) . ": Data tidak lengkap";
                        $errorCount++;
                        continue;
                    }

                    // Find dosen by NIP
                    $dosen = Dosen::where('nip', $rowData['NIP Dospem'])->first();
                    if (!$dosen) {
                        $errors[] = "Baris " . ($rowIndex + 2) . ": Dosen dengan NIP '{$rowData['NIP Dospem']}' tidak ditemukan";
                        $errorCount++;
                        continue;
                    }

                    // Check if NIM already exists
                    if (Mahasiswa::where('nim', $rowData['NIM'])->exists()) {
                        $errors[] = "Baris " . ($rowIndex + 2) . ": NIM '{$rowData['NIM']}' sudah terdaftar";
                        $errorCount++;
                        continue;
                    }

                    // Check dosen capacity
                    if ($dosen->kapasitas_ampu > 0 && $dosen->mahasiswas()->count() >= $dosen->kapasitas_ampu) {
                        $errors[] = "Baris " . ($rowIndex + 2) . ": Dosen '{$dosen->nama}' sudah mencapai kapasitas maksimal";
                        $errorCount++;
                        continue;
                    }

                    // Validate profil lulusan (optional - only validate if column exists)
                    $profilLulusan = null;
                    if (isset($rowData['Profil Lulusan']) && !empty($rowData['Profil Lulusan'])) {
                        $profilLulusan = $rowData['Profil Lulusan'];
                        $validProfilLulusan = ['Ilmuwan', 'Wirausaha', 'Profesional'];
                        if (!in_array($profilLulusan, $validProfilLulusan)) {
                            $errors[] = "Baris " . ($rowIndex + 2) . ": Profil Lulusan harus salah satu dari: " . implode(', ', $validProfilLulusan);
                            $errorCount++;
                            continue;
                        }
                    }

                    // Validate penjurusan (optional - only validate if column exists)
                    $penjurusan = null;
                    if (isset($rowData['Penjurusan']) && !empty($rowData['Penjurusan'])) {
                        $penjurusan = $rowData['Penjurusan'];
                        $validPenjurusan = ['Sistem Informasi', 'Perekayasa Perangkat Lunak', 'Perekayasa Jaringan Komputer', 'Sistem Cerdas'];
                        if (!in_array($penjurusan, $validPenjurusan)) {
                            $errors[] = "Baris " . ($rowIndex + 2) . ": Penjurusan harus salah satu dari: " . implode(', ', $validPenjurusan);
                            $errorCount++;
                            continue;
                        }
                    }

                    // Handle optional fields - check if columns exist before accessing
                    $siapSidang = (isset($rowData['Siap Sidang']) && $rowData['Siap Sidang'] == '1') ? true : false;
                    $isPrioritas = (isset($rowData['Prioritas']) && $rowData['Prioritas'] == '1') ? true : false;
                    $keteranganPrioritas = (isset($rowData['Keterangan Prioritas']) && !empty($rowData['Keterangan Prioritas'])) ? $rowData['Keterangan Prioritas'] : null;

                    // Create mahasiswa
                    Mahasiswa::create([
                        'nim' => $rowData['NIM'],
                        'nama' => $rowData['Nama'],
                        'angkatan' => (int) $rowData['Angkatan'],
                        'id_dospem' => $dosen->id,
                        'judul_skripsi' => $rowData['Judul Skripsi'],
                        'profil_lulusan' => $profilLulusan,
                        'penjurusan' => $penjurusan,
                        'siap_sidang' => $siapSidang,
                        'is_prioritas' => $isPrioritas,
                        'keterangan_prioritas' => $keteranganPrioritas,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            // Prepare message
            $message = "Import selesai: {$successCount} data berhasil diimport";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} data gagal";
            }

            if (!empty($errors)) {
                $errorDetails = implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorDetails .= ' (dan ' . (count($errors) - 5) . ' error lainnya)';
                }
                return redirect()->route('mahasiswa.index')
                    ->with($successCount > 0 ? 'success' : 'error', $message)
                    ->with('info', 'Detail error: ' . $errorDetails);
            }

            return redirect()->route('mahasiswa.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mahasiswa.index')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
}
