<?php

namespace App\Http\Controllers; // <-- Pastikan namespace ini benar!

use App\Models\JadwalPenguji; // Impor model JadwalPenguji
use App\Models\Penguji;      // Impor model Penguji
use Carbon\Carbon;
use Illuminate\Http\Request; // Digunakan untuk manipulasi tanggal dan waktu

class JadwalPengujiController extends Controller // <-- Pastikan nama kelas ini benar!
{
    /**
     * Menampilkan daftar jadwal penguji.
     */
    public function index()
    {
        try {
            // Add index to improve query performance
            $jadwalPengujis = JadwalPenguji::with(['penguji' => function ($query) {
                $query->select('id', 'nip', 'nama');
            }])
                ->select('id', 'id_penguji', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'deskripsi')
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->paginate(10);

            return view('jadwal_penguji.index', compact('jadwalPengujis'));
        } catch (\Exception $e) {
            \Log::error('Error in JadwalPengujiController@index: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat jadwal penguji baru.
     */
    public function create()
    {
        $pengujis = Penguji::all(); // Ambil semua penguji untuk dropdown

        return view('jadwal_penguji.create', compact('pengujis'));
    }

    /**
     * Menyimpan jadwal penguji baru ke database, dengan cek bentrok.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_penguji' => 'required|exists:penguji,id',
            'tanggal' => 'required|date|after_or_equal:today', // Tanggal tidak bisa di masa lalu
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai', // Waktu selesai harus setelah waktu mulai
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $tanggal = Carbon::parse($request->tanggal)->toDateString(); // Konversi tanggal ke format database (YYYY-MM-DD)
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        // Logika untuk mengecek bentrok jadwal penguji untuk penguji yang sama
        // Cek apakah ada jadwal lain untuk penguji yang sama pada tanggal yang sama
        // dan rentang waktunya tumpang tindih
        $isBentrok = JadwalPenguji::where('id_penguji', $request->id_penguji)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                // Kondisi tumpang tindih waktu:
                // (Mulai_baru < Selesai_lama AND Selesai_baru > Mulai_lama)
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->exists();

        if ($isBentrok) {
            return back()->withInput()->withErrors(['bentrok' => 'Jadwal penguji ini bentrok dengan jadwal lain pada tanggal dan waktu yang sama. Silakan pilih waktu lain.']);
        }

        JadwalPenguji::create($request->all());

        return redirect()->route('jadwal-penguji.index')->with('success', 'Jadwal penguji berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit jadwal penguji.
     */
    public function edit(JadwalPenguji $jadwalPenguji)
    {
        $pengujis = Penguji::all();

        return view('jadwal_penguji.edit', compact('jadwalPenguji', 'pengujis'));
    }

    /**
     * Memperbarui jadwal penguji di database, dengan cek bentrok.
     */
    public function update(Request $request, JadwalPenguji $jadwalPenguji)
    {
        $request->validate([
            'id_penguji' => 'required|exists:penguji,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $tanggal = Carbon::parse($request->tanggal)->toDateString();
        $waktuMulai = $request->waktu_mulai;
        $waktuSelesai = $request->waktu_selesai;

        // Cek bentrok saat update, kecualikan jadwal yang sedang diedit (berdasarkan ID-nya)
        $isBentrok = JadwalPenguji::where('id_penguji', $request->id_penguji)
            ->where('tanggal', $tanggal)
            ->where('id', '!=', $jadwalPenguji->id) // Penting: Kecualikan jadwal ini sendiri dari pengecekan
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->exists();

        if ($isBentrok) {
            return back()->withInput()->withErrors(['bentrok' => 'Jadwal penguji ini bentrok dengan jadwal lain pada tanggal dan waktu yang sama. Silakan pilih waktu lain.']);
        }

        $jadwalPenguji->update($request->all());

        return redirect()->route('jadwal-penguji.index')->with('success', 'Jadwal penguji berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal penguji dari database.
     */
    public function destroy(JadwalPenguji $jadwalPenguji)
    {
        $jadwalPenguji->delete();

        return redirect()->route('jadwal-penguji.index')->with('success', 'Jadwal penguji berhasil dihapus.');
    }
}
