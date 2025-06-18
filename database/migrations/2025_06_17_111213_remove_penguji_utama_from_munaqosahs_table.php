<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePengujiUtamaFromMunaqosahsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('munaqosahs', function (Blueprint $table) {
            // Periksa apakah kolom dan foreign key ada sebelum mencoba menghapusnya
            if (Schema::hasColumn('munaqosahs', 'id_penguji_utama')) {
                // Pastikan untuk menjatuhkan foreign key constraint terlebih dahulu
                // Kita akan menggunakan nama default foreign key Laravel: nama_tabel_kolom_foreign
                // atau bisa juga dengan dropConstrainedForeignId() jika ada.
                // Namun, jika sudah tidak ada, dropConstrainedForeignId() akan error, jadi lebih aman cek.

                // Cek jika foreign key exists sebelum drop
                // Ini adalah bagian yang menyebabkan error Anda jika constraint sudah tidak ada.
                // Cara paling aman adalah membuat foreign key constraint secara manual di down()
                // saat rollback, karena nama constraint bisa bervariasi.
                // Atau gunakan try-catch.
                try {
                    $table->dropForeign(['id_penguji_utama']); // Coba drop constraint
                } catch (\Exception $e) {
                    // Lakukan apa-apa, artinya constraint sudah tidak ada.
                }
                $table->dropColumn('id_penguji_utama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('munaqosahs', function (Blueprint $table) {
            // Saat rollback (down), tambahkan kembali kolomnya.
            // Penting: Pastikan ini sesuai dengan definisi awal kolom Anda.
            if (!Schema::hasColumn('munaqosahs', 'id_penguji_utama')) {
                $table->foreignId('id_penguji_utama')->nullable()->constrained('pengujis')->onDelete('restrict');
            }
        });
    }
}