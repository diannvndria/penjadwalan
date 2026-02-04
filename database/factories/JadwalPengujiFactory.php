<?php

namespace Database\Factories;

use App\Models\JadwalPenguji;
use App\Models\Penguji;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JadwalPenguji>
 */
class JadwalPengujiFactory extends Factory
{
    protected $model = JadwalPenguji::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $waktuMulai = fake()->time('H:i');
        $waktuSelesai = Carbon::createFromFormat('H:i', $waktuMulai)->addHours(2)->format('H:i');

        return [
            'id_penguji' => Penguji::factory(),
            'tanggal' => Carbon::now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'deskripsi' => fake()->sentence(),
        ];
    }

    /**
     * State untuk jadwal pada tanggal tertentu.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal' => $date,
        ]);
    }

    /**
     * State untuk jadwal pada waktu tertentu.
     */
    public function atTime(string $start, string $end): static
    {
        return $this->state(fn (array $attributes) => [
            'waktu_mulai' => $start,
            'waktu_selesai' => $end,
        ]);
    }

    /**
     * State untuk penguji tertentu.
     */
    public function forPenguji(Penguji $penguji): static
    {
        return $this->state(fn (array $attributes) => [
            'id_penguji' => $penguji->id,
        ]);
    }
}
