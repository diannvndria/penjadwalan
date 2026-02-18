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
        // 1. Ensure 'nip' column exists and is populated in 'dosen'
        // Accessing 'nip' from existing migration if it exists, otherwise add it.
        if (! Schema::hasColumn('dosen', 'nip')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->string('nip')->nullable()->after('id');
            });
        }

        // Ensure all dosens have a NIP. If not, generate a dummy one to avoid constraint violation.
        // This is critical for staging environments with existing data.
        // Use raw SQL to update NIP to avoid "cached plan must not change result type" error in Postgres
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE dosen SET nip = strftime('%Y%m%d', 'now') || abs(random() % 9000 + 1000) || id WHERE nip IS NULL OR nip = ''");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE dosen SET nip = to_char(now(), 'YYYYMMDD') || floor(random() * 9000 + 1000)::int || id WHERE nip IS NULL OR nip = ''");
        } else {
            // MySQL/MariaDB fallback
            DB::statement("UPDATE dosen SET nip = concat(date_format(now(), '%Y%m%d'), floor(rand() * 9000 + 1000), id) WHERE nip IS NULL OR nip = ''");
        }

        // Make 'nip' not null and unique (preparatory step)
        // Make 'nip' not null
        Schema::table('dosen', function (Blueprint $table) {
            $table->string('nip')->nullable(false)->change();
        });

        // Add proper unique constraint which might not exist from previous migration
        // (previous migration adds column but not unique)
        // Check if unique index exists to be safe for incremental runs,
        // but for fresh runs we just add it.
        // Since catching exception aborts transaction in Postgres, we avoid dropUnique inside try-catch.

        // We will just add the unique constraint.
        // If this fails strictly because it already exists (unlikely in fresh, possible in broken state),
        // recovering without aborting transaction is hard in pure PHP/PDO/Postgres without savepoints.
        // Assuming migrate:fresh state:
        Schema::table('dosen', function (Blueprint $table) {
            // In Laravel, unique() on existing column adds the constraint.
            // We give it a specific name to ensure we know it.
            $table->unique('nip', 'dosen_nip_unique');
        });

        // 2. Add temporary column 'dospem_nip' to 'mahasiswa'
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('dospem_nip')->nullable()->after('id_dospem');
        });

        // 3. Populate 'mahasiswa.dospem_nip' from 'dosen.nip'
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE mahasiswa
                SET dospem_nip = (SELECT nip FROM dosen WHERE dosen.id = mahasiswa.id_dospem)
            ');
        } else {
            // PostgreSQL requires FROM for joins in UPDATE
            DB::statement('
                UPDATE mahasiswa
                SET dospem_nip = dosen.nip
                FROM dosen
                WHERE mahasiswa.id_dospem = dosen.id
            ');
        }

        // 4. Drop old FK and column in 'mahasiswa'
        Schema::table('mahasiswa', function (Blueprint $table) {
            // Drop foreign key using array syntax to let Laravel handle naming
            $table->dropForeign(['id_dospem']);
            $table->dropColumn('id_dospem');
        });

        // 5. Switch PK in 'dosen' table
        // 5. Switch PK in 'dosen' table
        // We recreate the table to ensure 'nip' is the first column for all drivers
        Schema::create('dosen_new', function (Blueprint $table) {
            $table->string('nip')->primary();
            $table->string('nama');
            $table->integer('kapasitas_ampu')->default(0);
            $table->timestamps();
        });

        DB::statement('INSERT INTO dosen_new (nip, nama, kapasitas_ampu, created_at, updated_at)
                       SELECT nip, nama, kapasitas_ampu, created_at, updated_at FROM dosen');

        Schema::drop('dosen');
        Schema::rename('dosen_new', 'dosen');

        // 6. Rename 'dospem_nip' to 'id_dospem' and add FK in 'mahasiswa'
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->renameColumn('dospem_nip', 'id_dospem');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('id_dospem')->nullable(false)->change();
        });

        // Use raw SQL to add foreign key to ensure it references 'nip'
        // Schema builder might have issues if table state is confused
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->foreign('id_dospem')->references('nip')->on('dosen')->onDelete('restrict');
            });
        } else {
            // For Postgres specifically
            DB::statement('ALTER TABLE mahasiswa ADD CONSTRAINT mahasiswa_id_dospem_foreign FOREIGN KEY (id_dospem) REFERENCES dosen (nip) ON DELETE RESTRICT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert 'mahasiswa' FK: Drop FK, rename column?, No, we need to map back to IDs.
        // But IDs are lost from 'dosen' table. We'd need to re-generate IDs.

        // This down migration essentially restores the structure but IDs will be new.

        // Add 'id' back to 'dosen'
        Schema::table('dosen', function (Blueprint $table) {
            $table->dropPrimary(['nip']);
            // In Postgres, adding auto-increment ID to existing table is tricky without dropping data or complex steps.
            // Simplest for 'down': Add column, rename table/copy?
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::create('dosen_old', function (Blueprint $table) {
                $table->id();
                $table->string('nip')->nullable();
                $table->string('nama');
                $table->integer('kapasitas_ampu')->default(0);
                $table->timestamps();
            });
            DB::statement('INSERT INTO dosen_old (nip, nama, kapasitas_ampu, created_at, updated_at) SELECT nip, nama, kapasitas_ampu, created_at, updated_at FROM dosen');
            Schema::drop('dosen');
            Schema::rename('dosen_old', 'dosen');
        } else {
            Schema::table('dosen', function (Blueprint $table) {
                $table->id()->first(); // This adds auto-increment primary key
            });
        }

        // Now update 'mahasiswa'
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['id_dospem']);
            $table->string('dospem_nip_temp')->after('id_dospem'); // Store NIP temporarily
        });

        DB::statement('UPDATE mahasiswa SET dospem_nip_temp = id_dospem');

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn('id_dospem');
            $table->unsignedBigInteger('id_dospem')->nullable()->after('dospem_nip_temp');
        });

        // Populate 'mahasiswa.id_dospem' from 'dosen.id' using NIP join
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE mahasiswa
                SET id_dospem = (SELECT id FROM dosen WHERE dosen.nip = mahasiswa.dospem_nip_temp)
            ');
        } else {
            DB::statement('
                UPDATE mahasiswa
                SET id_dospem = dosen.id
                FROM dosen
                WHERE mahasiswa.dospem_nip_temp = dosen.nip
            ');
        }

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn('dospem_nip_temp');
            $table->unsignedBigInteger('id_dospem')->nullable(false)->change();
            $table->foreign('id_dospem')->references('id')->on('dosen')->onDelete('restrict');
        });
    }
};
