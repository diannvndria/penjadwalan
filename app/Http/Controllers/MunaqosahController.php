<?php

namespace App\Http\Controllers;

use App\Models\Munaqosah;       // Import model Munaqosah
use App\Models\Mahasiswa;      // Import model Mahasiswa
use App\Models\Penguji;        // Import model Penguji
use App\Models\JadwalPenguji;  // Import model JadwalPenguji (untuk cek bentrok non-munaqosah)
use App\Models\HistoriMunaqosah; // Import model HistoriMunaqosah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // Untuk transaksi database (DB::transaction)
use Carbon\Carbon;                  // Untuk manipulasi tanggal dan waktu

class MunaqosahController extends Controller
{
    /**
     * Menampilkan daftar jadwal munaqosah.
     * Mengambil semua jadwal munaqosah beserta relasi terkait (mahasiswa, penguji1, penguji2)
     * untuk ditampilkan di tabel indeks.
     */
    public function index()
    {
        // Eager load relasi untuk menghindari masalah N+1 query dan menampilkan data terkait
        // Relasi 'pengujiUtama' telah dihapus, jadi tidak perlu di-load
        $munaqosahs = Munaqosah::with('mahasiswa', 'penguji1', 'penguji2')->get();
        return view('munaqosah.index', compact('munaqosahs'));
    }

    /**
     * Menampilkan form untuk membuat jadwal munaqosah baru.
     * Hanya menampilkan mahasiswa yang berstatus 'siap_sidang' dan belum memiliki jadwal munaqosah.
     */
    public function create()
    {
        // Mengambil daftar mahasiswa yang siap sidang dan belum dijadwalkan munaqosah
        // Eager load dospem juga untuk informasi di dropdown
        $mahasiswasSiapSidang = Mahasiswa::where('siap_sidang', true)
                                         ->doesntHave('munaqosah') // Filter: mahasiswa yang belum punya relasi munaqosah
                                         ->with('dospem')
                                         ->get();
        // Mengambil semua penguji untuk pilihan di dropdown form
        $pengujis = Penguji::all();
        return view('munaqosah.create', compact('mahasiswasSiapSidang', 'pengujis'));
    }

    /**
     * Menyimpan data jadwal munaqosah baru ke database.
     * Melakukan validasi input, serta pengecekan bentrok jadwal untuk semua penguji yang dipilih.
     * Jika berhasil, menyimpan data dan mencatat histori perubahan.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'id_mahasiswa' => 'required|exists:mahasiswas,id|unique:munaqosahs,id_mahasiswa', // Mahasiswa hanya bisa punya 1 jadwal munaqosah
            'tanggal_munaqosah' => 'required|date|after_or_equal:today', // Tanggal munaqosah tidak boleh di masa lalu
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai', // Waktu selesai harus setelah waktu mulai
            'id_penguji1' => 'required|exists:pengujis,id', // Penguji 1 wajib ada dan harus ada di tabel pengujis
            'id_penguji2' => 'nullable|exists:pengujis,id|different:id_penguji1', // Penguji 2 opsional, harus beda dengan Penguji 1
            // id_penguji_utama sudah dihapus dari skema database dan validasi
        ]);

        // Mengambil dan memformat data tanggal dan waktu untuk pengecekan bentrok
        $tanggal = Carbon::parse($request->tanggal_munaqosah)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        // Mengumpulkan semua ID penguji yang terlibat dalam jadwal ini (menghilangkan duplikat dan nilai null)
        // Hanya Penguji 1 dan Penguji 2 yang diperhitungkan
        $pengujiIds = array_filter(array_unique([
            $request->id_penguji1,
            $request->id_penguji2,
        ]));

        // Loop untuk mengecek bentrok jadwal untuk setiap penguji yang dipilih
        foreach ($pengujiIds as $pengujiId) {
            // Memanggil metode pembantu untuk mengecek konflik jadwal
            // Tanpa excludeMunaqosahId karena ini adalah pembuatan jadwal baru
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)) {
                $pengujiNama = Penguji::find($pengujiId)->nama;
                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        // Jika tidak ada bentrok, simpan data dalam transaksi database
        DB::transaction(function () use ($request) {
            $munaqosah = Munaqosah::create([
                'id_mahasiswa' => $request->id_mahasiswa,
                'tanggal_munaqosah' => $request->tanggal_munaqosah,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'id_penguji1' => $request->id_penguji1,
                'id_penguji2' => $request->id_penguji2,
                // id_penguji_utama tidak lagi ada di tabel
                'status_konfirmasi' => 'pending', // Default status untuk jadwal baru
            ]);

            // Mencatat histori bahwa jadwal munaqosah baru telah dibuat
            HistoriMunaqosah::create([
                'id_munaqosah' => $munaqosah->id,
                'perubahan' => 'Jadwal munaqosah baru dibuat.',
                'dilakukan_oleh' => auth()->id(), // Mengambil ID user yang sedang login
            ]);
        });

        return redirect()->route('munaqosah.index')->with('success', 'Jadwal munaqosah berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit jadwal munaqosah yang sudah ada.
     */
    public function edit(Munaqosah $munaqosah)
    {
        // Mengambil daftar mahasiswa yang siap sidang, atau mahasiswa yang sedang memiliki jadwal munaqosah ini.
        $mahasiswasSiapSidang = Mahasiswa::where('siap_sidang', true)
                                         ->orWhere('id', $munaqosah->id_mahasiswa)
                                         ->with('dospem')
                                         ->get();
        // Mengambil semua penguji untuk pilihan di dropdown form
        $pengujis = Penguji::all();
        return view('munaqosah.edit', compact('munaqosah', 'mahasiswasSiapSidang', 'pengujis'));
    }

