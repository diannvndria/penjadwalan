<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\JadwalPenguji;
use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\RuangUjian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MunaqosahControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Mahasiswa $mahasiswa;

    protected Penguji $penguji1;

    protected Penguji $penguji2;

    protected RuangUjian $ruang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
        $dosen = Dosen::factory()->create();
        $this->mahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();
        $this->penguji1 = Penguji::factory()->create();
        $this->penguji2 = Penguji::factory()->create();
        $this->ruang = RuangUjian::factory()->create();
    }

    #[Test]
    public function it_displays_munaqosah_index_page(): void
    {
        Munaqosah::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('munaqosah.index'));

        $response->assertStatus(200);
        $response->assertViewIs('munaqosah.index');
        $response->assertViewHas('munaqosahs');
    }

    #[Test]
    public function it_filters_munaqosah_by_date_range(): void
    {
        $startDate = Carbon::tomorrow()->toDateString();
        $endDate = Carbon::tomorrow()->addDays(7)->toDateString();

        Munaqosah::factory()->create(['tanggal_munaqosah' => $this->formatDate(Carbon::tomorrow()->toDateString())]);

        $response = $this->actingAs($this->user)->get(route('munaqosah.index', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('startDate', $startDate);
        $response->assertViewHas('endDate', $endDate);
    }

    #[Test]
    public function it_filters_munaqosah_by_status(): void
    {
        Munaqosah::factory()->dikonfirmasi()->create();
        Munaqosah::factory()->create(['status_konfirmasi' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('munaqosah.index', [
            'status' => 'dikonfirmasi',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('status', 'dikonfirmasi');
    }

    #[Test]
    public function it_displays_munaqosah_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('munaqosah.create'));

        $response->assertStatus(200);
        $response->assertViewIs('munaqosah.create');
        $response->assertViewHas('mahasiswasSiapSidang');
        $response->assertViewHas('pengujis');
        $response->assertViewHas('ruangUjians');
    }

    #[Test]
    public function it_stores_new_munaqosah(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response->assertRedirect(route('munaqosah.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('munaqosah', [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
            'status_konfirmasi' => 'pending',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), []);

        $response->assertSessionHasErrors([
            'id_mahasiswa',
            'tanggal_munaqosah',
            'waktu_mulai',
            'waktu_selesai',
            'id_penguji1',
            'id_ruang_ujian',
        ]);
    }

    #[Test]
    public function it_validates_unique_mahasiswa(): void
    {
        $dosen = Dosen::factory()->create();
        $mahasiswaWithMunaqosah = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();
        Munaqosah::factory()->create(['id_mahasiswa' => $mahasiswaWithMunaqosah->nim]);

        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $mahasiswaWithMunaqosah->nim,
            'tanggal_munaqosah' => Carbon::tomorrow()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $this->penguji1->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response->assertSessionHasErrors('id_mahasiswa');
    }

    #[Test]
    public function it_validates_penguji1_and_penguji2_are_different(): void
    {
        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => Carbon::tomorrow()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji1->id, // Same as penguji1
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response->assertSessionHasErrors('id_penguji2');
    }

    #[Test]
    public function it_detects_penguji_conflict_with_jadwal_penguji(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create jadwal penguji yang bentrok
        JadwalPenguji::factory()->forPenguji($this->penguji1)->create([
            'tanggal' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
        ]);

        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response->assertSessionHasErrors('bentrok');
    }

    #[Test]
    public function it_detects_penguji_conflict_with_other_munaqosah(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create existing munaqosah with penguji1
        $dosen = Dosen::factory()->create();
        $otherMahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();
        Munaqosah::factory()->create([
            'id_mahasiswa' => $otherMahasiswa->nim,
            'tanggal_munaqosah' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
            'id_penguji1' => $this->penguji1->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '10:00', // Overlapping time
            'waktu_selesai' => '12:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response->assertSessionHasErrors('bentrok');
    }

    #[Test]
    public function it_detects_ruang_conflict(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        // Create existing munaqosah in same room
        $dosen = Dosen::factory()->create();
        $otherMahasiswa = Mahasiswa::factory()->siapSidang()->forDosen($dosen)->create();
        $otherPenguji1 = Penguji::factory()->create();
        $otherPenguji2 = Penguji::factory()->create();

        Munaqosah::factory()->create([
            'id_mahasiswa' => $otherMahasiswa->nim,
            'tanggal_munaqosah' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
            'id_penguji1' => $otherPenguji1->id,
            'id_penguji2' => $otherPenguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '10:00', // Overlapping time
            'waktu_selesai' => '12:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id, // Same room
        ]);

        if ($response->getSession()->get('errors')) {
            dump($response->getSession()->get('errors')->getMessages());
        }

        $response->assertSessionHasErrors('bentrok');
    }

    #[Test]
    public function it_displays_munaqosah_edit_form(): void
    {
        $munaqosah = Munaqosah::factory()->create();

        $response = $this->actingAs($this->user)->get(route('munaqosah.edit', $munaqosah));

        $response->assertStatus(200);
        $response->assertViewIs('munaqosah.edit');
        $response->assertViewHas('munaqosah');
    }

    #[Test]
    public function it_updates_existing_munaqosah(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();
        $munaqosah = Munaqosah::factory()->create([
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('09:00'),
            'waktu_selesai' => $this->formatTime('11:00'),
            'status_konfirmasi' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->put(route('munaqosah.update', $munaqosah), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '14:00',
            'waktu_selesai' => '16:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
            'status_konfirmasi' => 'dikonfirmasi',
        ]);

        $response->assertRedirect(route('munaqosah.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('munaqosah', [
            'id' => $munaqosah->id,
            'tanggal_munaqosah' => $this->formatDate($futureDate),
            'waktu_mulai' => $this->formatTime('14:00'),
            'waktu_selesai' => $this->formatTime('16:00'),
            'status_konfirmasi' => 'dikonfirmasi',
        ]);
    }

    #[Test]
    public function it_validates_status_konfirmasi_options(): void
    {
        $munaqosah = Munaqosah::factory()->create();

        $response = $this->actingAs($this->user)->put(route('munaqosah.update', $munaqosah), [
            'id_mahasiswa' => $munaqosah->id_mahasiswa,
            'tanggal_munaqosah' => Carbon::tomorrow()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $munaqosah->id_penguji1,
            'id_ruang_ujian' => $munaqosah->id_ruang_ujian,
            'status_konfirmasi' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status_konfirmasi');
    }

    #[Test]
    public function it_deletes_munaqosah(): void
    {
        $munaqosah = Munaqosah::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('munaqosah.destroy', $munaqosah));

        $response->assertRedirect(route('munaqosah.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('munaqosah', ['id' => $munaqosah->id]);
    }

    #[Test]
    public function it_bulk_deletes_munaqosahs(): void
    {
        $munaqosahs = Munaqosah::factory()->count(3)->create();
        $ids = $munaqosahs->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->post(route('munaqosah.bulk-delete'), [
            'ids' => $ids,
        ]);

        $response->assertRedirect();
        foreach ($munaqosahs as $munaqosah) {
            $this->assertDatabaseMissing('munaqosah', ['id' => $munaqosah->id]);
        }
    }

    #[Test]
    public function it_bulk_exports_munaqosah(): void
    {
        $munaqosahs = Munaqosah::factory()->count(3)->create();
        $ids = $munaqosahs->pluck('id')->implode(',');

        $response = $this->actingAs($this->user)->post(route('munaqosah.bulk-export'), [
            'ids' => $ids,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Jadwal_Sidang_'.now()->format('Y-m-d_H-i-s').'.csv"');
    }

    #[Test]
    public function it_creates_histori_on_munaqosah_creation(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        $this->actingAs($this->user)->post(route('munaqosah.store'), [
            'id_mahasiswa' => $this->mahasiswa->nim,
            'tanggal_munaqosah' => $futureDate,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'id_penguji1' => $this->penguji1->id,
            'id_penguji2' => $this->penguji2->id,
            'id_ruang_ujian' => $this->ruang->id,
        ]);

        $this->assertDatabaseHas('histori_munaqosah', [
            'dilakukan_oleh' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_sorts_munaqosah_by_field(): void
    {
        Munaqosah::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('munaqosah.index', [
            'sort' => 'tanggal_munaqosah',
            'direction' => 'desc',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sortField', 'tanggal_munaqosah');
        $response->assertViewHas('sortDirection', 'desc');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('munaqosah.index'));

        $response->assertRedirect(route('login'));
    }
}
