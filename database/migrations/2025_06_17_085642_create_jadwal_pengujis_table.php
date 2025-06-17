<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalPengujisTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_pengujis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penguji')
                  ->constrained('pengujis') // Foreign key ke tabel 'pengujis'
                  ->onDelete('cascade'); // Jika penguji dihapus, jadwalnya ikut terhapus
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('deskripsi')->nullable(); // Deskripsi kegiatan (misal: "Kosong", "Rapat", dll.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pengujis');
    }
}