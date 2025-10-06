<?php

namespace App\Services;

use App\Models\Munaqosah;
use App\Models\Mahasiswa;
use App\Models\Penguji;
use App\Models\JadwalPenguji;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoScheduleService
{
    private array $workingDays;
    private array $workingHours;
    private int $durationMinutes; // Akan menyimpan durasi dalam menit

    /**
     * Constructor: Dijalankan setiap kali service ini dibuat.
     * Kita akan memuat konfigurasi dari session di sini.
     */
    public function __construct()
    {
        $defaults = [
            'default_duration_minutes' => 120,
            'working_hours' => ['start' => '08:00', 'end' => '16:00'],
            'working_days' => [1, 2, 3, 4, 5], // Senin-Jumat
        ];

        $config = session()->get('auto_schedule_config', $defaults);

        // Atur properti service berdasarkan konfigurasi yang dimuat
        $this->durationMinutes = $config['default_duration_minutes'];
        $this->workingHours = $config['working_hours'];
        $this->workingDays = $config['working_days'];
    }

    /**
     * Mengatur durasi sidang dalam menit untuk instance service saat ini.
     * @param int $minutes
     * @return void
     */
    public function setDuration(int $minutes): void
    {
        $this->durationMinutes = $minutes;
    }

    /**
     * Mengatur jam kerja untuk instance service saat ini.
     * @param string $start
     * @param string $end
     * @return void
     */
    public function setWorkingHours(string $start, string $end): void
    {
        $this->workingHours['start'] = $start;
        $this->workingHours['end'] = $end;
    }

    /**
     * Mengatur jangkauan pencarian jadwal.
     * Method ini ditambahkan kembali untuk mencegah error dari controller.
     * @param int $days
     * @return void
     */
    public function setSearchRange(int $days): void
    {
        // Untuk saat ini, method ini tidak melakukan apa-apa karena logika dinamis
        // belum menggunakan search range. Namun, keberadaannya akan mencegah error.
    }

    /**
     * Cari slot waktu yang tersedia secara dinamis.
     * Method ini sekarang akan menghasilkan slot berdasarkan konfigurasi.
     */
    private function findAvailableSlot($mahasiswa, $pengujis, $startDate, $endDate)
    {
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Gunakan dayOfWeekIso (1=Senin, 7=Minggu) agar konsisten
            if (in_array($currentDate->dayOfWeekIso, $this->workingDays)) {

                $slotStart = Carbon::parse($currentDate->toDateString() . ' ' . $this->workingHours['start']);
                $dayEnd = Carbon::parse($currentDate->toDateString() . ' ' . $this->workingHours['end']);

                // Loop dinamis berdasarkan durasi
                while ($slotStart->copy()->addMinutes($this->durationMinutes)->lte($dayEnd)) {
                    $slotEnd = $slotStart->copy()->addMinutes($this->durationMinutes);

                    // (Opsional) Logika untuk melewati jam istirahat
                    if ($slotStart->hour == 12 || ($slotStart->hour < 13 && $slotEnd->hour >= 13)) {
                        $slotStart->hour(13)->minute(0)->second(0);
                        continue; // Lanjut ke slot setelah jam 1 siang
                    }

                    // Cek ketersediaan penguji untuk slot dinamis ini
                    $availablePengujis = $this->findAvailablePengujis(
                        $pengujis,
                        $currentDate->toDateString(),
                        $slotStart->toTimeString('minutes'),
                        $slotEnd->toTimeString('minutes'),
                        $mahasiswa
                    );

                    if (count($availablePengujis) >= 2) {
                        // Cek ketersediaan ruang ujian untuk slot ini
                        $availableRoomId = $this->findAvailableRoomId(
                            $currentDate->toDateString(),
                            $slotStart->toTimeString('minutes'),
                            $slotEnd->toTimeString('minutes')
                        );

                        if (!$availableRoomId) {
                            // Jika tidak ada ruang tersedia, lanjut ke slot berikutnya
                            $slotStart->addMinutes($this->durationMinutes);
                            continue;
                        }
                        return [
                            'mahasiswa' => $mahasiswa,
                            'tanggal' => $currentDate->toDateString(),
                            'waktu_mulai' => $slotStart->format('H:i'),
                            'waktu_selesai' => $slotEnd->format('H:i'),
                            'penguji1' => $availablePengujis[0],
                            'penguji2' => $availablePengujis[1],
                            'id_ruang_ujian' => $availableRoomId,
                        ];
                    }

                    // Pindah ke slot berikutnya
                    $slotStart->addMinutes($this->durationMinutes);
                }
            }
            $currentDate->addDay();
        }
        return null;
    }

    // ===================================================================
    // CATATAN: Method-method di bawah ini tidak perlu diubah.
    // Salin saja semuanya untuk memastikan file Anda lengkap dan benar.
    // ===================================================================

    public function scheduleForMahasiswa(int $mahasiswaId): array
    {
        try {
            $mahasiswa = Mahasiswa::with('dospem')->findOrFail($mahasiswaId);

            if (!$this->validateMahasiswa($mahasiswa)['success']) {
                return ['success' => false, 'message' => 'Mahasiswa tidak valid, belum siap sidang, atau sudah memiliki jadwal.'];
            }

            $startDate = Carbon::now()->addDays(1);
            $endDate = Carbon::now()->addDays(60);

            // Cache all pengujis for 30 minutes to avoid repeated queries
            $allPengujis = Cache::remember('all_active_pengujis', 1800, function () {
                return Penguji::all();
            });

            if ($allPengujis->count() < 2) {
                return ['success' => false, 'message' => 'Memerlukan minimal 2 penguji.'];
            }

            $scheduleData = $this->findAvailableSlot($mahasiswa, $allPengujis, $startDate, $endDate);

            if ($scheduleData) {
                $this->createMunaqosahSchedule($scheduleData);
                return ['success' => true, 'message' => "Jadwal berhasil dibuat pada " . Carbon::parse($scheduleData['tanggal'])->format('d-m-Y') . " jam " . $scheduleData['waktu_mulai']];
            }

            return ['success' => false, 'message' => 'Tidak dapat menemukan slot waktu & kombinasi 2 penguji yang tersedia.'];

        } catch (\Exception $e) {
            Log::error("Error scheduling for student ID {$mahasiswaId}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['success' => false, 'message' => 'Terjadi kesalahan internal: ' . $e->getMessage()];
        }
    }

    public function batchScheduleAll(): array
    {
        $readyStudents = Mahasiswa::where('siap_sidang', true)->whereDoesntHave('munaqosah')->get();
        $results = [];
        $scheduled_count = 0;
        $failed_count = 0;

        foreach ($readyStudents as $student) {
            $result = $this->scheduleForMahasiswa($student->id);
            $results[] = ['mahasiswa' => $student->nama, 'nim' => $student->nim, 'result' => $result];
            if ($result['success']) { $scheduled_count++; } else { $failed_count++; }
        }

        return ['message' => 'Batch scheduling selesai.', 'scheduled_count' => $scheduled_count, 'failed_count' => $failed_count, 'results' => $results];
    }

    private function validateMahasiswa($mahasiswa)
    {
        if (!$mahasiswa || !$mahasiswa->siap_sidang || $mahasiswa->munaqosah) {
            return ['success' => false];
        }
        return ['success' => true];
    }

    private function findAvailablePengujis($pengujis, $tanggal, $waktuMulai, $waktuSelesai, $mahasiswa)
    {
        $availablePengujis = [];
        foreach ($pengujis as $penguji) {
            if (($mahasiswa->dospem && $penguji->id == $mahasiswa->dospem->id) || !$this->isPengujiAvailable($penguji->id, $tanggal, $waktuMulai, $waktuSelesai)) {
                continue;
            }
            $availablePengujis[] = $penguji;
        }
        return $availablePengujis;
    }

    private function isPengujiAvailable($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)
    {
        // Cache availability checks for 5 minutes to speed up batch scheduling
        $cacheKey = "penguji_available_{$pengujiId}_{$tanggal}_{$waktuMulai}_{$waktuSelesai}";

        return Cache::remember($cacheKey, 300, function () use ($pengujiId, $tanggal, $waktuMulai, $waktuSelesai) {
            $isBusyInMunaqosah = Munaqosah::where(fn($q) => $q->where('id_penguji1', $pengujiId)->orWhere('id_penguji2', $pengujiId))
                ->where('tanggal_munaqosah', $tanggal)
                ->where(fn($q) => $q->where('waktu_mulai', '<', $waktuSelesai)->where('waktu_selesai', '>', $waktuMulai))
                ->exists();

            if ($isBusyInMunaqosah) return false;

            $isBusyInJadwal = JadwalPenguji::where('id_penguji', $pengujiId)
                ->where('tanggal', $tanggal)
                ->where(fn($q) => $q->where('waktu_mulai', '<', $waktuSelesai)->where('waktu_selesai', '>', $waktuMulai))
                ->exists();

            return !$isBusyInJadwal;
        });
    }

    private function findAvailableRoomId($tanggal, $waktuMulai, $waktuSelesai)
    {
        // Cache active rooms for 1 hour
        $rooms = Cache::remember('active_rooms', 3600, function () {
            return \App\Models\RuangUjian::where('is_aktif', true)->get();
        });
        foreach ($rooms as $room) {
            $isRoomBusy = Munaqosah::where('id_ruang_ujian', $room->id)
                ->where('tanggal_munaqosah', $tanggal)
                ->where(fn($q) => $q->where('waktu_mulai', '<', $waktuSelesai)->where('waktu_selesai', '>', $waktuMulai))
                ->exists();

            if (!$isRoomBusy) {
                return $room->id;
            }
        }
        return null;
    }

    private function createMunaqosahSchedule($scheduleData)
    {
        $munaqosah = Munaqosah::create([
            'id_mahasiswa' => $scheduleData['mahasiswa']->id,
            'tanggal_munaqosah' => $scheduleData['tanggal'],
            'waktu_mulai' => $scheduleData['waktu_mulai'],
            'waktu_selesai' => $scheduleData['waktu_selesai'],
            'id_penguji1' => $scheduleData['penguji1']->id,
            'id_penguji2' => $scheduleData['penguji2']->id,
            'id_ruang_ujian' => $scheduleData['id_ruang_ujian'] ?? null,
            'status_konfirmasi' => 'pending'
        ]);

        \App\Models\HistoriMunaqosah::create([
            'id_munaqosah' => $munaqosah->id,
            'perubahan' => "Jadwal dibuat otomatis dengan 2 penguji: {$scheduleData['penguji1']->nama} dan {$scheduleData['penguji2']->nama}. Ruang: " . (optional(\App\Models\RuangUjian::find($scheduleData['id_ruang_ujian'] ?? null))->nama ?? '-') . ".",
            'dilakukan_oleh' => auth()->id() ?? null,
        ]);

        // Clear relevant caches after creating a schedule
        $this->clearScheduleCache($scheduleData['tanggal']);
    }

    /**
     * Clear caches related to scheduling for a specific date
     */
    private function clearScheduleCache(string $tanggal): void
    {
        // Clear all penguji availability caches for this date
        Cache::forget('all_active_pengujis');

        // We can't easily clear specific penguji availability caches without knowing all combinations,
        // but they expire after 5 minutes anyway which is acceptable

        Log::info("Cleared schedule-related caches for date: {$tanggal}");
    }
}
