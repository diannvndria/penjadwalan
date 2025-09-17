<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RuangUjian;

class RuangUjianSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['nama' => 'Ruang A101', 'lokasi' => 'Gedung A Lantai 1', 'kapasitas' => 20, 'is_aktif' => true],
            ['nama' => 'Ruang A102', 'lokasi' => 'Gedung A Lantai 1', 'kapasitas' => 20, 'is_aktif' => true],
            ['nama' => 'Ruang B201', 'lokasi' => 'Gedung B Lantai 2', 'kapasitas' => 15, 'is_aktif' => true],
        ];

        foreach ($rooms as $room) {
            RuangUjian::updateOrCreate(
                ['nama' => $room['nama']],
                $room
            );
        }
    }
}


