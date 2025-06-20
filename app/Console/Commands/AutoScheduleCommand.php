<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoScheduleService;
use App\Models\Mahasiswa;

class AutoScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-schedule:run 
                            {--student= : ID mahasiswa untuk dijadwalkan}
                            {--batch : Jadwalkan semua mahasiswa yang siap sidang}
                            {--simulate : Simulasi tanpa menyimpan ke database}
                            {--detail : Tampilkan output detail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan auto-schedule untuk penjadwalan sidang skripsi';

    protected \App\Services\AutoScheduleService $autoScheduleService;

    public function __construct(\App\Services\AutoScheduleService $autoScheduleService)
    {
        parent::__construct();
        $this->autoScheduleService = $autoScheduleService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai Auto-Schedule untuk Penjadwalan Sidang Skripsi');
        $this->info('================================================');

        // Jika ada option student, jadwalkan satu mahasiswa
        if ($this->option('student')) {
            $this->scheduleOneStudent();
            return;
        }

        // Jika ada option batch, jadwalkan semua mahasiswa
        if ($this->option('batch')) {
            $this->batchScheduleAll();
            return;
        }

        // Default: tampilkan bantuan
        $this->showHelp();
    }

    /**
     * Jadwalkan satu mahasiswa
     */
    protected function scheduleOneStudent(): void
    {
        $studentId = $this->option('student');
        
        if (!is_numeric($studentId)) {
            $this->error('❌ ID mahasiswa harus berupa angka');
            return;
        }

        $mahasiswa = Mahasiswa::find($studentId);
        if (!$mahasiswa) {
            $this->error("❌ Mahasiswa dengan ID {$studentId} tidak ditemukan");
            return;
        }

        $this->info("📋 Memproses mahasiswa: {$mahasiswa->nama} (NIM: {$mahasiswa->nim})");

        if (!$mahasiswa->siap_sidang) {
            $this->warn('⚠️  Mahasiswa belum ditandai siap sidang');
            return;
        }

        if ($mahasiswa->munaqosah) {
            $this->warn('⚠️  Mahasiswa sudah memiliki jadwal munaqosah');
            return;
        }

        if ($this->option('simulate')) {
            $this->simulateSchedule($mahasiswa);
        } else {
            $this->executeSchedule((int)$studentId);
        }
    }

    /**
     * Jadwalkan semua mahasiswa yang siap sidang
     */
    protected function batchScheduleAll(): void
    {
        $this->info('📋 Memproses batch scheduling untuk semua mahasiswa yang siap sidang...');

        $result = $this->autoScheduleService->batchScheduleAll();

        $this->info('📊 Hasil Batch Scheduling:');
        $this->info("✅ Berhasil dijadwalkan: {$result['scheduled_count']}");
        $this->info("❌ Gagal dijadwalkan: {$result['failed_count']}");

        if ($this->option('detail') && !empty($result['results'])) {
            $this->info("\n📝 Detail Hasil:");
            
            $headers = ['Nama', 'NIM', 'Status', 'Keterangan'];
            $rows = [];

            foreach ($result['results'] as $item) {
                $rows[] = [
                    $item['mahasiswa'],
                    $item['nim'],
                    $item['result']['success'] ? '✅ Berhasil' : '❌ Gagal',
                    $item['result']['message']
                ];
            }

            $this->table($headers, $rows);
        }

        $this->info("\n🎉 Batch scheduling selesai!");
    }

    /**
     * Simulasi penjadwalan
     */
    protected function simulateSchedule(Mahasiswa $mahasiswa): void
    {
        $this->info('🔍 Menjalankan simulasi auto-schedule...');

        try {
            // Menggunakan reflection untuk mengakses method protected
            $reflection = new \ReflectionClass($this->autoScheduleService);
            $method = $reflection->getMethod('findAvailableSlot');
            $method->setAccessible(true);
            
            $availableSlot = $method->invoke($this->autoScheduleService);

            if ($availableSlot) {
                $this->info('✅ Simulasi berhasil - slot tersedia ditemukan:');
                $this->info("📅 Tanggal: {$availableSlot['date']}");
                $this->info("⏰ Waktu: {$availableSlot['start_time']} - {$availableSlot['end_time']}");
                $this->info("👨‍🏫 Penguji 1 ID: {$availableSlot['penguji1_id']}");
                $this->info("👨‍🏫 Penguji 2 ID: {$availableSlot['penguji2_id']}");
            } else {
                $this->error('❌ Simulasi gagal - tidak ada slot yang tersedia');
            }

        } catch (\Exception $e) {
            $this->error("❌ Error dalam simulasi: {$e->getMessage()}");
        }
    }

    /**
     * Eksekusi penjadwalan aktual
     */
    protected function executeSchedule(int $studentId): void
    {
        $this->info('⚡ Menjalankan auto-schedule...');

        $result = $this->autoScheduleService->scheduleForMahasiswa($studentId);

        if ($result['success']) {
            $this->info('✅ Auto-schedule berhasil!');
            
            if (isset($result['data']['munaqosah'])) {
                $munaqosah = $result['data']['munaqosah'];
                $this->info("📅 Tanggal: {$munaqosah->tanggal_munaqosah}");
                $this->info("⏰ Waktu: {$munaqosah->waktu_mulai} - {$munaqosah->waktu_selesai}");
                $this->info("👨‍🏫 Penguji 1: {$munaqosah->penguji1->nama}");
                $this->info("👨‍🏫 Penguji 2: {$munaqosah->penguji2->nama}");
                $this->info("📊 Status: {$munaqosah->status_konfirmasi}");
            }
        } else {
            $this->error("❌ Auto-schedule gagal: {$result['message']}");
        }
    }

    /**
     * Tampilkan bantuan penggunaan
     */
    protected function showHelp(): void
    {
        $this->info('📖 Cara Penggunaan:');
        $this->info('');
        $this->info('1. Jadwalkan satu mahasiswa:');
        $this->info('   php artisan auto-schedule:run --student=1');
        $this->info('');
        $this->info('2. Jadwalkan semua mahasiswa yang siap sidang:');
        $this->info('   php artisan auto-schedule:run --batch');
        $this->info('');
        $this->info('3. Simulasi tanpa menyimpan:');
        $this->info('   php artisan auto-schedule:run --student=1 --simulate');
        $this->info('');
        $this->info('4. Dengan output detail:');
        $this->info('   php artisan auto-schedule:run --batch --detail');
        $this->info('');
        $this->info('💡 Tips: Gunakan --simulate untuk testing sebelum eksekusi aktual');
    }
}
