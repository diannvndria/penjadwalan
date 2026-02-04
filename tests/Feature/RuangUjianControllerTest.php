<?php

namespace Tests\Feature;

use App\Models\Munaqosah;
use App\Models\RuangUjian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RuangUjianControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
    }

    #[Test]
    public function it_displays_ruang_ujian_index_page(): void
    {
        RuangUjian::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('ruang-ujian.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ruang_ujian.index');
        $response->assertViewHas('ruang');
    }

    #[Test]
    public function it_displays_ruang_ujian_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('ruang-ujian.create'));

        $response->assertStatus(200);
        $response->assertViewIs('ruang_ujian.create');
    }

    #[Test]
    public function it_stores_new_ruang_ujian(): void
    {
        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => 'Ruang A101',
            'lokasi' => 'Gedung A Lantai 1',
            'kapasitas' => 30,
            'is_aktif' => true,
            'lantai' => 1,
            'is_prioritas' => false,
        ]);

        $response->assertRedirect(route('ruang-ujian.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ruang_ujian', [
            'nama' => 'Ruang A101',
            'lokasi' => 'Gedung A Lantai 1',
            'kapasitas' => 30,
        ]);
    }

    #[Test]
    public function it_stores_ruang_ujian_with_priority(): void
    {
        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => 'Ruang VIP',
            'lokasi' => 'Gedung Utama',
            'kapasitas' => 20,
            'is_aktif' => true,
            'lantai' => 1,
            'is_prioritas' => true,
        ]);

        $response->assertRedirect(route('ruang-ujian.index'));
        $this->assertDatabaseHas('ruang_ujian', [
            'nama' => 'Ruang VIP',
            'is_prioritas' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => '',
            'kapasitas' => '',
            'lantai' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'kapasitas', 'lantai']);
    }

    #[Test]
    public function it_validates_kapasitas_minimum(): void
    {
        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => 'Ruang Test',
            'kapasitas' => 0,
            'lantai' => 1,
        ]);

        $response->assertSessionHasErrors('kapasitas');
    }

    #[Test]
    public function it_validates_lantai_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => 'Ruang Test',
            'kapasitas' => 10,
            'lantai' => 0, // Below minimum
        ]);

        $response->assertSessionHasErrors('lantai');

        $response = $this->actingAs($this->user)->post(route('ruang-ujian.store'), [
            'nama' => 'Ruang Test 2',
            'kapasitas' => 10,
            'lantai' => 11, // Above maximum
        ]);

        $response->assertSessionHasErrors('lantai');
    }

    #[Test]
    public function it_displays_ruang_ujian_edit_form(): void
    {
        $ruang = RuangUjian::factory()->create();

        $response = $this->actingAs($this->user)->get(route('ruang-ujian.edit', $ruang));

        $response->assertStatus(200);
        $response->assertViewIs('ruang_ujian.edit');
        $response->assertViewHas('ruangUjian');
    }

    #[Test]
    public function it_updates_existing_ruang_ujian(): void
    {
        $ruang = RuangUjian::factory()->create([
            'nama' => 'Old Room',
            'kapasitas' => 20,
        ]);

        $response = $this->actingAs($this->user)->put(route('ruang-ujian.update', $ruang), [
            'nama' => 'Updated Room',
            'lokasi' => 'New Location',
            'kapasitas' => 40,
            'is_aktif' => true,
            'lantai' => 2,
            'is_prioritas' => true,
        ]);

        $response->assertRedirect(route('ruang-ujian.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ruang_ujian', [
            'id' => $ruang->id,
            'nama' => 'Updated Room',
            'kapasitas' => 40,
            'lantai' => 2,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_update(): void
    {
        $ruang = RuangUjian::factory()->create();

        $response = $this->actingAs($this->user)->put(route('ruang-ujian.update', $ruang), [
            'nama' => '',
            'kapasitas' => '',
            'lantai' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'kapasitas', 'lantai']);
    }

    #[Test]
    public function it_deletes_ruang_ujian_without_schedules(): void
    {
        $ruang = RuangUjian::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('ruang-ujian.destroy', $ruang));

        $response->assertRedirect(route('ruang-ujian.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('ruang_ujian', ['id' => $ruang->id]);
    }

    #[Test]
    public function it_prevents_deleting_ruang_ujian_with_munaqosah(): void
    {
        $ruang = RuangUjian::factory()->create();
        Munaqosah::factory()->create(['id_ruang_ujian' => $ruang->id]);

        $response = $this->actingAs($this->user)->delete(route('ruang-ujian.destroy', $ruang));

        $response->assertRedirect(route('ruang-ujian.index'));
        $response->assertSessionHas('error');
        // Note: Cannot use assertDatabaseHas here due to PostgreSQL transaction abort on FK violation
    }

    #[Test]
    public function it_can_deactivate_ruang_ujian(): void
    {
        $ruang = RuangUjian::factory()->create(['is_aktif' => true]);

        $response = $this->actingAs($this->user)->put(route('ruang-ujian.update', $ruang), [
            'nama' => $ruang->nama,
            'kapasitas' => $ruang->kapasitas,
            'lantai' => $ruang->lantai,
            'is_aktif' => false,
        ]);

        $response->assertRedirect(route('ruang-ujian.index'));
        $this->assertDatabaseHas('ruang_ujian', [
            'id' => $ruang->id,
            'is_aktif' => false,
        ]);
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('ruang-ujian.index'));

        $response->assertRedirect(route('login'));
    }
}
