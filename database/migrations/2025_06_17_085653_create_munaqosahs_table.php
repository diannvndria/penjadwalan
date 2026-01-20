<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunaqosahsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('munaqosahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')
                ->constrained('mahasiswas') // Foreign key ke tabel 'mahasiswas'
                ->onDelete('cascade'); // Jika mahasiswa dihapus, jadwal munaqosahnya ikut terhapus
            $table->date('tanggal_munaqosah');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->foreignId('id_penguji1')->constrained('pengujis')->onDelete('restrict'); // Penguji wajib
            $table->foreignId('id_penguji2')->nullable()->constrained('pengujis')->onDelete('restrict'); // Penguji opsional
            $table->enum('status_konfirmasi', ['pending', 'dikonfirmasi', 'ditolak'])->default('pending'); // Status konfirmasi admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('munaqosahs');
    }
}
