<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\JadwalPenguji;
use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\RuangUjian;
use App\Models\User;
use App\Services\AutoScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AutoScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected AutoScheduleService $autoScheduleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();
        $this->regularUser = User::factory()->create(['role' => 'user']);
        $this->autoScheduleService = app(AutoScheduleService::class);
    }

    #[Test]
    public function it_can_schedule_ready_student_automatically(): void
    {
        // Setup test data
        // Create pengujis first to avoid ID collision with dosen (both tables start at ID 1)
        Penguji::factory()->count(3)->create();
        $dosen = Dosen::factory()->create();
        RuangUjian::factory()->create();

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->nim);

        // Assertions
        $this->assertTrue($result['success'], $result['message'] ?? 'Unknown error');
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa->nim,
            'status_konfirmasi' => 'pending',
        ]);
    }

    #[Test]
    public function it_handles_no_available_slot_gracefully(): void
    {
        // Create pengujis first and make them both busy for the next 60 days
        $penguji1 = Penguji::factory()->create();
        $penguji2 = Penguji::factory()->create();

        // Setup test data with all pengujis busy
        $dosen = Dosen::factory()->create();

        // Block all working days for the next 60 days (full day coverage)
        $today = Carbon::today();
        for ($i = 1; $i <= 60; $i++) {
            $date = $today->copy()->addDays($i);
            // Only create jadwal for weekdays
            if ($date->isWeekday()) {
                JadwalPenguji::factory()->forPenguji($penguji1)->create([
                    'tanggal' => $date->format('Y-m-d'),
                    'waktu_mulai' => '08:00:00',
                    'waktu_selesai' => '16:00:00',
                ]);

                JadwalPenguji::factory()->forPenguji($penguji2)->create([
                    'tanggal' => $date->format('Y-m-d'),
                    'waktu_mulai' => '08:00:00',
                    'waktu_selesai' => '16:00:00',
                ]);
            }
        }

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->nim);

        // Assertions
        $this->assertFalse($result['success']);
        $this->assertDatabaseMissing('munaqosah', [
            'id_mahasiswa' => $mahasiswa->nim,
        ]);
    }

    #[Test]
    public function it_prevents_scheduling_already_scheduled_student(): void
    {
        // Setup test data
        $dosen = Dosen::factory()->create();
        $penguji1 = Penguji::factory()->create();
        $penguji2 = Penguji::factory()->create();
        $ruang = RuangUjian::factory()->create();

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        // Create existing munaqosah
        Munaqosah::factory()->create([
            'id_mahasiswa' => $mahasiswa->nim,
            'id_penguji1' => $penguji1->id,
            'id_penguji2' => $penguji2->id,
            'id_ruang_ujian' => $ruang->id,
        ]);

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->nim);

        // Assertions
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah memiliki jadwal', $result['message']);
    }

    #[Test]
    public function it_can_batch_schedule_multiple_students(): void
    {
        // Setup test data - create pengujis first to avoid ID collision with dosen
        Penguji::factory()->count(4)->create();
        RuangUjian::factory()->count(2)->create();
        $dosen = Dosen::factory()->create();

        // Create multiple students ready for defense
        $mahasiswa1 = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();
        $mahasiswa2 = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        // Test batch auto-schedule
        $result = $this->autoScheduleService->batchScheduleAll();

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['scheduled_count']);
        $this->assertEquals(0, $result['failed_count']);
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa1->nim,
        ]);
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa2->nim,
        ]);
    }

    #[Test]
    public function admin_can_access_auto_schedule_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/auto-schedule');

        $response->assertStatus(200);
    }

    #[Test]
    public function non_admin_cannot_access_auto_schedule_page(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/auto-schedule');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_get_ready_students_via_api(): void
    {
        // Setup test data
        $dosen = Dosen::factory()->create();

        $readyStudent = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create([
            'nama' => 'Ready Student',
        ]);

        Mahasiswa::factory()->forDosen($dosen)->create([
            'nama' => 'Not Ready Student',
            'siap_sidang' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/auto-schedule/ready-students');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ])
            ->assertJsonFragment([
                'nama' => 'Ready Student',
            ]);
    }

    #[Test]
    public function it_can_schedule_individual_student_via_api(): void
    {
        // Setup test data - create pengujis first to avoid ID collision with dosen
        Penguji::factory()->count(3)->create();
        RuangUjian::factory()->create();
        $dosen = Dosen::factory()->create();

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        $response = $this->actingAs($this->adminUser)
            ->postJson('/auto-schedule/schedule-student', [
                'mahasiswa_id' => $mahasiswa->nim,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_can_simulate_scheduling(): void
    {
        // Setup test data - create pengujis first to avoid ID collision with dosen
        Penguji::factory()->count(3)->create();
        RuangUjian::factory()->create();
        $dosen = Dosen::factory()->create();

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        $response = $this->actingAs($this->adminUser)
            ->postJson('/auto-schedule/simulate', [
                'mahasiswa_id' => $mahasiswa->nim,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'simulation',
            ]);

        // Ensure no actual munaqosah was created
        $this->assertDatabaseMissing('munaqosah', [
            'id_mahasiswa' => $mahasiswa->nim,
        ]);
    }

    #[Test]
    public function it_requires_minimum_two_pengujis(): void
    {
        $dosen = Dosen::factory()->create();
        Penguji::factory()->create(); // Only 1 penguji
        RuangUjian::factory()->create();

        $mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();

        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->nim);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('2 penguji', $result['message']);
    }

    #[Test]
    public function it_can_get_configuration(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/auto-schedule/configuration');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'default_duration_minutes',
                    'working_hours',
                    'working_days',
                ],
            ]);
    }

    #[Test]
    public function it_can_update_configuration(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->putJson('/auto-schedule/configuration', [
                'duration_minutes' => 90,
                'working_hours' => [
                    'start' => '09:00',
                    'end' => '17:00',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function it_validates_configuration_update(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->putJson('/auto-schedule/configuration', [
                'duration_minutes' => 10, // Below minimum of 30
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function guest_cannot_access_auto_schedule(): void
    {
        $response = $this->get('/auto-schedule');

        $response->assertRedirect(route('login'));
    }
}
