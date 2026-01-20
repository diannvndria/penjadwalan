<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique(); // NIM harus unik
            $table->string('nama');
            $table->unsignedInteger('angkatan'); // Angkatan, bilangan bulat positif
            $table->string('judul_skripsi');
            $table->foreignId('id_dospem')
                ->constrained('dosens') // Foreign key ke tabel 'dosens'
                ->onDelete('restrict'); // Jika dosen dihapus, data mahasiswa tidak ikut terhapus (harus hapus mahasiswa dulu)
            $table->boolean('siap_sidang')->default(false); // Status siap sidang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
}
