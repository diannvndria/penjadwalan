<?php

namespace Database\Seeders;

use App\Models\RuangUjian;
use Illuminate\Database\Seeder;

class RuangUjianSeeder extends Seeder
{
    public function run(): void
    {
        RuangUjian::truncate();

        RuangUjian::factory()->create([
            'nama' => 'Ruang A101',
            'lokasi' => 'Gedung A Lantai 1',
            'kapasitas' => 20,
            'is_aktif' => true,
            'lantai' => 1,
        ]);

        RuangUjian::factory()->create([
            'nama' => 'Ruang A102',
            'lokasi' => 'Gedung A Lantai 1',
            'kapasitas' => 20,
            'is_aktif' => true,
            'lantai' => 1,
        ]);

        RuangUjian::factory()->create([
            'nama' => 'Ruang B201',
            'lokasi' => 'Gedung B Lantai 2',
            'kapasitas' => 15,
            'is_aktif' => true,
            'lantai' => 2,
        ]);
    }
}
