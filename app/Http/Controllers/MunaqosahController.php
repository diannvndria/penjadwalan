<?php

namespace App\Http\Controllers;

use App\Models\Munaqosah;       // Import model Munaqosah
use App\Models\Mahasiswa;      // Import model Mahasiswa
use App\Models\Penguji;        // Import model Penguji
use App\Models\JadwalPenguji;  // Import model JadwalPenguji (untuk cek bentrok non-munaqosah)
use App\Models\HistoriMunaqosah; // Import model HistoriMunaqosah
use App\Models\RuangUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // Untuk transaksi database (DB::transaction)
use Carbon\Carbon;                  // Untuk manipulasi tanggal dan waktu
class MunaqosahController extends Controller
{
    /**
     * Menampilkan daftar jadwal munaqosah.
     */
    public function index(Request $request)
    {
        $query = Munaqosah::with('mahasiswa', 'penguji1', 'penguji2', 'ruangUjian');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate) {
            $query->whereDate('tanggal_munaqosah', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_munaqosah', '<=', $endDate);
        }

        $munaqosahs = $query->orderBy('tanggal_munaqosah')->orderBy('waktu_mulai')->get();

        return view('munaqosah.index', [
            'munaqosahs' => $munaqosahs,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
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
            'id_mahasiswa' => 'required|exists:mahasiswas,id|unique:munaqosahs,id_mahasiswa',
            'tanggal_munaqosah' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'id_penguji1' => 'required|exists:pengujis,id',
            'id_penguji2' => 'nullable|exists:pengujis,id|different:id_penguji1',
            'id_ruang_ujian' => 'required|exists:ruang_ujian,id',
        ]);

        $tanggal = Carbon::parse($request->tanggal_munaqosah)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        $pengujiIds = array_filter(array_unique([
            $request->id_penguji1,
            $request->id_penguji2,
        ]));

        foreach ($pengujiIds as $pengujiId) {
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)) {
                $pengujiNama = Penguji::find($pengujiId)->nama;
                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        if ($this->checkRuangConflict($request->id_ruang_ujian, $tanggal, $waktuMulai, $waktuSelesai)) {
            $ruangNama = RuangUjian::find($request->id_ruang_ujian)->nama ?? 'Ruang';
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
            'id_mahasiswa' => 'required|exists:mahasiswas,id|unique:munaqosahs,id_mahasiswa,' . $munaqosah->id,
            'tanggal_munaqosah' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'id_penguji1' => 'required|exists:pengujis,id',
            'id_penguji2' => 'nullable|exists:pengujis,id|different:id_penguji1',
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

        foreach ($pengujiIds as $pengujiId) {
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $munaqosah->id)) {
                $pengujiNama = Penguji::find($pengujiId)->nama;
                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        if ($this->checkRuangConflict($request->id_ruang_ujian, $tanggal, $waktuMulai, $waktuSelesai, $munaqosah->id)) {
            $ruangNama = RuangUjian::find($request->id_ruang_ujian)->nama ?? 'Ruang';
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
                        $modelClass = 'App\\Models\\' . ucfirst(str_replace('id_', '', $key));
                        $oldValueName = $originalData[$key] ? ($modelClass::find($originalData[$key])->nama ?? 'N/A') : 'Kosong';
                        $newValueName = $value ? ($modelClass::find($value)->nama ?? 'N/A') : 'Kosong';
                        $changes[] = ucfirst(str_replace('id_', '', $key)) . ": '$oldValueName' menjadi '$newValueName'";
                    } elseif ($key === 'status_konfirmasi') {
                         $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": '" . ucfirst($originalData[$key]) . "' menjadi '" . ucfirst($value) . "'";
                    }
                    else {
                        $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": '$originalData[$key]' menjadi '$value'";
                    }
                }
            }

            if (!empty($changes)) {
                HistoriMunaqosah::create([
                    'id_munaqosah' => $munaqosah->id,
                    'perubahan' => 'Jadwal munaqosah diperbarui: ' . implode(', ', $changes),
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
                'perubahan' => 'Jadwal munaqosah untuk mahasiswa ' . $mahasiswaNama . ' (NIM: ' . $mahasiswaNIM . ') telah dihapus.',
                'dilakukan_oleh' => auth()->id(),
            ]);
        });
        return redirect()->route('munaqosah.index')->with('success', 'Jadwal munaqosah berhasil dihapus.');
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

    private function checkRuangConflict($ruangId, $tanggal, $waktuMulai, $waktuSelesai, $excludeMunaqosahId = null)
    {
        $query = Munaqosah::where('id_ruang_ujian', $ruangId)
            ->where('tanggal_munaqosah', $tanggal)
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