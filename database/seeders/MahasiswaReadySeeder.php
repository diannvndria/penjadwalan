<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\JadwalPenguji;
use App\Models\HistoriMunaqosah;
use Carbon\Carbon;

class MahasiswaReadySeeder extends Seeder
{
	public function run(): void
	{
		// Ensure there are some dosens and pengujis available
		$dosens = Dosen::all();
		if ($dosens->isEmpty()) {
			// create some default dosens
			$dosens = collect();
			for ($i = 1; $i <= 5; $i++) {
				$dosens->push(Dosen::firstOrCreate(['nama' => "Dr. Dosen $i"], ['kapasitas_ampu' => 10]));
			}
		}

		$pengujis = Penguji::all();
		if ($pengujis->isEmpty()) {
			$pengujis = collect();
			for ($i = 1; $i <= 8; $i++) {
				$pengujis->push(Penguji::firstOrCreate(['nama' => "Dr. Penguji $i"], ['is_prioritas' => false]));
			}
		}

		// Create mahasiswa sample data (some ready for sidang, some not)
		for ($i = 1; $i <= 20; $i++) {
			$nim = '2021' . str_pad($i, 4, '0', STR_PAD_LEFT);

			$mahasiswa = Mahasiswa::updateOrCreate(
				['nim' => $nim],
				[
					'nama' => "Mahasiswa Seed $i",
					'angkatan' => 2021,
					'judul_skripsi' => "Judul Skripsi Seed $i",
					'profil_lulusan' => 'Ilmu Komputer',
					'penjurusan' => 'Teknik Informatika',
					'id_dospem' => $dosens[($i - 1) % $dosens->count()]->id,
					'siap_sidang' => $i <= 8, // first 8 are ready
					'is_prioritas' => ($i % 10 === 0),
					'keterangan_prioritas' => ($i % 10 === 0) ? 'Tesis mendesak' : null,
				]
			);

			// If mahasiswa is ready, optionally create a munaqosah schedule and history
			if ($mahasiswa->siap_sidang) {
				$tanggal = Carbon::today()->addDays(rand(1, 30))->format('Y-m-d');
				$waktu_mulai = sprintf('%02d:00:00', rand(8, 14));
				$waktu_selesai = sprintf('%02d:00:00', intval(explode(':', $waktu_mulai)[0]) + 2);

				$muna = Munaqosah::firstOrCreate([
					'id_mahasiswa' => $mahasiswa->id,
				], [
					'tanggal_munaqosah' => $tanggal,
					'waktu_mulai' => $waktu_mulai,
					'waktu_selesai' => $waktu_selesai,
					'id_penguji1' => $pengujis[($i - 1) % $pengujis->count()]->id,
					'id_penguji2' => $pengujis[($i) % $pengujis->count()]->id,
					'id_ruang_ujian' => null,
					'status_konfirmasi' => 'pending',
				]);

				// Add an initial history entry
				HistoriMunaqosah::firstOrCreate([
					'id_munaqosah' => $muna->id,
					'perubahan' => 'Jadwal dibuat oleh seeder',
					'dilakukan_oleh' => null,
					'created_at' => Carbon::now(),
				]);
			}
		}

		// Create some jadwal penguji (block times) to create realistic conflicts
		$today = Carbon::today();
		$pengujis->each(function ($penguji, $index) use ($today) {
			// each penguji gets 1-2 blocked time slots
			$count = rand(1, 2);
			for ($j = 0; $j < $count; $j++) {
				$tanggal = $today->copy()->addDays(rand(0, 20))->format('Y-m-d');
				$start = sprintf('%02d:00:00', rand(8, 15));
				$end = sprintf('%02d:00:00', intval(explode(':', $start)[0]) + 2);

				JadwalPenguji::firstOrCreate([
					'id_penguji' => $penguji->id,
					'tanggal' => $tanggal,
					'waktu_mulai' => $start,
					'waktu_selesai' => $end,
				], [
					'deskripsi' => 'Kegiatan terjadwal',
				]);
			}
		});

		$this->command->info('✅ Mahasiswa & related seed data created (20 mahasiswa, 8 ready)');
	}
}
