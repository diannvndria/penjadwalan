<?php
namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Penguji; // Tambahkan import Penguji
use App\Models\JadwalPenguji; // Tambahkan import JadwalPenguji
use App\Models\Munaqosah; // Tambahkan import Munaqosah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk transaksi
use Carbon\Carbon; // Untuk manipulasi tanggal/waktu

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa, dengan opsi filter angkatan.
     */
    public function index(Request $request)
    {
        $angkatan = $request->query('angkatan'); // Ambil parameter 'angkatan' dari URL
        $mahasiswas = Mahasiswa::query(); // Mulai query untuk model Mahasiswa

        if ($angkatan) {
            $mahasiswas->where('angkatan', $angkatan); // Terapkan filter jika 'angkatan' ada
        }

        // Eager load relasi 'dospem' untuk menghindari N+1 query problem
        $mahasiswas = $mahasiswas->with('dospem')->paginate(10); // Ambil 10 data per halaman

        // Ambil daftar angkatan unik yang tersedia di database untuk dropdown filter
        $angkatans_tersedia = Mahasiswa::select('angkatan')->distinct()->orderBy('angkatan', 'asc')->pluck('angkatan');

        return view('mahasiswa.index', compact('mahasiswas', 'angkatan', 'angkatans_tersedia'));
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
        $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswas,nim', // NIM harus unik dan maks 20 karakter
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:' . date('Y'), // Angkatan valid antara 2000 sampai tahun sekarang
            'judul_skripsi' => 'required|string',
            'id_dospem' => 'required|exists:dosens,id', // id_dospem harus ada di tabel dosens
            'siap_sidang' => 'boolean', // Ini akan otomatis diset jika checkbox dicentang
        ]);

        $dosen = Dosen::find($request->id_dospem);
        if ($dosen->kapasitas_ampu > 0 && $dosen->mahasiswas()->count() >= $dosen->kapasitas_ampu) {
            return back()->withInput()->withErrors(['id_dospem' => 'Dosen pembimbing ini sudah mencapai kapasitas maksimal. Pilih dosen lain atau perbarui kapasitas dosen.']);
        }

        Mahasiswa::create($request->all());

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
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $oldSiapSidang = $mahasiswa->siap_sidang;

        $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
            'judul_skripsi' => 'required|string',
            'id_dospem' => 'required|exists:dosens,id',
            'siap_sidang' => 'boolean',
        ]);

        if ($mahasiswa->id_dospem != $request->id_dospem) {
            $newDospem = Dosen::find($request->id_dospem);
            if ($newDospem->kapasitas_ampu > 0 && $newDospem->mahasiswas()->count() >= $newDospem->kapasitas_ampu) {
                return back()->withInput()->withErrors(['id_dospem' => 'Dosen pembimbing baru ini sudah mencapai kapasitas maksimal. Pilih dosen lain atau perbarui kapasitas dosen.']);
            }
        }

        DB::transaction(function () use ($request, $mahasiswa, $oldSiapSidang) {
            $mahasiswa->update($request->all());

            // === Logika Penjadwalan Otomatis Dimulai Di Sini ===
            // Jika status siap_sidang diubah menjadi TRUE dan sebelumnya FALSE, dan belum ada jadwal munaqosah
            if ($request->boolean('siap_sidang') && !$oldSiapSidang && !$mahasiswa->munaqosah) {
                $pesanOtomatisasi = '';
                try {
                    // Panggil fungsi untuk mencoba membuat jadwal munaqosah otomatis
                    $jadwalTersedia = $this->findAvailableMunaqosahSlot($mahasiswa);

                    if ($jadwalTersedia) {
                        Munaqosah::create([
                            'id_mahasiswa' => $mahasiswa->id,
                            'tanggal_munaqosah' => $jadwalTersedia['tanggal'],
                            'waktu_mulai' => $jadwalTersedia['waktu_mulai'],
                            'waktu_selesai' => $jadwalTersedia['waktu_selesai'],
                            'id_penguji1' => $jadwalTersedia['penguji1']->id,
                            'id_penguji2' => $jadwalTersedia['penguji2']->id, // Pastikan ini tidak null
                            'status_konfirmasi' => 'pending', // Default status untuk jadwal otomatis
                        ]);
                        $pesanOtomatisasi = ' Jadwal munaqosah otomatis berhasil dibuat.';
                    } else {
                        $pesanOtomatisasi = ' Tidak dapat menemukan slot jadwal munaqosah otomatis yang tersedia.';
                    }
                } catch (\Exception $e) {
                    $pesanOtomatisasi = ' Gagal membuat jadwal munaqosah otomatis: ' . $e->getMessage();
                    // Log error untuk debugging lebih lanjut
                    \Log::error('Error penjadwalan otomatis mahasiswa ' . $mahasiswa->id . ': ' . $e->getMessage());
                }
                session()->flash('info', $pesanOtomatisasi); // Gunakan flash session untuk info tambahan
            }
            // === Logika Penjadwalan Otomatis Berakhir Di Sini ===
        });


        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.' . (session('info') ?? ''));
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

    /**
     * Metode pembantu untuk menemukan slot munaqosah yang tersedia (disesuaikan untuk 2 penguji).
     *
     * @param Mahasiswa $mahasiswa
     * @return array|null Mengembalikan array dengan detail jadwal jika ditemukan, null jika tidak.
     */
    private function findAvailableMunaqosahSlot(Mahasiswa $mahasiswa)
    {
        $durationInMinutes = 90; // Durasi standar munaqosah
        $maxDaysAhead = 30; // Cari slot hingga 30 hari ke depan
        $startTimeLimit = '08:00'; // Munaqosah tidak dimulai sebelum jam 8 pagi
        $endTimeLimit = '17:00'; // Munaqosah tidak selesai setelah jam 5 sore

        $allPengujis = Penguji::all();
        if ($allPengujis->count() < 2) { // Minimal butuh 2 penguji
            throw new \Exception('Tidak cukup penguji terdaftar (minimal 2) untuk menjadwalkan munaqosah otomatis.');
        }

        for ($i = 0; $i <= $maxDaysAhead; $i++) {
            $currentDate = Carbon::now()->addDays($i)->format('Y-m-d');
            // Anda bisa menambahkan logika untuk melewati hari libur/akhir pekan jika diperlukan
            // $dayOfWeek = Carbon::parse($currentDate)->dayOfWeek;
            // if ($dayOfWeek == Carbon::SUNDAY || $dayOfWeek == Carbon::SATURDAY) { continue; }

            $currentTime = Carbon::parse($currentDate . ' ' . $startTimeLimit);
            $endOfDay = Carbon::parse($currentDate . ' ' . $endTimeLimit);

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

                    if (!$isBentrok) {
                        $availablePengujisForSlot->push($penguji);
                    }
                }

                // Jika setidaknya ada 2 penguji yang tersedia
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
        return null; // Tidak menemukan slot yang tersedia
    }

    /**
     * Metode pembantu untuk mengecek bentrok jadwal penguji (disesuaikan untuk 2 penguji).
     *
     * @param int $pengujiId
     * @param string $tanggal
     * @param string $waktuMulai
     * @param string $waktuSelesai
     * @param int|null $excludeMunaqosahId ID munaqosah yang dikecualikan dari pengecekan (saat update)
     * @return bool True jika ada bentrok, False jika tidak.
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
        $queryMunaqosah = Munaqosah::where(function($query) use ($pengujiId) {
                // Hanya cek id_penguji1 dan id_penguji2
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
}