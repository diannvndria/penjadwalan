<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RuangUjianSeeder;
use Database\Seeders\AllDataSeeder;
use Database\Seeders\MahasiswaReadySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            RuangUjianSeeder::class,
            AllDataSeeder::class,
            MahasiswaReadySeeder::class,
        ]);
    }
}
