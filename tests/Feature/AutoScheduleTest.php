<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\JadwalPenguji;
use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\User;
use App\Services\AutoScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected $autoScheduleService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->autoScheduleService = app(AutoScheduleService::class);
    }

    /** @test */
    public function it_can_schedule_ready_student_automatically()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);

        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->id);

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa->id,
            'status_konfirmasi' => 'pending',
        ]);
        $this->assertDatabaseHas('histori_munaqosah', [
            'perubahan' => 'Jadwal dibuat otomatis oleh sistem',
        ]);
    }

    /** @test */
    public function it_handles_no_available_slot_gracefully()
    {
        // Setup test data with all pengujis busy
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);

        // Create only 2 pengujis and make them both busy for the next week
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);

        $today = Carbon::today();
        for ($i = 0; $i < 7; $i++) {
            JadwalPenguji::create([
                'id_penguji' => $penguji1->id,
                'tanggal' => $today->copy()->addDays($i)->format('Y-m-d'),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '16:00:00',
                'deskripsi' => 'Busy all day',
            ]);

            JadwalPenguji::create([
                'id_penguji' => $penguji2->id,
                'tanggal' => $today->copy()->addDays($i)->format('Y-m-d'),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '16:00:00',
                'deskripsi' => 'Busy all day',
            ]);
        }

        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->id);

        // Assertions
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Tidak ada slot', $result['message']);
        $this->assertDatabaseMissing('munaqosah', [
            'id_mahasiswa' => $mahasiswa->id,
        ]);
    }

    /** @test */
    public function it_prevents_scheduling_already_scheduled_student()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);

        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        // Create existing munaqosah
        Munaqosah::create([
            'id_mahasiswa' => $mahasiswa->id,
            'tanggal_munaqosah' => Carbon::today()->addDay()->format('Y-m-d'),
            'waktu_mulai' => '10:00:00',
            'waktu_selesai' => '12:00:00',
            'id_penguji1' => $penguji1->id,
            'id_penguji2' => $penguji2->id,
            'status_konfirmasi' => 'pending',
        ]);

        // Test auto-schedule
        $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->id);

        // Assertions
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah memiliki jadwal', $result['message']);
    }

    /** @test */
    public function it_can_batch_schedule_multiple_students()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);
        $penguji3 = Penguji::create(['nama' => 'Dr. Test Penguji 3']);
        $penguji4 = Penguji::create(['nama' => 'Dr. Test Penguji 4']);

        // Create multiple students ready for defense
        $mahasiswa1 = Mahasiswa::create([
            'nim' => '123456781',
            'nama' => 'Test Mahasiswa 1',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi 1',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        $mahasiswa2 = Mahasiswa::create([
            'nim' => '123456782',
            'nama' => 'Test Mahasiswa 2',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi 2',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        // Test batch auto-schedule
        $result = $this->autoScheduleService->batchScheduleAll();

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['scheduled_count']);
        $this->assertEquals(0, $result['failed_count']);
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa1->id,
        ]);
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $mahasiswa2->id,
        ]);
    }

    /** @test */
    public function admin_can_access_auto_schedule_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/auto-schedule');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_auto_schedule_page()
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($regularUser)
            ->get('/auto-schedule');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_get_ready_students_via_api()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);

        $readyStudent = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Ready Student',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        $notReadyStudent = Mahasiswa::create([
            'nim' => '123456788',
            'nama' => 'Not Ready Student',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi 2',
            'id_dospem' => $dosen->id,
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

    /** @test */
    public function it_can_schedule_individual_student_via_api()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);

        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/auto-schedule/schedule-student', [
                'mahasiswa_id' => $mahasiswa->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_can_simulate_scheduling()
    {
        // Setup test data
        $dosen = Dosen::create(['nama' => 'Dr. Test Dosen']);
        $penguji1 = Penguji::create(['nama' => 'Dr. Test Penguji 1']);
        $penguji2 = Penguji::create(['nama' => 'Dr. Test Penguji 2']);

        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2021,
            'judul_skripsi' => 'Test Judul Skripsi',
            'id_dospem' => $dosen->id,
            'siap_sidang' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/auto-schedule/simulate', [
                'mahasiswa_id' => $mahasiswa->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'simulation',
            ]);

        // Ensure no actual munaqosah was created
        $this->assertDatabaseMissing('munaqosah', [
            'id_mahasiswa' => $mahasiswa->id,
        ]);
    }
}
