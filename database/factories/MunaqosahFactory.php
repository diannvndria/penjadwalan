<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\RuangUjian;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Munaqosah>
 */
class MunaqosahFactory extends Factory
{
    protected $model = Munaqosah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $waktuMulai = fake()->randomElement(['08:00', '10:00', '13:00', '15:00']);
        $waktuSelesai = Carbon::createFromFormat('H:i', $waktuMulai)->addHours(2)->format('H:i');

        return [
            'id_mahasiswa' => Mahasiswa::factory()->siapSidang(),
            'tanggal_munaqosah' => Carbon::now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'id_penguji1' => Penguji::factory(),
            'id_penguji2' => Penguji::factory(),
            'id_ruang_ujian' => RuangUjian::factory(),
            'status_konfirmasi' => 'pending',
        ];
    }

    /**
     * State untuk status dikonfirmasi.
     */
    public function dikonfirmasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_konfirmasi' => 'dikonfirmasi',
        ]);
    }

    /**
     * State untuk status ditolak.
     */
    public function ditolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_konfirmasi' => 'ditolak',
        ]);
    }

    /**
     * State untuk tanggal tertentu.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal_munaqosah' => $date,
        ]);
    }

    /**
     * State untuk waktu tertentu.
     */
    public function atTime(string $start, string $end): static
    {
        return $this->state(fn (array $attributes) => [
            'waktu_mulai' => $start,
            'waktu_selesai' => $end,
        ]);
    }
}
