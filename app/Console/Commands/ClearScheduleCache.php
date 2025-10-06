<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearScheduleCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:clear-cache
                            {--all : Clear all schedule-related caches}
                            {--pengujis : Clear pengujis cache only}
                            {--rooms : Clear rooms cache only}
                            {--dosens : Clear dosens cache only}
                            {--angkatans : Clear angkatans cache only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear auto-schedule caches (pengujis, rooms, dosens, angkatans, availability checks)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all') || (!$this->option('pengujis') && !$this->option('rooms') && !$this->option('dosens') && !$this->option('angkatans'))) {
            // Clear all schedule caches
            Cache::forget('all_active_pengujis');
            Cache::forget('active_rooms');
            Cache::forget('all_dosens');
            Cache::forget('all_dosens_ordered');
            Cache::forget('available_angkatans');

            $this->info('✅ All schedule caches cleared successfully!');
            $this->info('   - Pengujis cache: cleared');
            $this->info('   - Rooms cache: cleared');
            $this->info('   - Dosens cache: cleared');
            $this->info('   - Angkatans cache: cleared');
            $this->info('   - Penguji availability caches will expire in 5 minutes');

            return Command::SUCCESS;
        }

        if ($this->option('pengujis')) {
            Cache::forget('all_active_pengujis');
            $this->info('✅ Pengujis cache cleared successfully!');
        }

        if ($this->option('rooms')) {
            Cache::forget('active_rooms');
            $this->info('✅ Rooms cache cleared successfully!');
        }

        if ($this->option('dosens')) {
            Cache::forget('all_dosens');
            Cache::forget('all_dosens_ordered');
            $this->info('✅ Dosens cache cleared successfully!');
        }

        if ($this->option('angkatans')) {
            Cache::forget('available_angkatans');
            $this->info('✅ Angkatans cache cleared successfully!');
        }

        return Command::SUCCESS;
    }
}
