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
        Schema::table('jadwal_pengujis', function (Blueprint $table) {
            // Add indexes for commonly queried columns
            $table->index('tanggal');
            $table->index('waktu_mulai');
            $table->index(['id_penguji', 'tanggal']); // Compound index for filtering by penguji and date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pengujis', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['waktu_mulai']);
            $table->dropIndex(['id_penguji', 'tanggal']);
        });
    }
};
