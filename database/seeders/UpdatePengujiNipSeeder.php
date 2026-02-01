<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePengujiNipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pengujis = DB::table('penguji')->get();

        foreach ($pengujis as $penguji) {
            $nip = '19860' . str_pad($penguji->id, 2, '0', STR_PAD_LEFT) . '0' . str_pad($penguji->id, 2, '0', STR_PAD_LEFT) . '100' . str_pad($penguji->id, 1, '0', STR_PAD_LEFT);
            DB::table('penguji')
                ->where('id', $penguji->id)
                ->update(['nip' => $nip]);
        }
    }
}
