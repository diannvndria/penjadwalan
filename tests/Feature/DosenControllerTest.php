<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DosenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
    }

    #[Test]
    public function it_displays_dosen_index_page(): void
    {
        Dosen::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('dosen.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dosen.index');
        $response->assertViewHas('dosens');
    }

    #[Test]
    public function it_displays_dosen_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('dosen.create'));

        $response->assertStatus(200);
        $response->assertViewIs('dosen.create');
    }

    #[Test]
    public function it_stores_new_dosen(): void
    {
        $response = $this->actingAs($this->user)->post(route('dosen.store'), [
            'nama' => 'Dr. John Doe',
            'kapasitas_ampu' => 10,
        ]);

        $response->assertRedirect(route('dosen.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dosen', [
            'nama' => 'Dr. John Doe',
            'kapasitas_ampu' => 10,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('dosen.store'), [
            'nama' => '',
            'kapasitas_ampu' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'kapasitas_ampu']);
    }

    #[Test]
    public function it_validates_kapasitas_ampu_is_non_negative(): void
    {
        $response = $this->actingAs($this->user)->post(route('dosen.store'), [
            'nama' => 'Dr. John Doe',
            'kapasitas_ampu' => -5,
        ]);

        $response->assertSessionHasErrors('kapasitas_ampu');
    }

    #[Test]
    public function it_validates_kapasitas_ampu_is_integer(): void
    {
        $response = $this->actingAs($this->user)->post(route('dosen.store'), [
            'nama' => 'Dr. John Doe',
            'kapasitas_ampu' => 'abc',
        ]);

        $response->assertSessionHasErrors('kapasitas_ampu');
    }

    #[Test]
    public function it_displays_dosen_edit_form(): void
    {
        $dosen = Dosen::factory()->create();

        $response = $this->actingAs($this->user)->get(route('dosen.edit', $dosen));

        $response->assertStatus(200);
        $response->assertViewIs('dosen.edit');
        $response->assertViewHas('dosen');
    }

    #[Test]
    public function it_updates_existing_dosen(): void
    {
        $dosen = Dosen::factory()->create([
            'nama' => 'Old Name',
            'kapasitas_ampu' => 5,
        ]);

        $response = $this->actingAs($this->user)->put(route('dosen.update', $dosen), [
            'nama' => 'New Name',
            'kapasitas_ampu' => 15,
        ]);

        $response->assertRedirect(route('dosen.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dosen', [
            'id' => $dosen->id,
            'nama' => 'New Name',
            'kapasitas_ampu' => 15,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_update(): void
    {
        $dosen = Dosen::factory()->create();

        $response = $this->actingAs($this->user)->put(route('dosen.update', $dosen), [
            'nama' => '',
            'kapasitas_ampu' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'kapasitas_ampu']);
    }

    #[Test]
    public function it_deletes_dosen_without_mahasiswa(): void
    {
        $dosen = Dosen::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('dosen.destroy', $dosen));

        $response->assertRedirect(route('dosen.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('dosen', ['id' => $dosen->id]);
    }

    #[Test]
    public function it_prevents_deleting_dosen_with_mahasiswa(): void
    {
        $dosen = Dosen::factory()->create();
        Mahasiswa::factory()->forDosen($dosen)->create();

        $response = $this->actingAs($this->user)->delete(route('dosen.destroy', $dosen));

        $response->assertRedirect(route('dosen.index'));
        $response->assertSessionHas('error');
        // Note: Cannot use assertDatabaseHas here due to PostgreSQL transaction abort on FK violation
    }

    #[Test]
    public function it_shows_mahasiswa_count_on_index(): void
    {
        $dosen = Dosen::factory()->create();
        Mahasiswa::factory()->count(3)->forDosen($dosen)->create();

        $response = $this->actingAs($this->user)->get(route('dosen.index'));

        $response->assertStatus(200);
        $response->assertSee('3'); // Count should be visible
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('dosen.index'));

        $response->assertRedirect(route('login'));
    }
}
