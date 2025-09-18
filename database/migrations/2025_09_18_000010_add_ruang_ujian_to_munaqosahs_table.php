<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('munaqosahs', function (Blueprint $table) {
            // Tambah kolom ruang ujian, nullable demi kompatibilitas mundur
            $table->foreignId('id_ruang_ujian')
                ->nullable()
                ->after('id_penguji2')
                ->constrained('ruang_ujian')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('munaqosahs', function (Blueprint $table) {
            $table->dropForeign(['id_ruang_ujian']);
            $table->dropColumn('id_ruang_ujian');
        });
    }
};


