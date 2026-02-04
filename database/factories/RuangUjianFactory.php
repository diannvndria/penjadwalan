<?php

namespace Database\Factories;

use App\Models\RuangUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RuangUjian>
 */
class RuangUjianFactory extends Factory
{
    protected $model = RuangUjian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Ruang '.fake()->unique()->numberBetween(100, 999),
            'lokasi' => fake()->address(),
            'kapasitas' => fake()->numberBetween(10, 50),
            'is_aktif' => true,
            'lantai' => fake()->numberBetween(1, 5),
            'is_prioritas' => false,
        ];
    }

    /**
     * State untuk ruang tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_aktif' => false,
        ]);
    }

    /**
     * State untuk ruang prioritas.
     */
    public function prioritas(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_prioritas' => true,
        ]);
    }
}
