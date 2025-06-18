<?php

namespace App\Services;

use App\Models\Munaqosah;
use App\Models\Mahasiswa;
use App\Models\Penguji;
use App\Models\JadwalPenguji;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AutoScheduleService
{
    private $workingDays = [1, 2, 3, 4, 5]; // Senin-Jumat (1=Senin, 5=Jumat)
    private $workingHours = [
        'start' => '08:00',
        'end' => '16:00'
    ];
    private $sessionDuration = 2; // Durasi sidang dalam jam
    private $timeSlots = [
        '08:00-10:00',
        '10:00-12:00',
        '13:00-15:00',
        '15:00-17:00'
    ];

    /**
     * Generate jadwal munaqosah otomatis untuk mahasiswa yang siap sidang
     */
    public function generateAutoSchedule($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->addDays(7); // Mulai 1 minggu dari sekarang
        $endDate = $endDate ?: Carbon::now()->addDays(60); // Sampai 2 bulan dari sekarang
        
        // Ambil mahasiswa yang siap sidang dan belum dijadwalkan
        $mahasiswas = Mahasiswa::where('siap_sidang', true)
            ->doesntHave('munaqosah')
            ->with('dospem')
            ->get();

        if ($mahasiswas->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada mahasiswa yang siap sidang dan belum dijadwalkan.'
            ];
        }

        // Ambil semua penguji yang tersedia
        $pengujis = Penguji::all();
        
        if ($pengujis->count() < 2) {
            return [
                'success' => false,
                'message' => 'Auto-scheduling memerlukan minimal 2 penguji untuk setiap sidang. Saat ini hanya tersedia ' . $pengujis->count() . ' penguji.'
            ];
        }

        $scheduledCount = 0;
        $failedSchedules = [];

        foreach ($mahasiswas as $mahasiswa) {
            $schedule = $this->findAvailableSlot($mahasiswa, $pengujis, $startDate, $endDate);
            
            if ($schedule) {
                $this->createMunaqosahSchedule($schedule);
                $scheduledCount++;
            } else {
                $failedSchedules[] = $mahasiswa->nama . " (NIM: {$mahasiswa->nim})";
            }
        }

        return [
            'success' => true,
            'scheduled_count' => $scheduledCount,
            'failed_schedules' => $failedSchedules,
            'message' => "Berhasil membuat {$scheduledCount} jadwal munaqosah otomatis."
        ];
    }

    /**
     * Cari slot waktu yang tersedia untuk mahasiswa tertentu
     */
    private function findAvailableSlot($mahasiswa, $pengujis, $startDate, $endDate)
    {
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Skip hari libur (weekend)
            if (!in_array($currentDate->dayOfWeek, $this->workingDays)) {
                $currentDate->addDay();
                continue;
            }

            // Coba setiap slot waktu di hari ini
            foreach ($this->timeSlots as $timeSlot) {
                [$waktuMulai, $waktuSelesai] = explode('-', $timeSlot);
                
                // Cari kombinasi penguji yang tersedia
                $availablePengujis = $this->findAvailablePengujis(
                    $pengujis, 
                    $currentDate->toDateString(), 
                    $waktuMulai, 
                    $waktuSelesai,
                    $mahasiswa
                );

                if (count($availablePengujis) >= 2) { // Wajib 2 penguji untuk auto-scheduling
                    return [
                        'mahasiswa' => $mahasiswa,
                        'tanggal' => $currentDate->toDateString(),
                        'waktu_mulai' => $waktuMulai,
                        'waktu_selesai' => $waktuSelesai,
                        'penguji1' => $availablePengujis[0],
                        'penguji2' => $availablePengujis[1] // Selalu ada penguji ke-2
                    ];
                }
            }
            
            $currentDate->addDay();
        }

        return null;
    }

    /**
     * Cari penguji yang tersedia pada waktu tertentu
     */
    private function findAvailablePengujis($pengujis, $tanggal, $waktuMulai, $waktuSelesai, $mahasiswa)
    {
        $availablePengujis = [];
        
        foreach ($pengujis as $penguji) {
            // Skip jika penguji adalah dospem mahasiswa (untuk menghindari konflik kepentingan)
            // Cek berdasarkan ID dan juga nama jika ID dospem tidak tersedia
            if ($mahasiswa->id_dospem && $penguji->id == $mahasiswa->id_dospem) {
                continue;
            }
            
            // Jika mahasiswa memiliki dospem, pastikan penguji bukan dospem tersebut
            if ($mahasiswa->dospem && ($penguji->nama == $mahasiswa->dospem->nama)) {
                continue;
            }

            if (!$this->isPengujiConflicted($penguji->id, $tanggal, $waktuMulai, $waktuSelesai)) {
                $availablePengujis[] = $penguji;
                
                // Batasi maksimal 2 penguji
                if (count($availablePengujis) >= 2) {
                    break;
                }
            }
        }

        return $availablePengujis;
    }

    /**
     * Cek apakah penguji bentrok pada waktu tertentu
     */
    private function isPengujiConflicted($pengujiId, $tanggal, $waktuMulai, $waktuSelesai)
    {
        // Cek bentrok dengan jadwal munaqosah lain
        $munaqosahConflict = Munaqosah::where('tanggal_munaqosah', $tanggal)
            ->where(function ($query) use ($pengujiId) {
                $query->where('id_penguji1', $pengujiId)
                      ->orWhere('id_penguji2', $pengujiId);
            })
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                    // Overlap: jadwal baru mulai sebelum jadwal lama selesai DAN jadwal baru selesai setelah jadwal lama mulai
                    $q->where('waktu_mulai', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>', $waktuMulai);
                });
            })
            ->exists();

        if ($munaqosahConflict) {
            return true;
        }

        // Cek bentrok dengan jadwal penguji lain (non-munaqosah)
        $jadwalPengujiConflict = JadwalPenguji::where('id_penguji', $pengujiId)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>', $waktuMulai);
                });
            })
            ->exists();

        return $jadwalPengujiConflict;
    }

    /**
     * Buat jadwal munaqosah baru dengan logging histori
     */
    private function createMunaqosahSchedule($scheduleData)
    {
        // Validasi bahwa kedua penguji tersedia
        if (!isset($scheduleData['penguji2']) || !$scheduleData['penguji2']) {
            throw new \Exception('Auto-scheduling memerlukan 2 penguji. Penguji ke-2 tidak tersedia.');
        }

        $munaqosah = Munaqosah::create([
            'id_mahasiswa' => $scheduleData['mahasiswa']->id,
            'tanggal_munaqosah' => $scheduleData['tanggal'],
            'waktu_mulai' => $scheduleData['waktu_mulai'],
            'waktu_selesai' => $scheduleData['waktu_selesai'],
            'id_penguji1' => $scheduleData['penguji1']->id,
            'id_penguji2' => $scheduleData['penguji2']->id, // Selalu wajib ada
            'status_konfirmasi' => 'pending'
        ]);

        // Catat histori bahwa jadwal dibuat secara otomatis dengan 2 penguji
        \App\Models\HistoriMunaqosah::create([
            'id_munaqosah' => $munaqosah->id,
            'perubahan' => "Jadwal munaqosah dibuat secara otomatis dengan 2 penguji: {$scheduleData['penguji1']->nama} dan {$scheduleData['penguji2']->nama}.",
            'dilakukan_oleh' => auth()->id() ?? 1, // Default ke user ID 1 jika tidak ada user yang login
        ]);

        return $munaqosah;
    }

    /**
     * Generate jadwal untuk mahasiswa tertentu
     */
    public function generateForStudent($mahasiswaId, $startDate = null, $endDate = null)
    {
        $mahasiswa = Mahasiswa::with('dospem')->find($mahasiswaId);
        
        if (!$mahasiswa || !$mahasiswa->siap_sidang || $mahasiswa->munaqosah) {
            return [
                'success' => false,
                'message' => 'Mahasiswa tidak valid, belum siap sidang, atau sudah memiliki jadwal munaqosah.'
            ];
        }

        $startDate = $startDate ?: Carbon::now()->addDays(7);
        $endDate = $endDate ?: Carbon::now()->addDays(60);
        $pengujis = Penguji::all();

        // Validasi minimal 2 penguji untuk auto-scheduling
        if ($pengujis->count() < 2) {
            return [
                'success' => false,
                'message' => 'Auto-scheduling memerlukan minimal 2 penguji. Saat ini hanya tersedia ' . $pengujis->count() . ' penguji.'
            ];
        }

        $schedule = $this->findAvailableSlot($mahasiswa, $pengujis, $startDate, $endDate);
        
        if ($schedule) {
            $munaqosah = $this->createMunaqosahSchedule($schedule);
            return [
                'success' => true,
                'munaqosah' => $munaqosah,
                'message' => "Jadwal munaqosah berhasil dibuat secara otomatis dengan 2 penguji: {$schedule['penguji1']->nama} dan {$schedule['penguji2']->nama}."
            ];
        }

        return [
            'success' => false,
            'message' => 'Tidak dapat menemukan slot waktu dengan 2 penguji yang tersedia untuk mahasiswa ini.'
        ];
    }

    /**
     * Validasi apakah mahasiswa bisa dijadwalkan
     */
    private function validateMahasiswa($mahasiswa)
    {
        if (!$mahasiswa->siap_sidang) {
            return false;
        }

        if ($mahasiswa->munaqosah) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan statistik scheduling
     */
    public function getSchedulingStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->addDays(7);
        $endDate = $endDate ?: Carbon::now()->addDays(60);

        $mahasiswasReady = Mahasiswa::where('siap_sidang', true)
            ->doesntHave('munaqosah')
            ->count();

        $totalPengujis = Penguji::count();
        
        $totalSlots = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $this->workingDays)) {
                $totalSlots += count($this->timeSlots);
            }
            $currentDate->addDay();
        }

        return [
            'mahasiswas_ready' => $mahasiswasReady,
            'total_pengujis' => $totalPengujis,
            'total_slots_available' => $totalSlots,
            'estimated_capacity' => min($totalSlots, $mahasiswasReady)
        ];
    }
}
