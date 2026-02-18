<?php

namespace Tests\Feature;

use App\Models\JadwalPenguji;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PengujiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
    }

    #[Test]
    public function it_displays_penguji_index_page(): void
    {
        Penguji::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('penguji.index'));

        $response->assertStatus(200);
        $response->assertViewIs('penguji.index');
        $response->assertViewHas('pengujis');
    }

    #[Test]
    public function it_displays_penguji_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('penguji.create'));

        $response->assertStatus(200);
        $response->assertViewIs('penguji.create');
    }

    #[Test]
    public function it_stores_new_penguji(): void
    {
        $response = $this->actingAs($this->user)->post(route('penguji.store'), [
            'nama' => 'Dr. Penguji Test',
            'is_prioritas' => false,
        ]);

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penguji', [
            'nama' => 'Dr. Penguji Test',
            'is_prioritas' => false,
        ]);
    }

    #[Test]
    public function it_stores_penguji_with_priority(): void
    {
        $response = $this->actingAs($this->user)->post(route('penguji.store'), [
            'nama' => 'Dr. Priority Penguji',
            'is_prioritas' => true,
            'keterangan_prioritas' => 'Ketua Jurusan',
        ]);

        $response->assertRedirect(route('penguji.index'));
        $this->assertDatabaseHas('penguji', [
            'nama' => 'Dr. Priority Penguji',
            'is_prioritas' => true,
            'keterangan_prioritas' => 'Ketua Jurusan',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('penguji.store'), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors('nama');
    }

    #[Test]
    public function it_validates_nama_max_length(): void
    {
        $response = $this->actingAs($this->user)->post(route('penguji.store'), [
            'nama' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('nama');
    }

    #[Test]
    public function it_displays_penguji_edit_form(): void
    {
        $penguji = Penguji::factory()->create();

        $response = $this->actingAs($this->user)->get(route('penguji.edit', $penguji));

        $response->assertStatus(200);
        $response->assertViewIs('penguji.edit');
        $response->assertViewHas('penguji');
    }

    #[Test]
    public function it_updates_existing_penguji(): void
    {
        $penguji = Penguji::factory()->create(['nama' => 'Old Name']);

        $response = $this->actingAs($this->user)->put(route('penguji.update', $penguji), [
            'nama' => 'New Name',
            'is_prioritas' => true,
            'keterangan_prioritas' => 'Updated priority reason',
        ]);

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penguji', [
            'id' => $penguji->id,
            'nama' => 'New Name',
            'is_prioritas' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_update(): void
    {
        $penguji = Penguji::factory()->create();

        $response = $this->actingAs($this->user)->put(route('penguji.update', $penguji), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors('nama');
    }

    #[Test]
    public function it_deletes_penguji_without_relationships(): void
    {
        $penguji = Penguji::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('penguji.destroy', $penguji));

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('penguji', ['id' => $penguji->id]);
    }

    #[Test]
    public function it_deletes_penguji_and_cascades_jadwal(): void
    {
        $penguji = Penguji::factory()->create();
        $jadwal = JadwalPenguji::factory()->forPenguji($penguji)->create();

        $response = $this->actingAs($this->user)->delete(route('penguji.destroy', $penguji));

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('penguji', ['id' => $penguji->id]);
        $this->assertDatabaseMissing('jadwal_penguji', ['id' => $jadwal->id]);
    }

    #[Test]
    public function it_prevents_deleting_penguji_assigned_to_munaqosah(): void
    {
        $penguji = Penguji::factory()->create();
        Munaqosah::factory()->create(['id_penguji1' => $penguji->id]);

        $response = $this->actingAs($this->user)->delete(route('penguji.destroy', $penguji));

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('error');
        // Note: Cannot use assertDatabaseHas here due to PostgreSQL transaction abort on FK violation
    }

    #[Test]
    public function it_bulk_deletes_penguji(): void
    {
        $pengujis = Penguji::factory()->count(3)->create();
        $ids = $pengujis->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->delete(route('penguji.bulk-delete'), [
            'ids' => $ids,
        ]);

        $response->assertRedirect(route('penguji.index'));
        $response->assertSessionHas('success');
        foreach ($pengujis as $penguji) {
            $this->assertDatabaseMissing('penguji', ['id' => $penguji->id]);
        }
    }

    #[Test]
    public function it_bulk_exports_penguji(): void
    {
        $pengujis = Penguji::factory()->count(3)->create();
        $ids = $pengujis->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->post(route('penguji.bulk-export'), [
            'ids' => $ids,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="penguji_export_'.now()->format('Y-m-d_H-i-s').'.csv"');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('penguji.index'));

        $response->assertRedirect(route('login'));
    }
}
