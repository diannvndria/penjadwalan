<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilLulusanAndPenjurusanToMahasiswasTable extends Migration
{
    /**
     * Run the migrations.
     * Metode ini akan dijalankan saat `php artisan migrate`.
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Menambahkan kolom 'profil_lulusan' (contoh: Ilmuwan, Wirausaha, Profesional)
            // Default bisa disesuaikan atau dibuat nullable jika tidak wajib
            $table->string('profil_lulusan')->nullable()->after('judul_skripsi');

            // Menambahkan kolom 'penjurusan' (contoh: Sistem Informasi, Perekayasa Perangkat Lunak, dll.)
            // Default bisa disesuaikan atau dibuat nullable jika tidak wajib
            $table->string('penjurusan')->nullable()->after('profil_lulusan');
        });
    }

    /**
     * Reverse the migrations.
     * Metode ini akan dijalankan saat `php artisan migrate:rollback`.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Menghapus kolom 'profil_lulusan' jika migrasi di-rollback
            $table->dropColumn('profil_lulusan');
            // Menghapus kolom 'penjurusan' jika migrasi di-rollback
            $table->dropColumn('penjurusan');
        });
    }
}
