<?php
namespace App\Http\Controllers;

use App\Models\Penguji; // Pastikan model Penguji sudah diimpor
use Illuminate\Http\Request;

class PengujiController extends Controller
{
    /**
     * Menampilkan daftar penguji.
     */
    public function index()
    {
        $pengujis = Penguji::all();
        return view('penguji.index', compact('pengujis'));
    }

    /**
     * Menampilkan form untuk membuat penguji baru.
     */
    public function create()
    {
        return view('penguji.create');
    }

    /**
     * Menyimpan data penguji baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Penguji::create($request->all());

        return redirect()->route('penguji.index')->with('success', 'Penguji berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data penguji.
     */
    public function edit(Penguji $penguji)
    {
        return view('penguji.edit', compact('penguji'));
    }

    /**
     * Memperbarui data penguji di database.
     */
    public function update(Request $request, Penguji $penguji)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $penguji->update($request->all());

        return redirect()->route('penguji.index')->with('success', 'Data penguji berhasil diperbarui.');
    }

    /**
     * Menghapus data penguji dari database.
     */
    public function destroy(Penguji $penguji)
    {
        try {
            $penguji->delete();
            return redirect()->route('penguji.index')->with('success', 'Penguji berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani jika penguji masih terhubung ke jadwal atau munaqosah (Foreign Key Constraint)
            return redirect()->route('penguji.index')->with('error', 'Tidak dapat menghapus penguji karena masih terhubung dengan jadwal atau munaqosah.');
        }
    }
}