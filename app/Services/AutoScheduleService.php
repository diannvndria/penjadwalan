<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use App\Models\Penguji;
use App\Models\RuangUjian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoScheduleService
{
    private array $workingDays;

    private array $workingHours;

    private int $durationMinutes; // Akan menyimpan durasi dalam menit

    /**
     * In-memory workload tracker for load balancing during batch operations
     * This gets updated after each successful schedule to ensure even distribution
     */
    private ?array $workloadTracker = null;

    /**
     * In-memory tracker for room bookings during batch operations
     * Format: ['date|start|end' => [room_id1, room_id2, ...]]
     */
    private array $roomBookings = [];

    /**
     * In-memory tracker for examiner bookings during batch operations
     * Format: ['date|start|end' => [penguji_id1, penguji_id2, ...]]
     */
    private array $examinerBookings = [];

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
     */
    public function setDuration(int $minutes): void
    {
        $this->durationMinutes = $minutes;
    }

    /**
     * Mengatur jam kerja untuk instance service saat ini.
     */
    public function setWorkingHours(string $start, string $end): void
    {
        $this->workingHours['start'] = $start;
        $this->workingHours['end'] = $end;
    }

    /**
     * Mengatur jangkauan pencarian jadwal.
     * Method ini ditambahkan kembali untuk mencegah error dari controller.
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

                $slotStart = Carbon::parse($currentDate->toDateString().' '.$this->workingHours['start']);
                $dayEnd = Carbon::parse($currentDate->toDateString().' '.$this->workingHours['end']);

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

                        if (! $availableRoomId) {
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

            if (! $this->validateMahasiswa($mahasiswa)['success']) {
                return ['success' => false, 'message' => 'Mahasiswa tidak valid, belum siap sidang, atau sudah memiliki jadwal.'];
            }

            $startDate = Carbon::now()->addDays(1);
            $endDate = Carbon::now()->addDays(60);
            $allPengujis = Penguji::all();

            if ($allPengujis->count() < 2) {
                return ['success' => false, 'message' => 'Memerlukan minimal 2 penguji.'];
            }

            $scheduleData = $this->findAvailableSlot($mahasiswa, $allPengujis, $startDate, $endDate);

            if ($scheduleData) {
                $this->createMunaqosahSchedule($scheduleData);

                return ['success' => true, 'message' => 'Jadwal berhasil dibuat pada '.Carbon::parse($scheduleData['tanggal'])->format('d-m-Y').' jam '.$scheduleData['waktu_mulai']];
            }

            return ['success' => false, 'message' => 'Tidak dapat menemukan slot waktu & kombinasi 2 penguji yang tersedia.'];

        } catch (\Exception $e) {
            Log::error("Error scheduling for student ID {$mahasiswaId}: ".$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return ['success' => false, 'message' => 'Terjadi kesalahan internal: '.$e->getMessage()];
        }
    }

    private const CHUNK_SIZE = 5; // Process 5 records at a time

    public function batchScheduleAll(): array
    {
        try {
            // Increase time limit for this operation
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');

            // Reset all in-memory trackers at the start of batch operation
            // This ensures fresh state for room bookings, examiner bookings, and workload
            $this->resetAllTrackers();

            $results = [];
            $scheduledCount = 0;
            $failedCount = 0;

            // OPTIMIZATION: Eager load dospem and munaqosah to prevent N+1 queries
            // Sort by SCHEDULE PRIORITY (prioritas_jadwal) ONLY for scheduling order
            // Room priority (is_prioritas) does NOT affect scheduling order - only room allocation
            $mahasiswas = Mahasiswa::where('siap_sidang', true)
                ->whereDoesntHave('munaqosah')
                ->with(['dospem', 'munaqosah'])
                ->orderByRaw('CASE WHEN prioritas_jadwal = true THEN 0 ELSE 1 END')
                ->orderBy('angkatan', 'asc') // Older students (lower angkatan) get priority as tiebreaker
                ->get();

            // OPTIMIZATION: Cache pengujis list for the entire batch operation
            $allPengujis = Cache::remember('batch_schedule_pengujis', 60, function () {
                return Penguji::all();
            });

            // Process in chunks but handle transactions per student
            foreach ($mahasiswas->chunk(self::CHUNK_SIZE) as $chunk) {
                foreach ($chunk as $mahasiswa) {
                    try {
                        // Use DB::transaction for atomic operation per student
                        // This handles beginTransaction/commit/rollBack automatically
                        $result = DB::transaction(function () use ($mahasiswa, $allPengujis) {
                            return $this->scheduleForMahasiswaOptimized($mahasiswa, $allPengujis);
                        });

                        if ($result['success']) {
                            $scheduledCount++;
                        } else {
                            $failedCount++;
                        }

                        $results[] = [
                            'mahasiswa' => $mahasiswa->nama,
                            'nim' => $mahasiswa->nim,
                            'result' => $result,
                        ];

                    } catch (\Exception $e) {
                        // Log the error for this specific student
                        Log::error("Error scheduling for student ID {$mahasiswa->id}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        $failedCount++;
                        $results[] = [
                            'mahasiswa' => $mahasiswa->nama,
                            'nim' => $mahasiswa->nim,
                            'result' => [
                                'success' => false,
                                'message' => 'Error: '.$e->getMessage(),
                            ],
                        ];
                    }
                }

                // Add a small delay between chunks to be nice to the server
                if (count($mahasiswas) > self::CHUNK_SIZE) {
                    usleep(50000); // 50ms delay (reduced from 200ms)
                }
            }

            // Clear cache after batch operation
            Cache::forget('batch_schedule_pengujis');

            return [
                'success' => true,
                'message' => "Penjadwalan batch selesai: $scheduledCount berhasil, $failedCount gagal",
                'scheduled_count' => $scheduledCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ];

        } catch (\Exception $e) {
            Log::error('Error in batch scheduling', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan dalam batch scheduling: '.$e->getMessage(),
                'scheduled_count' => $scheduledCount ?? 0,
                'failed_count' => ($mahasiswas->count() ?? 0) - ($scheduledCount ?? 0),
                'results' => $results ?? [],
            ];
        }
    }

    /**
     * Optimized version of scheduleForMahasiswa that accepts pre-loaded pengujis
     */
    private function scheduleForMahasiswaOptimized($mahasiswa, $allPengujis): array
    {
        try {
            if (! $this->validateMahasiswa($mahasiswa)['success']) {
                return ['success' => false, 'message' => 'Mahasiswa tidak valid, belum siap sidang, atau sudah memiliki jadwal.'];
            }

            if ($allPengujis->count() < 2) {
                return ['success' => false, 'message' => 'Memerlukan minimal 2 penguji.'];
            }

            $startDate = Carbon::now()->addDays(1);
            $endDate = Carbon::now()->addDays(60);

            $scheduleData = $this->findAvailableSlot($mahasiswa, $allPengujis, $startDate, $endDate);

            if ($scheduleData) {
                $this->createMunaqosahSchedule($scheduleData);

                return ['success' => true, 'message' => 'Jadwal berhasil dibuat pada '.Carbon::parse($scheduleData['tanggal'])->format('d-m-Y').' jam '.$scheduleData['waktu_mulai']];
            }

            return ['success' => false, 'message' => 'Tidak dapat menemukan slot waktu & kombinasi 2 penguji yang tersedia.'];

        } catch (\Exception $e) {
            Log::error("Error scheduling for student ID {$mahasiswa->id}: ".$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return ['success' => false, 'message' => 'Terjadi kesalahan internal: '.$e->getMessage()];
        }
    }

    private function validateMahasiswa($mahasiswa)
    {
        if (! $mahasiswa || ! $mahasiswa->siap_sidang || $mahasiswa->munaqosah) {
            return ['success' => false];
        }

        return ['success' => true];
    }

    /**
     * Get IDs of examiners who are busy at the given time slot
     * OPTIMIZATION: Single query instead of N+1
     */
    private function getBusyPengujiIds($tanggal, $waktuMulai, $waktuSelesai)
    {
        // Query 1: Penguji 1 from munaqosah
        $q1 = DB::table('munaqosah')
            ->select('id_penguji1 as id')
            ->where('tanggal_munaqosah', $tanggal)
            ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                $q->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        // Query 2: Penguji 2 from munaqosah
        $q2 = DB::table('munaqosah')
            ->select('id_penguji2 as id')
            ->where('tanggal_munaqosah', $tanggal)
            ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                $q->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        // Query 3: Jadwal Penguji
        $q3 = DB::table('jadwal_penguji')
            ->select('id_penguji as id')
            ->where('tanggal', $tanggal)
            ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                $q->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        return $q1->union($q2)->union($q3)->pluck('id')->toArray();
    }

    private function findAvailablePengujis($pengujis, $tanggal, $waktuMulai, $waktuSelesai, $mahasiswa)
    {
        // OPTIMIZATION: Get all busy IDs in one go to avoid N+1 queries
        $busyIds = $this->getBusyPengujiIds($tanggal, $waktuMulai, $waktuSelesai);

        // Also get examiners booked in-memory during this batch
        $slotKey = "{$tanggal}|{$waktuMulai}|{$waktuSelesai}";
        $inMemoryBookedExaminers = $this->examinerBookings[$slotKey] ?? [];
        $busyIds = array_unique(array_merge($busyIds, $inMemoryBookedExaminers));

        // Get current workload for each penguji (for load balancing)
        $workloadCounts = $this->getPengujiWorkloadCounts();

        $availablePengujis = [];
        $dospemId = $mahasiswa->dospem ? $mahasiswa->dospem->id : null;

        foreach ($pengujis as $penguji) {
            // Dosen pembimbing tidak boleh jadi penguji
            if ($dospemId && $penguji->id == $dospemId) {
                continue;
            }

            // Cek apakah penguji sibuk (using in-memory check instead of DB query)
            if (in_array($penguji->id, $busyIds)) {
                continue;
            }

            // Attach workload count for sorting
            $penguji->current_workload = $workloadCounts[$penguji->id] ?? 0;
            $availablePengujis[] = $penguji;
        }

        // LOAD BALANCING: Sort by current workload (least busy first)
        usort($availablePengujis, function ($a, $b) {
            return $a->current_workload <=> $b->current_workload;
        });

        return $availablePengujis;
    }

    /**
     * Get workload counts for all penguji (for load balancing)
     * Returns array with penguji_id => total_assignments
     * Uses instance-based tracking that persists during batch operations
     */
    private function getPengujiWorkloadCounts(): array
    {
        // Initialize workload tracker from database if not yet loaded
        if ($this->workloadTracker === null) {
            $result = DB::select("
                SELECT p.id,
                       COALESCE((SELECT COUNT(*) FROM munaqosah WHERE id_penguji1 = p.id), 0) +
                       COALESCE((SELECT COUNT(*) FROM munaqosah WHERE id_penguji2 = p.id), 0) as total
                FROM penguji p
            ");

            $this->workloadTracker = [];
            foreach ($result as $row) {
                $this->workloadTracker[$row->id] = (int) $row->total;
            }
        }

        return $this->workloadTracker;
    }

    /**
     * Update workload tracker after a schedule is created
     * This ensures even distribution during batch operations
     */
    private function incrementPengujiWorkload(int $penguji1Id, int $penguji2Id): void
    {
        if ($this->workloadTracker !== null) {
            $this->workloadTracker[$penguji1Id] = ($this->workloadTracker[$penguji1Id] ?? 0) + 1;
            $this->workloadTracker[$penguji2Id] = ($this->workloadTracker[$penguji2Id] ?? 0) + 1;
        }
    }

    /**
     * Track a room booking in-memory for the current batch
     */
    private function trackRoomBooking(string $tanggal, string $waktuMulai, string $waktuSelesai, int $roomId): void
    {
        $slotKey = "{$tanggal}|{$waktuMulai}|{$waktuSelesai}";
        if (!isset($this->roomBookings[$slotKey])) {
            $this->roomBookings[$slotKey] = [];
        }
        $this->roomBookings[$slotKey][] = $roomId;
    }

    /**
     * Track examiner bookings in-memory for the current batch
     */
    private function trackExaminerBooking(string $tanggal, string $waktuMulai, string $waktuSelesai, int $penguji1Id, int $penguji2Id): void
    {
        $slotKey = "{$tanggal}|{$waktuMulai}|{$waktuSelesai}";
        if (!isset($this->examinerBookings[$slotKey])) {
            $this->examinerBookings[$slotKey] = [];
        }
        $this->examinerBookings[$slotKey][] = $penguji1Id;
        $this->examinerBookings[$slotKey][] = $penguji2Id;
    }

    /**
     * Reset all in-memory trackers (call before starting a new batch)
     */
    public function resetAllTrackers(): void
    {
        $this->workloadTracker = null;
        $this->roomBookings = [];
        $this->examinerBookings = [];
    }

    /**
     * Reset workload tracker (call this to force fresh data from database)
     */
    public function resetWorkloadTracker(): void
    {
        $this->workloadTracker = null;
    }

    private function isPengujiAvailable($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)
    {
        // OPTIMIZATION: Combine both queries into a single optimized query
        // Check if penguji is busy in either munaqosah OR jadwal_penguji tables
        // IMPORTANT: Must select same columns in both UNION queries
        $isBusy = DB::table('munaqosah')
            ->select(DB::raw('1'))
            ->where(function ($q) use ($pengujiId) {
                $q->where('id_penguji1', $pengujiId)
                    ->orWhere('id_penguji2', $pengujiId);
            })
            ->where('tanggal_munaqosah', $tanggal)
            ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                $q->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            })
            ->union(
                DB::table('jadwal_penguji')
                    ->select(DB::raw('1'))
                    ->where('id_penguji', $pengujiId)
                    ->where('tanggal', $tanggal)
                    ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                        $q->where('waktu_mulai', '<', $waktuSelesai)
                            ->where('waktu_selesai', '>', $waktuMulai);
                    })
            )
            ->exists();

        return ! $isBusy;
    }

    /**
     * Cek apakah mahasiswa atau penguji berstatus prioritas RUANG
     * This checks is_prioritas flag ONLY (for room allocation)
     * prioritas_jadwal is NOT considered here - it only affects scheduling order
     */
    private function checkPriorityStatus(Mahasiswa $mahasiswa, $pengujis): bool
    {
        // Mahasiswa prioritas ruang (is_prioritas flag only)
        if ($mahasiswa->is_prioritas) {
            return true;
        }

        // Salah satu penguji prioritas (for accessibility needs)
        foreach ($pengujis as $penguji) {
            if ($penguji->isPrioritas()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cari ruang yang tersedia dengan prioritas lantai
     * OPTIMIZED: Uses single query with subquery instead of looping
     * Also checks in-memory bookings for batch operations
     */
    private function findAvailableRoomId($tanggal, $waktuMulai, $waktuSelesai, bool $needsPriorityRoom = false)
    {
        // Get rooms already booked in-memory during this batch
        $slotKey = "{$tanggal}|{$waktuMulai}|{$waktuSelesai}";
        $inMemoryBookedRooms = $this->roomBookings[$slotKey] ?? [];

        // OPTIMIZATION: Use a single query with NOT EXISTS subquery
        $query = RuangUjian::where('is_aktif', true)
            ->whereNotExists(function ($query) use ($tanggal, $waktuMulai, $waktuSelesai) {
                $query->select(DB::raw(1))
                    ->from('munaqosah')
                    ->whereColumn('munaqosah.id_ruang_ujian', 'ruang_ujian.id')
                    ->where('munaqosah.tanggal_munaqosah', $tanggal)
                    ->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                        $q->where('munaqosah.waktu_mulai', '<', $waktuSelesai)
                            ->where('munaqosah.waktu_selesai', '>', $waktuMulai);
                    });
            });

        // Exclude rooms already booked in-memory during this batch
        if (!empty($inMemoryBookedRooms)) {
            $query->whereNotIn('id', $inMemoryBookedRooms);
        }

        // Apply priority-based ordering
        if ($needsPriorityRoom) {
            // Prioritize rooms with is_prioritas = true, then floor 1, then other floors
            $query->orderByRaw('CASE WHEN is_prioritas THEN 0 WHEN lantai = 1 THEN 1 ELSE lantai + 10 END');
        } else {
            // For non-priority, just order by floor
            $query->orderBy('lantai');
        }

        $room = $query->first();

        if ($room && $needsPriorityRoom && ($room->is_prioritas || $room->lantai == 1)) {
            Log::info("Priority room allocated: {$room->nama} (Lantai {$room->lantai}, Prioritas: ".($room->is_prioritas ? 'Ya' : 'Tidak').')');
        }

        return $room?->id;
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
            'status_konfirmasi' => 'pending',
        ]);

        // OPTIMIZATION: Only fetch room data if we need it for the history record
        $ruangNama = '-';
        $ruangLantai = '';
        if ($scheduleData['id_ruang_ujian']) {
            $ruang = RuangUjian::select('nama', 'lantai')->find($scheduleData['id_ruang_ujian']);
            if ($ruang) {
                $ruangNama = $ruang->nama;
                $ruangLantai = " (Lantai {$ruang->lantai})";
            }
        }

        $priorityNote = isset($scheduleData['is_priority_allocation']) && $scheduleData['is_priority_allocation']
            ? ' [PRIORITAS]'
            : '';

        \App\Models\HistoriMunaqosah::create([
            'id_munaqosah' => $munaqosah->id,
            'perubahan' => "Jadwal dibuat otomatis dengan 2 penguji: {$scheduleData['penguji1']->nama} dan {$scheduleData['penguji2']->nama}. Ruang: {$ruangNama}{$ruangLantai}{$priorityNote}.",
            'dilakukan_oleh' => auth()->id() ?? null,
        ]);

        // Update in-memory workload tracker for load balancing during batch operations
        $this->incrementPengujiWorkload($scheduleData['penguji1']->id, $scheduleData['penguji2']->id);

        // Track room and examiner bookings for this time slot to prevent double-booking in batch
        if ($scheduleData['id_ruang_ujian']) {
            $this->trackRoomBooking(
                $scheduleData['tanggal'],
                $scheduleData['waktu_mulai'],
                $scheduleData['waktu_selesai'],
                $scheduleData['id_ruang_ujian']
            );
        }

        $this->trackExaminerBooking(
            $scheduleData['tanggal'],
            $scheduleData['waktu_mulai'],
            $scheduleData['waktu_selesai'],
            $scheduleData['penguji1']->id,
            $scheduleData['penguji2']->id
        );
    }
}
