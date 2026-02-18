<?php

namespace App\Jobs;

use App\Services\AutoScheduleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoScheduleJob implements ShouldQueue
{
    use Queueable;

    protected string $mahasiswaId;

    protected string $mode;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $mahasiswaId = null, string $mode = 'single')
    {
        $this->mahasiswaId = $mahasiswaId;
        $this->mode = $mode; // 'single' or 'batch'
    }

    /**
     * Execute the job.
     */
    public function handle(AutoScheduleService $autoScheduleService): void
    {
        try {
            if ($this->mode === 'batch') {
                Log::info('Starting batch auto-schedule job');
                $result = $autoScheduleService->batchScheduleAll();

                Log::info('Batch auto-schedule completed', [
                    'scheduled_count' => $result['scheduled_count'],
                    'failed_count' => $result['failed_count'],
                ]);
            } else {
                Log::info("Starting auto-schedule job for mahasiswa ID: {$this->mahasiswaId}");
                $result = $autoScheduleService->scheduleForMahasiswa($this->mahasiswaId);

                if ($result['success']) {
                    Log::info("Auto-schedule job completed successfully for mahasiswa ID: {$this->mahasiswaId}");
                } else {
                    Log::warning("Auto-schedule job failed for mahasiswa ID: {$this->mahasiswaId}", [
                        'reason' => $result['message'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Auto-schedule job failed with exception', [
                'mahasiswa_id' => $this->mahasiswaId,
                'mode' => $this->mode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Auto-schedule job failed permanently', [
            'mahasiswa_id' => $this->mahasiswaId,
            'mode' => $this->mode,
            'error' => $exception->getMessage(),
        ]);
    }
}
