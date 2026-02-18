<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MahasiswaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Dosen $dosen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
        $this->dosen = Dosen::factory()->withCapacity(10)->create();
    }

    #[Test]
    public function it_displays_mahasiswa_index_page(): void
    {
        Mahasiswa::factory()->count(3)->forDosen($this->dosen)->create();

        $response = $this->actingAs($this->user)->get(route('mahasiswa.index'));

        $response->assertStatus(200);
        $response->assertViewIs('mahasiswa.index');
        $response->assertViewHas('mahasiswas');
    }

    #[Test]
    public function it_filters_mahasiswa_by_angkatan(): void
    {
        Mahasiswa::factory()->forDosen($this->dosen)->create(['angkatan' => 2023]);
        Mahasiswa::factory()->forDosen($this->dosen)->create(['angkatan' => 2024]);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.index', ['angkatan' => 2023]));

        $response->assertStatus(200);
        $response->assertViewHas('angkatan', '2023');
    }

    #[Test]
    public function it_filters_mahasiswa_by_search(): void
    {
        Mahasiswa::factory()->forDosen($this->dosen)->create(['nama' => 'John Doe']);
        Mahasiswa::factory()->forDosen($this->dosen)->create(['nama' => 'Jane Smith']);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.index', ['search' => 'John']));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filters_mahasiswa_by_dospem(): void
    {
        $dosen2 = Dosen::factory()->create();
        Mahasiswa::factory()->forDosen($this->dosen)->create();
        Mahasiswa::factory()->forDosen($dosen2)->create();

        $response = $this->actingAs($this->user)->get(route('mahasiswa.index', ['dospem_id' => $this->dosen->id]));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_displays_mahasiswa_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('mahasiswa.create'));

        $response->assertStatus(200);
        $response->assertViewIs('mahasiswa.create');
        $response->assertViewHas('dosens');
    }

    #[Test]
    public function it_stores_new_mahasiswa(): void
    {
        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul Skripsi Test',
            'profil_lulusan' => 'Ilmuwan',
            'penjurusan' => 'Sistem Informasi',
            'id_dospem' => $this->dosen->id,
            'siap_sidang' => false,
            'is_prioritas' => false,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), []);

        $response->assertSessionHasErrors(['nim', 'nama', 'angkatan', 'judul_skripsi', 'id_dospem']);
    }

    #[Test]
    public function it_validates_unique_nim(): void
    {
        Mahasiswa::factory()->forDosen($this->dosen)->create(['nim' => '1234567890']);

        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul Skripsi',
            'id_dospem' => $this->dosen->id,
        ]);

        $response->assertSessionHasErrors('nim');
    }

    #[Test]
    public function it_validates_angkatan_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 1990, // Before 2000
            'judul_skripsi' => 'Judul Skripsi',
            'id_dospem' => $this->dosen->id,
        ]);

        $response->assertSessionHasErrors('angkatan');
    }

    #[Test]
    public function it_validates_profil_lulusan_options(): void
    {
        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul Skripsi',
            'profil_lulusan' => 'InvalidOption',
            'id_dospem' => $this->dosen->id,
        ]);

        $response->assertSessionHasErrors('profil_lulusan');
    }

    #[Test]
    public function it_prevents_exceeding_dosen_capacity_on_store(): void
    {
        $dosenWithCapacity1 = Dosen::factory()->withCapacity(1)->create();
        Mahasiswa::factory()->forDosen($dosenWithCapacity1)->create();

        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul Skripsi',
            'id_dospem' => $dosenWithCapacity1->id,
        ]);

        $response->assertSessionHasErrors('id_dospem');
    }

    #[Test]
    public function it_allows_dosen_with_zero_capacity_unlimited_students(): void
    {
        $dosenUnlimited = Dosen::factory()->noCapacity()->create();
        Mahasiswa::factory()->count(5)->forDosen($dosenUnlimited)->create();

        $response = $this->actingAs($this->user)->post(route('mahasiswa.store'), [
            'nim' => '1234567890',
            'nama' => 'Test Mahasiswa',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul Skripsi',
            'id_dospem' => $dosenUnlimited->id,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function it_displays_mahasiswa_edit_form(): void
    {
        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create();

        $response = $this->actingAs($this->user)->get(route('mahasiswa.edit', $mahasiswa));

        $response->assertStatus(200);
        $response->assertViewIs('mahasiswa.edit');
        $response->assertViewHas('mahasiswa');
        $response->assertViewHas('dosens');
    }

    #[Test]
    public function it_updates_existing_mahasiswa(): void
    {
        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create([
            'nim' => '1234567890',
            'nama' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)->put(route('mahasiswa.update', $mahasiswa), [
            'nim' => '1234567890',
            'nama' => 'New Name',
            'angkatan' => 2024,
            'judul_skripsi' => 'New Judul',
            'id_dospem' => $this->dosen->id,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('mahasiswa', [
            'id' => $mahasiswa->id,
            'nama' => 'New Name',
        ]);
    }

    #[Test]
    public function it_allows_same_nim_on_update(): void
    {
        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create(['nim' => '1234567890']);

        $response = $this->actingAs($this->user)->put(route('mahasiswa.update', $mahasiswa), [
            'nim' => '1234567890', // Same NIM
            'nama' => 'Updated Name',
            'angkatan' => 2023,
            'judul_skripsi' => 'Judul',
            'id_dospem' => $this->dosen->id,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
    }

    #[Test]
    public function it_can_untick_siap_sidang_on_update(): void
    {
        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create([
            'siap_sidang' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('mahasiswa.update', $mahasiswa), [
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama,
            'angkatan' => $mahasiswa->angkatan,
            'judul_skripsi' => $mahasiswa->judul_skripsi,
            'id_dospem' => $this->dosen->id,
            'siap_sidang' => '0', // Value from hidden field
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $this->assertDatabaseHas('mahasiswa', [
            'id' => $mahasiswa->id,
            'siap_sidang' => false,
        ]);
    }

    #[Test]
    public function it_prevents_exceeding_new_dosen_capacity_on_update(): void
    {
        $dosenWithCapacity1 = Dosen::factory()->withCapacity(1)->create();
        Mahasiswa::factory()->forDosen($dosenWithCapacity1)->create();

        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create();

        $response = $this->actingAs($this->user)->put(route('mahasiswa.update', $mahasiswa), [
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama,
            'angkatan' => $mahasiswa->angkatan,
            'judul_skripsi' => $mahasiswa->judul_skripsi,
            'id_dospem' => $dosenWithCapacity1->id, // Try to switch to full dosen
        ]);

        $response->assertSessionHasErrors('id_dospem');
    }

    #[Test]
    public function it_deletes_mahasiswa(): void
    {
        $mahasiswa = Mahasiswa::factory()->forDosen($this->dosen)->create();

        $response = $this->actingAs($this->user)->delete(route('mahasiswa.destroy', $mahasiswa));

        $response->assertRedirect(route('mahasiswa.index'));
        $this->assertDatabaseMissing('mahasiswa', ['id' => $mahasiswa->id]);
    }

    #[Test]
    public function it_sorts_mahasiswa_by_field(): void
    {
        Mahasiswa::factory()->forDosen($this->dosen)->create(['nama' => 'Budi']);
        Mahasiswa::factory()->forDosen($this->dosen)->create(['nama' => 'Andi']);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.index', [
            'sort' => 'nama',
            'direction' => 'asc',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sortField', 'nama');
        $response->assertViewHas('sortDirection', 'asc');
    }

    #[Test]
    public function it_bulk_deletes_mahasiswa(): void
    {
        $mahasiswas = Mahasiswa::factory()->count(3)->forDosen($this->dosen)->create();
        $ids = $mahasiswas->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->delete(route('mahasiswa.bulk-delete'), [
            'ids' => $ids,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');
        foreach ($mahasiswas as $mahasiswa) {
            $this->assertDatabaseMissing('mahasiswa', ['id' => $mahasiswa->id]);
        }
    }

    #[Test]
    public function it_bulk_exports_mahasiswa(): void
    {
        $mahasiswas = Mahasiswa::factory()->count(3)->forDosen($this->dosen)->create();
        $ids = $mahasiswas->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->post(route('mahasiswa.bulk-export'), [
            'ids' => $ids,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Data_Mahasiswa_'.now()->format('Y-m-d_H-i-s').'.csv"');
    }

    #[Test]
    public function it_downloads_import_template(): void
    {
        $response = $this->actingAs($this->user)->get(route('mahasiswa.download-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Template_Import_Mahasiswa.csv"');
    }

    #[Test]
    public function it_imports_mahasiswa_from_csv(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('mahasiswa.csv',
            "NIM,Nama,Angkatan,NIP Dospem,Judul Skripsi\n".
            "123456,Test Import,2023,{$this->dosen->nip},Judul Skripsi Import"
        );

        $response = $this->actingAs($this->user)->post(route('mahasiswa.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '123456',
            'nama' => 'Test Import',
            'id_dospem' => $this->dosen->id,
        ]);
    }

    #[Test]
    public function it_handles_import_errors(): void
    {
        // CSV with missing required field (NIM)
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('mahasiswa_error.csv',
            "NIM,Nama,Angkatan,NIP Dospem,Judul Skripsi\n".
            ",Test Error,2023,{$this->dosen->nip},Judul Skripsi Error"
        );

        $response = $this->actingAs($this->user)->post(route('mahasiswa.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('mahasiswa.index'));

        $response->assertRedirect(route('login'));
    }
}
