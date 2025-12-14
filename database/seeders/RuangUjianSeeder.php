<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RuangUjian;

class RuangUjianSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['nama' => 'Ruang A101', 'lokasi' => 'Gedung A Lantai 1', 'kapasitas' => 20, 'is_aktif' => true, 'lantai' => 1],
            ['nama' => 'Ruang A102', 'lokasi' => 'Gedung A Lantai 1', 'kapasitas' => 20, 'is_aktif' => true, 'lantai' => 1],
            ['nama' => 'Ruang B201', 'lokasi' => 'Gedung B Lantai 2', 'kapasitas' => 15, 'is_aktif' => true, 'lantai' => 2],
        ];

        foreach ($rooms as $room) {
            RuangUjian::updateOrCreate(
                ['nama' => $room['nama']],
                $room
            );
        }
    }
}


