<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah field prioritas di tabel mahasiswas
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->boolean('is_prioritas')->default(false)->after('siap_sidang');
            $table->text('keterangan_prioritas')->nullable()->after('is_prioritas');
        });

        // Tambah field prioritas di tabel pengujis
        Schema::table('pengujis', function (Blueprint $table) {
            $table->boolean('is_prioritas')->default(false)->after('nama');
            $table->text('keterangan_prioritas')->nullable()->after('is_prioritas');
        });

        // Tambah field lantai dan prioritas di tabel ruang_ujian
        Schema::table('ruang_ujian', function (Blueprint $table) {
            $table->integer('lantai')->default(1)->after('nama');
            $table->boolean('is_prioritas')->default(false)->after('lantai')
                ->comment('Ruang khusus untuk mahasiswa/penguji prioritas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['is_prioritas', 'keterangan_prioritas']);
        });

        Schema::table('pengujis', function (Blueprint $table) {
            $table->dropColumn(['is_prioritas', 'keterangan_prioritas']);
        });

        Schema::table('ruang_ujian', function (Blueprint $table) {
            $table->dropColumn(['lantai', 'is_prioritas']);
        });
    }
};
