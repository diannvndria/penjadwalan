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
        $query = Penguji::withCount([
            'munaqosahsAsPenguji1 as munaqosahs_as_penguji1_count' => function ($query) {
                $query->where('status_konfirmasi', 'dikonfirmasi');
            },
            'munaqosahsAsPenguji2 as munaqosahs_as_penguji2_count' => function ($query) {
                $query->where('status_konfirmasi', 'dikonfirmasi');
            },
        ]);

        $allIds = (clone $query)->pluck('nip')->toArray();

        $pengujis = $query->orderBy('nama')
            ->paginate(10);

        return view('penguji.index', compact('pengujis', 'allIds'));
    }

    /**
     * Menghapus beberapa data penguji sekaligus.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $request->ids);

        try {
            // Check usage first
            if (Penguji::whereIn('nip', $ids)->where(function ($query) {
                $query->has('munaqosahsAsPenguji1')->orHas('munaqosahsAsPenguji2');
            })->exists()) {
                return redirect()->route('penguji.index')->with('error', 'Beberapa data penguji tidak dapat dihapus karena masih digunakan.');
            }

            $count = Penguji::destroy($ids); // Eloquent destroy works with PKs (NIP now)

            return redirect()->route('penguji.index')->with('success', "$count data penguji berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->route('penguji.index')->with('error', 'Beberapa data penguji tidak dapat dihapus karena masih digunakan.');
        }
    }

    /**
     * Export beberapa data penguji sekaligus.
     */
    public function bulkExport(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $request->ids);
        $pengujis = Penguji::whereIn('nip', $ids)
            ->withCount([
                'munaqosahsAsPenguji1 as munaqosahs_as_penguji1_count' => function ($query) {
                    $query->where('status_konfirmasi', 'dikonfirmasi');
                },
                'munaqosahsAsPenguji2 as munaqosahs_as_penguji2_count' => function ($query) {
                    $query->where('status_konfirmasi', 'dikonfirmasi');
                },
            ])
            ->get();

        $filename = 'penguji_export_'.date('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['NIP', 'Nama Penguji', 'Prioritas', 'Keterangan Prioritas', 'Histori Penguji 1', 'Histori Penguji 2'];

        $callback = function () use ($pengujis, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($pengujis as $penguji) {
                $row['NIP'] = $penguji->nip ?? '';
                $row['Nama Penguji'] = $penguji->nama;
                $row['Prioritas'] = $penguji->is_prioritas ? 'Prioritas' : 'Biasa';
                $row['Keterangan Prioritas'] = $penguji->keterangan_prioritas ?? '';
                $row['Histori Penguji 1'] = $penguji->munaqosahs_as_penguji1_count ?? 0;
                $row['Histori Penguji 2'] = $penguji->munaqosahs_as_penguji2_count ?? 0;

                fputcsv($file, [
                    $row['NIP'],
                    $row['Nama Penguji'],
                    $row['Prioritas'],
                    $row['Keterangan Prioritas'],
                    $row['Histori Penguji 1'],
                    $row['Histori Penguji 2'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'nip' => 'required|string|max:255|unique:penguji,nip',
            'nama' => 'required|string|max:255',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
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
            'nip' => 'required|string|max:255|unique:penguji,nip,'.$penguji->nip.',nip',
            'nama' => 'required|string|max:255',
            'is_prioritas' => 'boolean',
            'keterangan_prioritas' => 'nullable|string|max:500',
        ]);

        $penguji->update($request->all());

        return redirect()->route('penguji.index')->with('success', 'Data penguji berhasil diperbarui.');
    }

    /**
     * Menghapus data penguji dari database.
     */
    public function destroy(Penguji $penguji)
    {
        if ($penguji->munaqosahsAsPenguji1()->exists() || $penguji->munaqosahsAsPenguji2()->exists()) {
            return redirect()->route('penguji.index')->with('error', 'Tidak dapat menghapus penguji karena masih terhubung dengan munaqosah.');
        }

        try {
            $penguji->delete();

            return redirect()->route('penguji.index')->with('success', 'Penguji berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('penguji.index')->with('error', 'Terjadi kesalahan saat menghapus penguji: '.$e->getMessage());
        }
    }
}
