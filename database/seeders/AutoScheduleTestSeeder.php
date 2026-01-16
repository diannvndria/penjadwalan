<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Penguji;
use App\Models\Mahasiswa;
use App\Models\JadwalPenguji;
use App\Models\RuangUjian;
use Carbon\Carbon;

class AutoScheduleTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // Create some dosens
        $dosens = [];
        for ($i = 1; $i <= 5; $i++) {
            $dosens[] = Dosen::firstOrCreate(
                ['nama' => "Dr. Dosen $i"],
                ['nama' => "Dr. Dosen $i"]
            );
        }

        // Create pengujis
        $pengujis = [];
        for ($i = 1; $i <= 8; $i++) {
            $pengujis[] = Penguji::firstOrCreate(
                ['nama' => "Dr. Penguji $i"],
                ['nama' => "Dr. Penguji $i"]
            );
        }

        // Create exam rooms
        $rooms = [];
        $roomNames = ['A101', 'A102', 'B201', 'B202'];
        foreach ($roomNames as $index => $roomName) {
            $rooms[] = RuangUjian::firstOrCreate(
                ['nama' => "Ruang $roomName"],
                [
                    'nama' => "Ruang $roomName",
                    'lantai' => ($index < 2) ? 1 : 2,
                    'kapasitas' => 30,
                    'is_aktif' => true,
                    'is_prioritas' => ($index === 0) // First room is priority room
                ]
            );
        }

        // Create some mahasiswa yang siap sidang
        $mahasiswas = [];
        for ($i = 1; $i <= 6; $i++) {
            $mahasiswas[] = Mahasiswa::firstOrCreate(
                ['nim' => "123456789" . $i],
                [
                    'nim' => "123456789" . $i,
                    'nama' => "Mahasiswa Test $i",
                    'angkatan' => 2021,
                    'judul_skripsi' => "Judul Skripsi Test $i untuk Auto Schedule",
                    'id_dospem' => $dosens[($i - 1) % 5]->id,
                    'siap_sidang' => ($i <= 4) // 4 mahasiswa pertama siap sidang
                ]
            );
        }

        // Create some jadwal penguji untuk simulasi konflik
        $today = Carbon::today();

        // Penguji 1 tidak tersedia hari ini jam 10:00-12:00
        JadwalPenguji::firstOrCreate([
            'id_penguji' => $pengujis[0]->id,
            'tanggal' => $today->format('Y-m-d'),
            'waktu_mulai' => '10:00:00',
            'waktu_selesai' => '12:00:00',
        ], [
            'deskripsi' => 'Rapat Departemen'
        ]);

        // Penguji 2 tidak tersedia besok jam 09:00-11:00
        JadwalPenguji::firstOrCreate([
            'id_penguji' => $pengujis[1]->id,
            'tanggal' => $today->addDay()->format('Y-m-d'),
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
        ], [
            'deskripsi' => 'Mengajar Kelas'
        ]);

        $this->command->info('✅ Auto-schedule test data has been created successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info("   - Admin user: admin@test.com (password: password)");
        $this->command->info("   - Dosens: " . count($dosens));
        $this->command->info("   - Pengujis: " . count($pengujis));
        $this->command->info("   - Ruang Ujian: " . count($rooms));
        $this->command->info("   - Mahasiswas: " . count($mahasiswas));
        $this->command->info("   - Mahasiswa siap sidang: 4");
        $this->command->info("   - Jadwal penguji (conflicts): 2");
    }
}
