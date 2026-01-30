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
        if (!in_array($sortField, $allowedSortFields)) {
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

        $mahasiswas = $query->paginate(10);

        // 3. PERBAIKAN PENGIRIMAN DATA: Kirim semua variabel yang dibutuhkan oleh view
        return view('mahasiswa.index', compact(
            'mahasiswas',
            'angkatans_tersedia',
            'dosens',
            'angkatan',
            'sortField',
            'sortDirection'
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
        $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswas,nim',
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:'.date('Y'),
            'judul_skripsi' => 'required|string',
            'profil_lulusan' => 'nullable|string|in:Ilmuwan,Wirausaha,Profesional', // Validasi profil lulusan
            'penjurusan' => 'nullable|string|in:Sistem Informasi,Perekayasa Perangkat Lunak,Perekayasa Jaringan Komputer,Sistem Cerdas', // Validasi penjurusan
            'id_dospem' => 'required|exists:dosens,id',
            'siap_sidang' => 'boolean',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
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
     * Termasuk logika pemicu penjadwalan otomatis jika status siap_sidang berubah (jika diaktifkan).
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $oldSiapSidang = $mahasiswa->siap_sidang;

        $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswas,nim,'.$mahasiswa->id,
            'nama' => 'required|string|max:255',
            'angkatan' => 'required|integer|min:2000|max:'.date('Y'),
            'judul_skripsi' => 'required|string',
            'profil_lulusan' => 'nullable|string|in:Ilmuwan,Wirausaha,Profesional',
            'penjurusan' => 'nullable|string|in:Sistem Informasi,Perekayasa Perangkat Lunak,Perekayasa Jaringan Komputer,Sistem Cerdas',
            'id_dospem' => 'required|exists:dosens,id',
            'siap_sidang' => 'boolean',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
        ]);

        if ($mahasiswa->id_dospem != $request->id_dospem) {
            $newDospem = Dosen::find($request->id_dospem);
            if ($newDospem->kapasitas_ampu > 0 && $newDospem->mahasiswas()->count() >= $newDospem->kapasitas_ampu) {
                return back()->withInput()->withErrors(['id_dospem' => 'Dosen pembimbing baru ini sudah mencapai kapasitas maksimal. Pilih dosen lain atau perbarui kapasitas dosen.']);
            }
        }

        DB::transaction(function () use ($request, $mahasiswa) {
            $mahasiswa->update($request->all());

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
}
