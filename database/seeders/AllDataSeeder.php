<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\JadwalPenguji;
use App\Models\Mahasiswa;
use App\Models\Penguji;
use App\Models\User;
use Database\Factories\MahasiswaFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AllDataSeeder extends Seeder
{
    public int $studentCount = 32;

    public int $prioritasCount = 10;

    public int $siapSidangCount = 22;

    public array $angkatanYears = [2020, 2021, 2022, 2023, 2024, 2025];

    public function run(): void
    {
        // 0. Cleanup existing data to avoid duplicates when running seeds multiple times
        Schema::disableForeignKeyConstraints();
        JadwalPenguji::truncate();
        Mahasiswa::truncate();
        Penguji::truncate();
        Dosen::truncate();
        Schema::enableForeignKeyConstraints();

        // Reset NIM counters for fresh sequential generation
        MahasiswaFactory::resetNimCounters();

        // 1. Users
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        // 2. Dosens
        $dosens = Dosen::factory()
            ->count(8)
            ->withCapacity(12)
            ->create();

        // 3. Pengujis (Distinct lecturers, not duplicating Dosen names)
        // Create 12 unique examiners with distinct names and NIPs
        Penguji::factory()->count(2)->create(['is_prioritas' => true]);
        Penguji::factory()->count(10)->create(['is_prioritas' => false]);
        $allPengujis = Penguji::all();

        // 4. Mahasiswa with realistic sequential NIMs per angkatan
        // Pre-select random indices for prioritas and siap_sidang students
        $allIndices = collect(range(0, $this->studentCount - 1))->shuffle();

        $prioritasIndices = $allIndices->take($this->prioritasCount)->flip()->all();
        $siapSidangIndices = $allIndices->take($this->siapSidangCount)->flip()->all();

        $studentsPerYear = (int) floor($this->studentCount / count($this->angkatanYears));
        $totalStudents = 0;
        $createdCount = 0;

        foreach ($this->angkatanYears as $index => $year) {
            // Last year gets remaining students
            $count = ($index === count($this->angkatanYears) - 1)
                ? ($this->studentCount - $totalStudents)
                : $studentsPerYear;

            for ($i = 0; $i < $count; $i++) {
                $isPrioritas = isset($prioritasIndices[$createdCount]);
                $isSiapSidang = isset($siapSidangIndices[$createdCount]);

                Mahasiswa::factory()
                    ->angkatan($year)
                    ->when($isPrioritas, fn ($f) => $f->prioritas())
                    ->create([
                        'id_dospem' => $dosens->random()->nip,
                        'siap_sidang' => $isSiapSidang,
                    ]);

                $createdCount++;
            }

            $totalStudents += $count;
        }

        // 5. Jadwal Penguji
        foreach ($allPengujis as $p) {
            JadwalPenguji::factory()->count(2)->create([
                'id_penguji' => $p->id,
            ]);
        }
    }
}
