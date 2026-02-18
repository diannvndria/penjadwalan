<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add temporary column to munaqosah to store NIM
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->string('mahasiswa_nim')->nullable()->after('id_mahasiswa');
        });

        // 2. Migrate data: Populate munaqosah.mahasiswa_nim from mahasiswa.nim
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE munaqosah
                SET mahasiswa_nim = (SELECT nim FROM mahasiswa WHERE mahasiswa.id = munaqosah.id_mahasiswa)
            ');
        } else {
            // PostgreSQL requires FROM for joins in UPDATE
            DB::statement('
                UPDATE munaqosah
                SET mahasiswa_nim = mahasiswa.nim
                FROM mahasiswa
                WHERE munaqosah.id_mahasiswa = mahasiswa.id
            ');
        }

        // 3. Drop FK and old column on munaqosah
        Schema::table('munaqosah', function (Blueprint $table) {
            // Drop foreign key first. Naming convention might vary, so we try standard.
            // based on previous files, it seems to be 'munaqosah_id_mahasiswa_foreign' or similar.
            $table->dropForeign(['id_mahasiswa']);
            $table->dropColumn('id_mahasiswa');
        });

        // 4. Modify mahasiswa table: Drop ID, make NIM primary
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support dropping PK column easily. We must recreate the table.
            Schema::create('mahasiswa_new', function (Blueprint $table) {
                $table->string('nim')->primary();
                $table->string('nama');
                $table->unsignedInteger('angkatan');
                $table->string('judul_skripsi');
                $table->enum('profil_lulusan', ['Ilmuwan', 'Wirausaha', 'Profesional'])->nullable();
                $table->enum('penjurusan', ['Sistem Informasi', 'Perekayasa Perangkat Lunak', 'Perekayasa Jaringan Komputer', 'Sistem Cerdas'])->nullable();
                $table->foreignId('id_dospem')->constrained('dosen')->onDelete('restrict');
                $table->boolean('siap_sidang')->default(false);
                $table->boolean('is_prioritas')->default(false);
                $table->boolean('prioritas_jadwal')->default(false);
                $table->string('keterangan_prioritas')->nullable();
                $table->timestamps();
            });

            // Copy data
            // Note: We need to map columns explicitly if order differs, but here we just select matching cols
            DB::statement('INSERT INTO mahasiswa_new (nim, nama, angkatan, judul_skripsi, profil_lulusan, penjurusan, id_dospem, siap_sidang, is_prioritas, prioritas_jadwal, keterangan_prioritas, created_at, updated_at)
                           SELECT nim, nama, angkatan, judul_skripsi, profil_lulusan, penjurusan, id_dospem, siap_sidang, is_prioritas, prioritas_jadwal, keterangan_prioritas, created_at, updated_at FROM mahasiswa');

            Schema::drop('mahasiswa');
            Schema::rename('mahasiswa_new', 'mahasiswa');
        } else {
            // Drop PK constraint safely using raw SQL to handle potential naming differences
            // and because dropPrimary() might not handle custom names correctly in all contexts
            DB::statement('ALTER TABLE mahasiswa DROP CONSTRAINT IF EXISTS "mahasiswa_pkey"');
            DB::statement('ALTER TABLE mahasiswa DROP CONSTRAINT IF EXISTS "mahasiswas_pkey"');

            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropColumn('id');
            });

            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->primary('nim');
            });
        }

        // 5. Finalize munaqosah table: Rename column and add new FK
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->renameColumn('mahasiswa_nim', 'id_mahasiswa');
        });

        Schema::table('munaqosah', function (Blueprint $table) {
            // Make it not null after population
            $table->string('id_mahasiswa')->nullable(false)->change();

            $table->foreign('id_mahasiswa')
                ->references('nim')
                ->on('mahasiswa')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing is complex because we lost the original integer IDs.
        // We have to recreate IDs for mahasiswa and then update munaqosah.

        // 1. Add ID column back to mahasiswa
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropPrimary('nim');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->id()->first();
        });

        // 2. Add temporary column for integer ID in munaqosah
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropForeign(['id_mahasiswa']);
            $table->unsignedBigInteger('mahasiswa_id_new')->nullable()->after('id_mahasiswa');
        });

        // 3. Restore data: Populate munaqosah.mahasiswa_id_new using join on NIM
        DB::statement('
            UPDATE munaqosah
            SET mahasiswa_id_new = mahasiswa.id
            FROM mahasiswa
            WHERE munaqosah.id_mahasiswa = mahasiswa.nim
        ');

        // 4. Drop NIM column in munaqosah (which is currently named id_mahasiswa)
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropColumn('id_mahasiswa');
        });

        // 5. Rename and add FK
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->renameColumn('mahasiswa_id_new', 'id_mahasiswa');
        });

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mahasiswa')->nullable(false)->change();

            $table->foreign('id_mahasiswa')
                ->references('id')
                ->on('mahasiswa')
                ->onDelete('cascade');
        });
    }
};