    /**
     * Memperbarui data jadwal munaqosah di database.
     * Melakukan validasi, pengecekan bentrok (kecuali jadwal yang sedang diedit), dan mencatat histori perubahan.
     */
    public function update(Request $request, Munaqosah $munaqosah)
    {
        // Simpan data asli munaqosah sebelum update untuk perbandingan histori
        $originalData = $munaqosah->getOriginal();

        // Validasi input dari form
        $request->validate([
            'id_mahasiswa' => 'required|exists:mahasiswas,id|unique:munaqosahs,id_mahasiswa,' . $munaqosah->id, // Kecualikan jadwal ini sendiri dari cek unik mahasiswa
            'tanggal_munaqosah' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'id_penguji1' => 'required|exists:pengujis,id',
            'id_penguji2' => 'nullable|exists:pengujis,id|different:id_penguji1',
            // id_penguji_utama sudah dihapus dari validasi
            'status_konfirmasi' => 'required|in:pending,dikonfirmasi,ditolak', // Admin mengkonfirmasi status jadwal
        ]);

        // Mengambil dan memformat data tanggal dan waktu untuk pengecekan bentrok
        $tanggal = Carbon::parse($request->tanggal_munaqosah)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        // Mengumpulkan semua ID penguji yang terlibat (hanya Penguji 1 dan Penguji 2)
        $pengujiIds = array_filter(array_unique([
            $request->id_penguji1,
            $request->id_penguji2,
        ]));

        // Loop untuk mengecek bentrok jadwal untuk setiap penguji yang dipilih
        foreach ($pengujiIds as $pengujiId) {
            // Memanggil metode pembantu untuk mengecek konflik jadwal,
            // dan MENGECUALIKAN ID Munaqosah yang sedang di-update
            if ($this->checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $munaqosah->id)) {
                $pengujiNama = Penguji::find($pengujiId)->nama;
                return back()->withInput()->withErrors(['bentrok' => "Penguji {$pengujiNama} tidak tersedia pada tanggal dan waktu yang dipilih karena bentrok dengan jadwal lain."]);
            }
        }

