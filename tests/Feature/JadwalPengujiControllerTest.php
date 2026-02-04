<?php

namespace Tests\Feature;

use App\Models\JadwalPenguji;
use App\Models\Penguji;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JadwalPengujiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Penguji $penguji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
        $this->penguji = Penguji::factory()->create();
    }

    #[Test]
    public function it_displays_jadwal_penguji_index_page(): void
    {
        JadwalPenguji::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('jadwal-penguji.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jadwal_penguji.index');
        $response->assertViewHas('jadwalPengujis');
    }

    #[Test]
    public function it_displays_jadwal_penguji_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('jadwal-penguji.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jadwal_penguji.create');
        $response->assertViewHas('pengujis');
    }

    #[Test]
    public function it_stores_new_jadwal_penguji(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'deskripsi' => 'Meeting Jurusan',
        ]);

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('jadwal_penguji', [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), []);

        $response->assertSessionHasErrors(['id_penguji', 'tanggal', 'waktu_mulai', 'waktu_selesai']);
    }

    #[Test]
    public function it_validates_penguji_exists(): void
    {
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => 9999, // Non-existent
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
        ]);

        $response->assertSessionHasErrors('id_penguji');
    }

    #[Test]
    public function it_validates_tanggal_not_in_past(): void
    {
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => Carbon::yesterday()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
        ]);

        $response->assertSessionHasErrors('tanggal');
    }

    #[Test]
    public function it_validates_waktu_selesai_after_waktu_mulai(): void
    {
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'waktu_mulai' => '11:00',
            'waktu_selesai' => '09:00', // Before start time
        ]);

        $response->assertSessionHasErrors('waktu_selesai');
    }

    #[Test]
    public function it_detects_overlapping_schedule_on_store(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create existing jadwal 09:00 - 11:00
        JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        // Try to create overlapping jadwal 10:00 - 12:00
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '12:00',
        ]);

        $response->assertSessionHasErrors('bentrok');
    }

    #[Test]
    public function it_allows_non_overlapping_schedule(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create existing jadwal 09:00 - 11:00
        JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        // Create non-overlapping jadwal 13:00 - 15:00
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '13:00',
            'waktu_selesai' => '15:00',
        ]);

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function it_allows_same_time_for_different_penguji(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();
        $penguji2 = Penguji::factory()->create();

        // Create jadwal for penguji 1
        JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        // Create same time jadwal for penguji 2
        $response = $this->actingAs($this->user)->post(route('jadwal-penguji.store'), [
            'id_penguji' => $penguji2->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
        ]);

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function it_displays_jadwal_penguji_edit_form(): void
    {
        $jadwal = JadwalPenguji::factory()->forPenguji($this->penguji)->create();

        $response = $this->actingAs($this->user)->get(route('jadwal-penguji.edit', $jadwal));

        $response->assertStatus(200);
        $response->assertViewIs('jadwal_penguji.edit');
        $response->assertViewHas('jadwalPenguji');
        $response->assertViewHas('pengujis');
    }

    #[Test]
    public function it_updates_existing_jadwal_penguji(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();
        $jadwal = JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        $response = $this->actingAs($this->user)->put(route('jadwal-penguji.update', $jadwal), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '14:00',
            'waktu_selesai' => '16:00',
            'deskripsi' => 'Updated description',
        ]);

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('jadwal_penguji', [
            'id' => $jadwal->id,
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('14:00'),
            'waktu_selesai' => $this->formatTime('16:00'),
        ]);
    }

    #[Test]
    public function it_excludes_self_when_checking_overlap_on_update(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();
        $jadwal = JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        // Update same jadwal with same time should work
        $response = $this->actingAs($this->user)->put(route('jadwal-penguji.update', $jadwal), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'deskripsi' => 'Updated',
        ]);

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function it_detects_overlapping_schedule_on_update(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create two jadwals
        $jadwal1 = JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);
        JadwalPenguji::factory()->forPenguji($this->penguji)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('13:00'),
            'waktu_selesai' => $this->formatTime('15:00'),
        ]);

        // Try to update jadwal1 to overlap with jadwal2
        $response = $this->actingAs($this->user)->put(route('jadwal-penguji.update', $jadwal1), [
            'id_penguji' => $this->penguji->id,
            'tanggal' => $futureDate,
            'waktu_mulai' => '14:00',
            'waktu_selesai' => '16:00',
        ]);

        $response->assertSessionHasErrors('bentrok');
    }

    #[Test]
    public function it_deletes_jadwal_penguji(): void
    {
        $jadwal = JadwalPenguji::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('jadwal-penguji.destroy', $jadwal));

        $response->assertRedirect(route('jadwal-penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('jadwal_penguji', ['id' => $jadwal->id]);
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('jadwal-penguji.index'));

        $response->assertRedirect(route('login'));
    }
}
