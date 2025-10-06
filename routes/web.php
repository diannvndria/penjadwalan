<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\PengujiController;
use App\Http\Controllers\JadwalPengujiController;
use App\Http\Controllers\MunaqosahController;
use App\Http\Controllers\AutoScheduleController;
use App\Http\Controllers\RuangUjianController;

Route::get('/', [AuthController::class, 'index']);

// === AUTHENTIKASI MANUAL ===
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// === RUTE YANG HANYA BISA DIAKSES OLEH USER YANG SUDAH LOGIN (GENERAL) ===
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute yang bisa diakses oleh user biasa (hanya melihat)
    // Mahasiswa: Lihat daftar mahasiswa
    Route::get('mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    // Jika ada detail mahasiswa: Route::get('mahasiswa/{mahasiswa}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');

    // Dosen: Lihat daftar dosen
    Route::get('dosen', [DosenController::class, 'index'])->name('dosen.index');

    // Penguji: Lihat daftar penguji
    Route::get('penguji', [PengujiController::class, 'index'])->name('penguji.index');

    // Ruang Ujian: Lihat daftar ruang ujian
    Route::get('ruang-ujian', [RuangUjianController::class, 'index'])->name('ruang-ujian.index');

    // Jadwal Penguji: Lihat daftar jadwal penguji
    Route::get('jadwal-penguji', [JadwalPengujiController::class, 'index'])->name('jadwal-penguji.index');

    // Jadwal Munaqosah: Lihat daftar jadwal munaqosah dan histori
    Route::get('munaqosah', [MunaqosahController::class, 'index'])->name('munaqosah.index');
    Route::get('munaqosah/{munaqosah}/histori', [MunaqosahController::class, 'histori'])->name('munaqosah.histori');

    // Tambahan untuk dosen penguji melihat jadwalnya
    // Ini membutuhkan ID user (dosen penguji) yang sedang login
    // Asumsi: Jika user adalah dosen, id user === id dosen di tabel dosens
    // Ini butuh implementasi lebih lanjut di model User/Dosen
    // Untuk saat ini, kita biarkan daftar umum jadwal penguji yang dapat dilihat.
    // Atau bisa buat route: Route::get('jadwal-menguji-saya', [JadwalPengujiController::class, 'mySchedules'])->name('jadwal-menguji.mine');

});


// === RUTE YANG HANYA BISA DIAKSES OLEH ADMIN ===
Route::middleware(['auth'])->group(function () {

    // Admin bisa melakukan CRUD Mahasiswa
    Route::resource('mahasiswa', MahasiswaController::class)->except(['index']); // Kecualikan index karena sudah di atas

    // Admin bisa melakukan CRUD Dosen
    Route::resource('dosen', DosenController::class)->except(['index']); // Kecualikan index

    // Admin bisa melakukan CRUD Penguji
    Route::resource('penguji', PengujiController::class)->except(['index']); // Kecualikan index

    // Admin bisa melakukan CRUD Jadwal Penguji
    Route::resource('jadwal-penguji', JadwalPengujiController::class)->except(['index']); // Kecualikan index

    // Admin bisa melakukan CRUD Jadwal Munaqosah
    Route::resource('munaqosah', MunaqosahController::class)->except(['index', 'histori']); // Kecualikan index & histori

    // Admin bisa melakukan CRUD Ruang Ujian
    Route::resource('ruang-ujian', RuangUjianController::class)->except(['index']); // Kecualikan index karena sudah di atas

    // === AUTO SCHEDULE ROUTES ===
    Route::prefix('auto-schedule')->name('auto-schedule.')->middleware('admin')->group(function () {
        // Halaman utama auto-schedule
        Route::get('/', [AutoScheduleController::class, 'index'])->name('index');

        // Mendapatkan mahasiswa yang siap untuk auto-schedule
        Route::get('/ready-students', [AutoScheduleController::class, 'getReadyStudents'])->name('ready-students');

        // Auto-schedule untuk satu mahasiswa
        Route::post('/schedule-student', [AutoScheduleController::class, 'scheduleStudent'])->name('schedule-student');

        // Auto-schedule untuk semua mahasiswa yang siap sidang
        Route::post('/batch-schedule', [AutoScheduleController::class, 'batchScheduleAll'])->name('batch-schedule');

        // Simulasi auto-schedule (untuk testing)
        Route::post('/simulate', [AutoScheduleController::class, 'simulateSchedule'])->name('simulate');

        // Konfigurasi auto-schedule
        Route::get('/configuration', [AutoScheduleController::class, 'getConfiguration'])->name('configuration');
        Route::put('/configuration', [AutoScheduleController::class, 'updateConfiguration'])->name('update-configuration');
    });
});
