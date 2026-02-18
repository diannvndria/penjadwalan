<?php

namespace Database\Factories;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dosen>
 */
class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a realistic birth date (e.g., between 20 and 50 years ago)
        $birthDate = fake()->dateTimeBetween('-50 years', '-20 years');

        // Generate an appointment date (must be after they turned 18)
        $appointmentDate = fake()->dateTimeBetween($birthDate->format('Y-m-d').' +18 years', 'now');

        $nip = sprintf(
            '%s%s%d%s',
            $birthDate->format('Ymd'),         // Birth date (8)
            $appointmentDate->format('Ym'),    // Appointment date (6)
            fake()->numberBetween(1, 2),       // Gender (1)
            fake()->unique()->numerify('###')            // Sequence (3)
        );

        return [
            'nama' => fake()->name(),
            'nip' => $nip,
            'kapasitas_ampu' => fake()->numberBetween(5, 20),
        ];
    }

    /**
     * State untuk dosen dengan kapasitas penuh (0).
     */
    public function noCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'kapasitas_ampu' => 0,
        ]);
    }

    /**
     * State untuk dosen dengan kapasitas tertentu.
     */
    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'kapasitas_ampu' => $capacity,
        ]);
    }
}
