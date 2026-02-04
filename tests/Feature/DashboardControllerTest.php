<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
    }

    #[Test]
    public function it_displays_dashboard_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    #[Test]
    public function it_shows_correct_total_mahasiswa_count(): void
    {
        $dosen = Dosen::factory()->create();
        Mahasiswa::factory()->count(5)->forDosen($dosen)->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('total_mahasiswa', 5);
    }

    #[Test]
    public function it_shows_correct_ready_for_defense_count(): void
    {
        $dosen = Dosen::factory()->create();

        // Create 3 mahasiswa siap sidang tanpa munaqosah
        Mahasiswa::factory()->count(3)->siapSidang()->forDosen($dosen)->create();

        // Create 2 mahasiswa tidak siap sidang
        Mahasiswa::factory()->count(2)->forDosen($dosen)->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('ready_for_defense_count', 3);
    }

    #[Test]
    public function it_shows_correct_upcoming_schedules_count(): void
    {
        // Create munaqosah dengan tanggal masa depan
        Munaqosah::factory()->count(2)
            ->state(['tanggal_munaqosah' => now()->addDays(5)])
            ->create();

        // Create munaqosah dengan tanggal lampau (tidak dihitung)
        Munaqosah::factory()
            ->state(['tanggal_munaqosah' => now()->subDays(5)])
            ->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('upcoming_schedules_count', 2);
    }

    #[Test]
    public function it_shows_upcoming_munaqosahs_list(): void
    {
        Munaqosah::factory()
            ->state(['tanggal_munaqosah' => now()->addDays(3)])
            ->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('upcoming_munaqosahs');
    }

    #[Test]
    public function it_shows_ready_students_list(): void
    {
        $dosen = Dosen::factory()->create();
        Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('ready_students');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
