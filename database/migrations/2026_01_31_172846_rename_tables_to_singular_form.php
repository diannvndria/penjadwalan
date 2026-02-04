<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename Indonesian pluralized tables to singular form.
     * Table mappings:
     * - dosens -> dosen
     * - pengujis -> penguji
     * - mahasiswas -> mahasiswa
     * - jadwal_pengujis -> jadwal_penguji
     * - munaqosahs -> munaqosah
     * - histori_munaqosahs -> histori_munaqosah
     */
    public function up(): void
    {
        // Step 1: Drop all foreign key constraints first (database-agnostic approach)
        Schema::table('histori_munaqosahs', function (Blueprint $table) {
            $table->dropForeign(['id_munaqosah']);
            $table->dropForeign(['dilakukan_oleh']);
        });

        Schema::table('munaqosahs', function (Blueprint $table) {
            $table->dropForeign(['id_mahasiswa']);
            $table->dropForeign(['id_penguji1']);
            $table->dropForeign(['id_penguji2']);
            $table->dropForeign(['id_ruang_ujian']);
        });

        Schema::table('jadwal_pengujis', function (Blueprint $table) {
            $table->dropForeign(['id_penguji']);
        });

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['id_dospem']);
        });

        // Step 2: Rename tables (order matters - child tables first, then parent tables)
        Schema::rename('histori_munaqosahs', 'histori_munaqosah');
        Schema::rename('jadwal_pengujis', 'jadwal_penguji');
        Schema::rename('munaqosahs', 'munaqosah');
        Schema::rename('mahasiswas', 'mahasiswa');
        Schema::rename('pengujis', 'penguji');
        Schema::rename('dosens', 'dosen');

        // Step 3: Re-add foreign key constraints with new table names
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreign('id_dospem')
                ->references('id')
                ->on('dosen')
                ->onDelete('restrict');
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->foreign('id_penguji')
                ->references('id')
                ->on('penguji')
                ->onDelete('cascade');
        });

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->foreign('id_mahasiswa')
                ->references('id')
                ->on('mahasiswa')
                ->onDelete('cascade');
            $table->foreign('id_penguji1')
                ->references('id')
                ->on('penguji')
                ->onDelete('restrict');
            $table->foreign('id_penguji2')
                ->references('id')
                ->on('penguji')
                ->onDelete('restrict');
            $table->foreign('id_ruang_ujian')
                ->references('id')
                ->on('ruang_ujian')
                ->onDelete('restrict');
        });

        Schema::table('histori_munaqosah', function (Blueprint $table) {
            $table->foreign('id_munaqosah')
                ->references('id')
                ->on('munaqosah')
                ->onDelete('cascade');
            $table->foreign('dilakukan_oleh')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Step 4: Rename sequences for PostgreSQL only (SQLite doesn't use sequences)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER SEQUENCE IF EXISTS dosens_id_seq RENAME TO dosen_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS pengujis_id_seq RENAME TO penguji_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS mahasiswas_id_seq RENAME TO mahasiswa_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS jadwal_pengujis_id_seq RENAME TO jadwal_penguji_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS munaqosahs_id_seq RENAME TO munaqosah_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS histori_munaqosahs_id_seq RENAME TO histori_munaqosah_id_seq');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop all foreign key constraints
        Schema::table('histori_munaqosah', function (Blueprint $table) {
            $table->dropForeign(['id_munaqosah']);
            $table->dropForeign(['dilakukan_oleh']);
        });

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropForeign(['id_mahasiswa']);
            $table->dropForeign(['id_penguji1']);
            $table->dropForeign(['id_penguji2']);
            $table->dropForeign(['id_ruang_ujian']);
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->dropForeign(['id_penguji']);
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['id_dospem']);
        });

        // Step 2: Rename tables back (parent tables first, then child tables)
        Schema::rename('dosen', 'dosens');
        Schema::rename('penguji', 'pengujis');
        Schema::rename('mahasiswa', 'mahasiswas');
        Schema::rename('munaqosah', 'munaqosahs');
        Schema::rename('jadwal_penguji', 'jadwal_pengujis');
        Schema::rename('histori_munaqosah', 'histori_munaqosahs');

        // Step 3: Re-add foreign key constraints with old table names
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreign('id_dospem', 'mahasiswas_id_dospem_foreign')
                ->references('id')
                ->on('dosens')
                ->onDelete('restrict');
        });

        Schema::table('jadwal_pengujis', function (Blueprint $table) {
            $table->foreign('id_penguji', 'jadwal_pengujis_id_penguji_foreign')
                ->references('id')
                ->on('pengujis')
                ->onDelete('cascade');
        });

        Schema::table('munaqosahs', function (Blueprint $table) {
            $table->foreign('id_mahasiswa', 'munaqosahs_id_mahasiswa_foreign')
                ->references('id')
                ->on('mahasiswas')
                ->onDelete('cascade');
            $table->foreign('id_penguji1', 'munaqosahs_id_penguji1_foreign')
                ->references('id')
                ->on('pengujis')
                ->onDelete('restrict');
            $table->foreign('id_penguji2', 'munaqosahs_id_penguji2_foreign')
                ->references('id')
                ->on('pengujis')
                ->onDelete('restrict');
            $table->foreign('id_ruang_ujian', 'munaqosahs_id_ruang_ujian_foreign')
                ->references('id')
                ->on('ruang_ujian')
                ->onDelete('restrict');
        });

        Schema::table('histori_munaqosahs', function (Blueprint $table) {
            $table->foreign('id_munaqosah', 'histori_munaqosahs_id_munaqosah_foreign')
                ->references('id')
                ->on('munaqosahs')
                ->onDelete('cascade');
            $table->foreign('dilakukan_oleh', 'histori_munaqosahs_dilakukan_oleh_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Step 4: Rename sequences back (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER SEQUENCE IF EXISTS dosen_id_seq RENAME TO dosens_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS penguji_id_seq RENAME TO pengujis_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS mahasiswa_id_seq RENAME TO mahasiswas_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS jadwal_penguji_id_seq RENAME TO jadwal_pengujis_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS munaqosah_id_seq RENAME TO munaqosahs_id_seq');
            DB::statement('ALTER SEQUENCE IF EXISTS histori_munaqosah_id_seq RENAME TO histori_munaqosahs_id_seq');
        }
    }
};
