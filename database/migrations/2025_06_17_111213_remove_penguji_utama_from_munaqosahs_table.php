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
            // Pastikan untuk menjatuhkan foreign key constraint terlebih dahulu
            $table->dropConstrainedForeignId('id_penguji_utama');
            // Kemudian jatuhkan kolomnya
            $table->dropColumn('id_penguji_utama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('munaqosahs', function (Blueprint $table) {
            // Tambahkan kembali kolom dan foreign key jika ingin rollback
            $table->foreignId('id_penguji_utama')->nullable()->constrained('pengujis')->onDelete('restrict');
        });
    }
}