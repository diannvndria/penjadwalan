<?php

namespace App\Http\Controllers;

use App\Models\RuangUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RuangUjianController extends Controller
{
    public function index()
    {
        $ruang = RuangUjian::orderBy('nama')->get();
        return view('ruang_ujian.index', compact('ruang'));
    }

    public function create()
    {
        return view('ruang_ujian.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'is_aktif' => 'boolean',
            'lantai' => 'required|integer|min:1|max:10',
            'is_prioritas' => 'boolean',
        ]);

        RuangUjian::create($request->all());

        // Clear rooms cache after creating new room
        Cache::forget('active_rooms');

        return redirect()->route('ruang-ujian.index')->with('success', 'Ruang ujian berhasil ditambahkan.');
    }

    public function edit(RuangUjian $ruangUjian)
    {
        return view('ruang_ujian.edit', compact('ruangUjian'));
    }

    public function update(Request $request, RuangUjian $ruangUjian)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'is_aktif' => 'boolean',
            'lantai' => 'required|integer|min:1|max:10',
            'is_prioritas' => 'boolean',
        ]);

        $ruangUjian->update($request->all());

        // Clear rooms cache after updating (especially important if is_aktif changed)
        Cache::forget('active_rooms');

        return redirect()->route('ruang-ujian.index')->with('success', 'Data ruang ujian berhasil diperbarui.');
    }

    public function destroy(RuangUjian $ruangUjian)
    {
        try {
            $ruangUjian->delete();

            // Clear rooms cache after deleting
            Cache::forget('active_rooms');

            return redirect()->route('ruang-ujian.index')->with('success', 'Ruang ujian berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('ruang-ujian.index')->with('error', 'Tidak dapat menghapus ruang ujian karena masih digunakan dalam jadwal.');
        }
    }
}
