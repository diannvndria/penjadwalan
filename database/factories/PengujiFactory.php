<?php

namespace Database\Factories;

use App\Models\Penguji;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penguji>
 */
class PengujiFactory extends Factory
{
    protected $model = Penguji::class;

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
            fake()->numerify('###')            // Sequence (3)
        );

        return [
            'nama' => fake()->name(),
            'nip' => $nip,
            'is_prioritas' => false,
            'keterangan_prioritas' => null,
        ];
    }

    /**
     * State untuk penguji prioritas.
     */
    public function prioritas(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_prioritas' => true,
            'keterangan_prioritas' => fake()->sentence(),
        ]);
    }
}
