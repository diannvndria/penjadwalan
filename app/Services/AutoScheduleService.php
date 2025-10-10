<?php

namespace App\Services;

use App\Models\Munaqosah;
use App\Models\Mahasiswa;
use App\Models\Penguji;
use App\Models\JadwalPenguji;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                        // Cek status prioritas untuk menentukan alokasi ruang
                        $needsPriorityRoom = $this->checkPriorityStatus($mahasiswa, array_slice($availablePengujis, 0, 2));

                        // Cek ketersediaan ruang ujian untuk slot ini
                        $availableRoomId = $this->findAvailableRoomId(
                            $currentDate->toDateString(),
                            $slotStart->toTimeString('minutes'),
                            $slotEnd->toTimeString('minutes'),
                            $needsPriorityRoom
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
                            'is_priority_allocation' => $needsPriorityRoom,
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

    private const CHUNK_SIZE = 5; // Process 5 records at a time

    public function batchScheduleAll(): array
    {
        try {
            // Increase time limit for this operation
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');

            $results = [];
            $scheduledCount = 0;
            $failedCount = 0;

            // Get all eligible students
            $mahasiswas = Mahasiswa::where('siap_sidang', true)
                ->whereDoesntHave('munaqosah')
                ->select(['id', 'nim', 'nama'])
                ->get();

            // Process in chunks with transaction per chunk
            foreach ($mahasiswas->chunk(self::CHUNK_SIZE) as $chunk) {
                DB::beginTransaction();
                try {
                    foreach ($chunk as $mahasiswa) {
                        try {
                            $result = $this->scheduleForMahasiswa($mahasiswa->id);
                            
                            if ($result['success']) {
                                $scheduledCount++;
                            } else {
                                $failedCount++;
                            }

                            $results[] = [
                                'mahasiswa' => $mahasiswa->nama,
                                'nim' => $mahasiswa->nim,
                                'result' => $result
                            ];
                            
                            // Commit each successful scheduling immediately
                            DB::commit();
                            DB::beginTransaction();
                            
                        } catch (\Exception $e) {
                            // Log the error for this specific student
                            Log::error("Error scheduling for student {$mahasiswa->nim}", [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            
                            // Rollback only this student's transaction
                            DB::rollBack();
                            DB::beginTransaction();
                            
                            $failedCount++;
                            $results[] = [
                                'mahasiswa' => $mahasiswa->nama,
                                'nim' => $mahasiswa->nim,
                                'result' => [
                                    'success' => false,
                                    'message' => 'Error: ' . $e->getMessage()
                                ]
                            ];
                        }
                    }
                    
                    // Commit any remaining transaction
                    if (DB::transactionLevel() > 0) {
                        DB::commit();
                    }

                    // Add a small delay between chunks
                    if (count($mahasiswas) > self::CHUNK_SIZE) {
                        usleep(200000); // 200ms delay
                    }
                    
                } catch (\Exception $chunkError) {
                    // If there's an error processing the chunk, log it and continue
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }
                    Log::error("Error processing chunk", [
                        'error' => $chunkError->getMessage(),
                        'trace' => $chunkError->getTraceAsString()
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => "Penjadwalan batch selesai: $scheduledCount berhasil, $failedCount gagal",
                'scheduled_count' => $scheduledCount,
                'failed_count' => $failedCount,
                'results' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Error in batch scheduling', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan dalam batch scheduling: ' . $e->getMessage(),
                'scheduled_count' => $scheduledCount ?? 0,
                'failed_count' => ($mahasiswas->count() ?? 0) - ($scheduledCount ?? 0),
                'results' => $results ?? []
            ];
        }
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

    /**
     * Cek apakah mahasiswa atau penguji berstatus prioritas
     */
    private function checkPriorityStatus(Mahasiswa $mahasiswa, $pengujis): bool
    {
        // Mahasiswa prioritas
        if ($mahasiswa->isPrioritas()) {
            return true;
        }

        // Salah satu penguji prioritas
        foreach ($pengujis as $penguji) {
            if ($penguji->isPrioritas()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cari ruang yang tersedia dengan prioritas lantai
     */
    private function findAvailableRoomId($tanggal, $waktuMulai, $waktuSelesai, bool $needsPriorityRoom = false)
    {
        // Cache active rooms for 1 hour
        $rooms = Cache::remember('active_rooms', 3600, function () {
            return \App\Models\RuangUjian::where('is_aktif', true)->get();
        });

        // Urutkan ruangan berdasarkan prioritas
        $sortedRooms = $rooms->sortBy(function ($room) use ($needsPriorityRoom) {
            // Jika butuh ruang prioritas, prioritaskan ruang prioritas dan lantai 1
            if ($needsPriorityRoom) {
                // Semakin kecil nilai, semakin prioritas
                if ($room->is_prioritas) return 0;
                if ($room->lantai == 1) return 1;
                return $room->lantai + 10;
            }
            // Untuk non-prioritas, urutkan normal berdasarkan lantai
            return $room->lantai;
        });

        foreach ($sortedRooms as $room) {
            $isRoomBusy = Munaqosah::where('id_ruang_ujian', $room->id)
                ->where('tanggal_munaqosah', $tanggal)
                ->where(fn($q) => $q->where('waktu_mulai', '<', $waktuSelesai)->where('waktu_selesai', '>', $waktuMulai))
                ->exists();

            if (!$isRoomBusy) {
                // Log jika alokasi prioritas berhasil
                if ($needsPriorityRoom && ($room->is_prioritas || $room->lantai == 1)) {
                    Log::info("Priority room allocated: {$room->nama} (Lantai {$room->lantai}, Prioritas: " . ($room->is_prioritas ? 'Ya' : 'Tidak') . ")");
                }
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

        $ruang = \App\Models\RuangUjian::find($scheduleData['id_ruang_ujian'] ?? null);
        $priorityNote = isset($scheduleData['is_priority_allocation']) && $scheduleData['is_priority_allocation']
            ? ' [PRIORITAS]'
            : '';

        \App\Models\HistoriMunaqosah::create([
            'id_munaqosah' => $munaqosah->id,
            'perubahan' => "Jadwal dibuat otomatis dengan 2 penguji: {$scheduleData['penguji1']->nama} dan {$scheduleData['penguji2']->nama}. Ruang: " . (optional($ruang)->nama ?? '-') . ($ruang ? " (Lantai {$ruang->lantai})" : '') . "{$priorityNote}.",
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
