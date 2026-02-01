<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateDosenNipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosens = [
            ['id' => 1, 'nip' => '198501011001'],
            ['id' => 2, 'nip' => '198502021002'],
            ['id' => 3, 'nip' => '198503031003'],
            ['id' => 4, 'nip' => '198504041004'],
            ['id' => 5, 'nip' => '198505051005'],
            ['id' => 6, 'nip' => '198506061006'],
            ['id' => 7, 'nip' => '198507071007'],
            ['id' => 8, 'nip' => '198508081008'],
        ];

        foreach ($dosens as $dosen) {
            DB::table('dosen')
                ->where('id', $dosen['id'])
                ->update(['nip' => $dosen['nip']]);
        }
    }
}
