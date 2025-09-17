<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ruang_ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('lokasi')->nullable();
            $table->unsignedInteger('kapasitas')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_ujian');
    }
};


