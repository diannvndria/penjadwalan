<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Penguji;
use App\Models\Mahasiswa;
use App\Models\RuangUjian;
use App\Models\Munaqosah;
use App\Models\JadwalPenguji;
use App\Models\HistoriMunaqosah;
use Carbon\Carbon;

class AllDataSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Admin Test',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        User::firstOrCreate([
            'email' => 'user@test.com'
        ], [
            'name' => 'Regular User',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        // Ruang Ujian (reuse existing seeder logic if present)
        $this->call(RuangUjianSeeder::class);

        // Create dosens
        $dosens = [];
        for ($i = 1; $i <= 6; $i++) {
            $dosens[] = Dosen::firstOrCreate(['nama' => "Dr. Dosen $i"], ['kapasitas_ampu' => 12]);
        }

        // Create pengujis
        $pengujis = [];
        for ($i = 1; $i <= 10; $i++) {
            $pengujis[] = Penguji::firstOrCreate(['nama' => "Dr. Penguji $i"], ['is_prioritas' => ($i <= 2)]);
        }

        // Create mahasiswa (more detailed)
        for ($i = 1; $i <= 30; $i++) {
            $nim = '2020' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $mahasiswa = Mahasiswa::updateOrCreate([
                'nim' => $nim
            ], [
                'nama' => "Mahasiswa Full $i",
                'angkatan' => 2020 + ($i % 3),
                'judul_skripsi' => "Studi Kasus $i",
                'profil_lulusan' => 'Ilmu Komputer',
                'penjurusan' => 'Teknik Informatika',
                'id_dospem' => $dosens[($i - 1) % count($dosens)]->id,
                'siap_sidang' => $i <= 12,
                'is_prioritas' => ($i % 7 === 0),
                'keterangan_prioritas' => ($i % 7 === 0) ? 'Prioritas beasiswa' : null,
            ]);

            if ($mahasiswa->siap_sidang) {
                $tanggal = Carbon::today()->addDays(rand(1, 60))->format('Y-m-d');
                $waktu_mulai = sprintf('%02d:00:00', rand(8, 14));
                $waktu_selesai = sprintf('%02d:00:00', intval(explode(':', $waktu_mulai)[0]) + 2);

                $muna = Munaqosah::updateOrCreate([
                    'id_mahasiswa' => $mahasiswa->id
                ], [
                    'tanggal_munaqosah' => $tanggal,
                    'waktu_mulai' => $waktu_mulai,
                    'waktu_selesai' => $waktu_selesai,
                    'id_penguji1' => $pengujis[($i - 1) % count($pengujis)]->id,
                    'id_penguji2' => $pengujis[($i) % count($pengujis)]->id,
                    'id_ruang_ujian' => null,
                    'status_konfirmasi' => 'pending',
                ]);

                HistoriMunaqosah::firstOrCreate([
                    'id_munaqosah' => $muna->id,
                    'perubahan' => 'Jadwal awal dibuat',
                    'dilakukan_oleh' => null,
                    'created_at' => Carbon::now(),
                ]);
            }
        }

        // Jadwal penguji blocks
        $today = Carbon::today();
        foreach ($pengujis as $idx => $p) {
            for ($j = 0; $j < 2; $j++) {
                $tanggal = $today->copy()->addDays(rand(0, 90))->format('Y-m-d');
                $start = sprintf('%02d:00:00', rand(8, 15));
                $end = sprintf('%02d:00:00', intval(explode(':', $start)[0]) + 2);

                JadwalPenguji::firstOrCreate([
                    'id_penguji' => $p->id,
                    'tanggal' => $tanggal,
                    'waktu_mulai' => $start,
                    'waktu_selesai' => $end,
                ], [
                    'deskripsi' => 'Kegiatan seeder'
                ]);
            }
        }

        $this->command->info('✅ Full dataset seeded: users, dosens, pengujis, mahasiswa, munaqosah, jadwal penguji, ruang ujian.');
    }
}