        // Jika tidak ada bentrok, simpan data dalam transaksi database
        DB::transaction(function () use ($request, $munaqosah, $originalData) {
            // Update data munaqosah
            $munaqosah->update([
                'id_mahasiswa' => $request->id_mahasiswa,
                'tanggal_munaqosah' => $request->tanggal_munaqosah,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'id_penguji1' => $request->id_penguji1,
                'id_penguji2' => $request->id_penguji2,
                // id_penguji_utama tidak lagi ada
                'status_konfirmasi' => $request->status_konfirmasi,
            ]);

            // Mencatat histori perubahan jika ada perbedaan data
            $changes = [];
            foreach ($request->except('_token', '_method') as $key => $value) {
                // Pastikan key ada di data original sebelum membandingkan
                if (array_key_exists($key, $originalData) && $value != $originalData[$key]) {
                    // Penanganan khusus untuk foreign key (id_mahasiswa, id_pengujiX) agar histori lebih mudah dibaca
                    // Sesuaikan list key di sini karena id_penguji_utama sudah dihapus
                    if (in_array($key, ['id_mahasiswa', 'id_penguji1', 'id_penguji2'])) {
                        $modelClass = 'App\\Models\\' . ucfirst(str_replace('id_', '', $key));
                        $oldValueName = $originalData[$key] ? ($modelClass::find($originalData[$key])->nama ?? 'N/A') : 'Kosong';
                        $newValueName = $value ? ($modelClass::find($value)->nama ?? 'N/A') : 'Kosong';
                        $changes[] = ucfirst(str_replace('id_', '', $key)) . ": '$oldValueName' menjadi '$newValueName'";
                    }
                    // Penanganan khusus untuk status_konfirmasi
                    elseif ($key === 'status_konfirmasi') {
                         $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": '" . ucfirst($originalData[$key]) . "' menjadi '" . ucfirst($value) . "'";
                    }
                    // Untuk kolom lainnya, langsung catat perubahan nilai
                    else {
                        $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": '$originalData[$key]' menjadi '$value'";
                    }
                }
            }

            // Jika ada perubahan, simpan histori
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
     * Mencatat histori penghapusan sebelum data dihapus secara permanen.
     */
    public function destroy(Munaqosah $munaqosah)
    {
        DB::transaction(function () use ($munaqosah) {
            // Ambil nama mahasiswa sebelum munaqosah dihapus untuk histori
            $mahasiswaNama = $munaqosah->mahasiswa->nama ?? 'Nama tidak diketahui';
            $mahasiswaNIM = $munaqosah->mahasiswa->nim ?? 'NIM tidak diketahui';

            // Hapus jadwal munaqosah
            // Karena onDelete('cascade') pada foreign key, histori yang terkait juga akan otomatis terhapus
            $munaqosah->delete();

            // Catat histori bahwa jadwal munaqosah telah dihapus
            // id_munaqosah di histori bisa null karena jadwalnya sudah tidak ada
            HistoriMunaqosah::create([
                'id_munaqosah' => null, // Menunjukkan jadwal utama sudah tidak ada
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
        // Mengambil semua histori perubahan untuk jadwal munaqosah ini,
        // serta user yang melakukan perubahan, diurutkan dari yang terbaru.
        $histories = $munaqosah->historiPerubahan()->with('user')->orderBy('created_at', 'desc')->get();
        return view('munaqosah.histori', compact('munaqosah', 'histories'));
    }

    /**
     * Metode pembantu (private) untuk mengecek bentrok jadwal penguji.
     * Digunakan baik untuk jadwal munaqosah (saat store/update) maupun untuk pencarian slot otomatis.
     *
     * @param int $pengujiId ID Penguji yang akan dicek ketersediaannya.
     * @param string $tanggal Tanggal dalam format 'YYYY-MM-DD'.
     * @param string $waktuMulai Waktu mulai dalam format 'HH:MM'.
     * @param string $waktuSelesai Waktu selesai dalam format 'HH:MM'.
     * @param int|null $excludeMunaqosahId ID Munaqosah yang dikecualikan dari pengecekan bentrok (digunakan saat update jadwal munaqosah).
     * @return bool True jika ada bentrok, False jika penguji tersedia di slot waktu tersebut.
     */
    private function checkPengujiConflict($pengujiId, $tanggal, $waktuMulai, $waktuSelesai, $excludeMunaqosahId = null)
    {
        // --- Pengecekan bentrok di tabel `jadwal_pengujis` (jadwal umum penguji) ---
        // Mencari jadwal_pengujis yang tumpang tindih untuk penguji dan tanggal yang sama
        $isBentrokJadwalPenguji = JadwalPenguji::where('id_penguji', $pengujiId)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                // Kondisi tumpang tindih waktu: (StartA < EndB AND EndA > StartB)
                $query->where('waktu_mulai', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->exists();

        if ($isBentrokJadwalPenguji) {
            return true; // Ditemukan bentrok dengan jadwal non-munaqosah
        }

        // --- Pengecekan bentrok di tabel `munaqosahs` (jadwal munaqosah lain) ---
        // Mencari jadwal munaqosah yang tumpang tindih untuk penguji dan tanggal yang sama
        $queryMunaqosah = Munaqosah::where(function ($query) use ($pengujiId) {
                // Cek apakah penguji ini terlibat sebagai penguji1 atau penguji2
                $query->where('id_penguji1', $pengujiId)
                      ->orWhere('id_penguji2', $pengujiId);
                // id_penguji_utama sudah tidak ada
            })
            ->where('tanggal_munaqosah', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                // Kondisi tumpang tindih waktu
                $query->where('waktu_mulai', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>', $waktuMulai);
            });

        // Jika ada ID munaqosah yang dikecualikan (saat update), tambahkan kondisi ini
        if ($excludeMunaqosahId) {
            $queryMunaqosah->where('id', '!=', $excludeMunaqosahId);
        }

        // Jika ditemukan bentrok dengan jadwal munaqosah lain
        if ($queryMunaqosah->exists()) {
            return true;
        }

        return false; // Tidak ada bentrok, penguji tersedia
    }
}