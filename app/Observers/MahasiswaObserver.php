<?php

namespace App\Observers;

use App\Models\Mahasiswa;
use App\Services\AutoScheduleService;
use Illuminate\Support\Facades\Log;

class MahasiswaObserver
{
    protected AutoScheduleService $autoScheduleService;

    public function __construct(AutoScheduleService $autoScheduleService)
    {
        $this->autoScheduleService = $autoScheduleService;
    }

    /**
     * Handle the Mahasiswa "updated" event.
     */
    public function updated(Mahasiswa $mahasiswa): void
    {
        // Cek apakah field siap_sidang berubah menjadi true
        if ($mahasiswa->isDirty('siap_sidang') && $mahasiswa->siap_sidang === true) {

            // Pastikan mahasiswa belum memiliki jadwal munaqosah
            if (! $mahasiswa->munaqosah) {

                Log::info("Mahasiswa {$mahasiswa->nama} ditandai siap sidang, memulai auto-schedule", [
                    'mahasiswa_id' => $mahasiswa->id,
                    'nim' => $mahasiswa->nim,
                ]);

                // Trigger auto-schedule secara asinkron jika menggunakan queue
                // Atau langsung eksekusi jika tidak menggunakan queue
                $this->triggerAutoSchedule($mahasiswa);
            }
        }
    }

    /**
     * Trigger auto-schedule untuk mahasiswa
     */
    protected function triggerAutoSchedule(Mahasiswa $mahasiswa): void
    {
        try {
            $result = $this->autoScheduleService->scheduleForMahasiswa($mahasiswa->id);

            if ($result['success']) {
                Log::info("Auto-schedule berhasil untuk mahasiswa {$mahasiswa->nama}", [
                    'mahasiswa_id' => $mahasiswa->id,
                    'munaqosah_data' => $result['data'],
                ]);
            } else {
                Log::warning("Auto-schedule gagal untuk mahasiswa {$mahasiswa->nama}", [
                    'mahasiswa_id' => $mahasiswa->id,
                    'reason' => $result['message'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error dalam auto-schedule untuk mahasiswa {$mahasiswa->nama}", [
                'mahasiswa_id' => $mahasiswa->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
