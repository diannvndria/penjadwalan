<?php
namespace App\Http\Controllers;

use App\Models\Dosen; // Pastikan model Dosen sudah diimpor
use Illuminate\Http\Request;

class DosenController extends Controller
{
    /**
     * Menampilkan daftar dosen.
     * Termasuk jumlah mahasiswa yang diampu.
     */
    public function index()
    {
        // Mengambil semua dosen dan menghitung jumlah mahasiswa yang diampu oleh masing-masing dosen
    $dosens = Dosen::withCount('mahasiswas')->orderBy('nama')->paginate(10); // paginate for consistent paging
    return view('dosen.index', compact('dosens'));
    }

    /**
     * Menampilkan form untuk membuat dosen baru.
     */
    public function create()
    {
        return view('dosen.create');
    }

    /**
     * Menyimpan data dosen baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas_ampu' => 'required|integer|min:0', // Kapasitas minimal 0
        ]);

        Dosen::create($request->all());

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data dosen.
     */
    public function edit(Dosen $dosen) // Laravel otomatis menemukan Dosen berdasarkan ID dari route
    {
        return view('dosen.edit', compact('dosen'));
    }

    /**
     * Memperbarui data dosen di database.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas_ampu' => 'required|integer|min:0',
        ]);

        $dosen->update($request->all());

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Menghapus data dosen dari database.
     */
    public function destroy(Dosen $dosen)
    {
        try {
            $dosen->delete();
            return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani jika ada mahasiswa yang masih terhubung ke dosen ini (Foreign Key Constraint)
            return redirect()->route('dosen.index')->with('error', 'Tidak dapat menghapus dosen karena masih mengampu mahasiswa. Harap hapus atau pindahkan mahasiswa terlebih dahulu.');
        }
    }
}