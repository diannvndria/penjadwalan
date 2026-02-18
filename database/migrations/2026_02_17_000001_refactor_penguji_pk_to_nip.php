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
        // 1. Ensure 'nip' column exists and is populated in 'pengujis' (table name is plural 'pengujis' or singular 'penguji'?)
        // Based on file list, verify table name. '2025_06_17_085532_create_pengujis_table.php' created 'pengujis'.
        // But '2026_01_31_172846_rename_tables_to_singular_form' renamed it to 'penguji'.
        // So table name is 'penguji'.

        if (! Schema::hasColumn('penguji', 'nip')) {
            Schema::table('penguji', function (Blueprint $table) {
                // If column doesn't exist, addTo it.
                // Note: '2026_02_01_222202_add_nip_to_penguji_table.php' might have added it.
                // If it exists, this block skipped.
                $table->string('nip')->nullable()->after('id');
            });
        }

        // Ensure all pengujis have a NIP.
        // Use raw SQL to avoiding query builder issues with cached plans
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE penguji SET nip = strftime('%Y%m%d', 'now') || abs(random() % 9000 + 1000) || id WHERE nip IS NULL OR nip = ''");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE penguji SET nip = to_char(now(), 'YYYYMMDD') || floor(random() * 9000 + 1000)::int || id WHERE nip IS NULL OR nip = ''");
        } else {
            // MySQL/MariaDB fallback
            DB::statement("UPDATE penguji SET nip = concat(date_format(now(), '%Y%m%d'), floor(rand() * 9000 + 1000), id) WHERE nip IS NULL OR nip = ''");
        }

        // Make 'nip' not null
        Schema::table('penguji', function (Blueprint $table) {
            $table->string('nip')->nullable(false)->change();
        });

        // Add unique constraint safely
        Schema::table('penguji', function (Blueprint $table) {
            // We give it a specific name to ensure we know it.
            // If it already exists, this might fail, but in migrate:fresh (which user just did) it should be fine.
            // However, '2026_02_01_222202_add_nip_to_penguji_table.php' added user defined unique?
            // No, that migration just added the column.
            // Let's add it if not exists, but Schema doesn't have hasIndex easily.
            // We'll rely on try-catch or just add it.
            // Since previous migration didn't add uniqye, we add it here.
            try {
                $table->unique('nip', 'penguji_nip_unique');
            } catch (\Exception $e) {
                // Ignore if already exists
            }
        });

        // 2. Add temporary columns to 'munaqosah' (id_penguji1, id_penguji2 -> penguji1_nip, penguji2_nip)
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->string('penguji1_nip')->nullable()->after('id_penguji1');
            $table->string('penguji2_nip')->nullable()->after('id_penguji2');
        });

        // 3. Add temporary column to 'jadwal_penguji' (id_penguji -> penguji_nip)
        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->string('penguji_nip')->nullable()->after('id_penguji');
        });

        // 4. Populate temporary columns
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('UPDATE munaqosah SET penguji1_nip = (SELECT nip FROM penguji WHERE penguji.id = munaqosah.id_penguji1)');
            DB::statement('UPDATE munaqosah SET penguji2_nip = (SELECT nip FROM penguji WHERE penguji.id = munaqosah.id_penguji2)');
            DB::statement('UPDATE jadwal_penguji SET penguji_nip = (SELECT nip FROM penguji WHERE penguji.id = jadwal_penguji.id_penguji)');
        } else {
            // Postgres/MySQL
            DB::statement('
                UPDATE munaqosah
                SET penguji1_nip = penguji.nip
                FROM penguji
                WHERE munaqosah.id_penguji1 = penguji.id
            ');
            DB::statement('
                UPDATE munaqosah
                SET penguji2_nip = penguji.nip
                FROM penguji
                WHERE munaqosah.id_penguji2 = penguji.id
            ');
            DB::statement('
                UPDATE jadwal_penguji
                SET penguji_nip = penguji.nip
                FROM penguji
                WHERE jadwal_penguji.id_penguji = penguji.id
            ');
        }

        // 5. Drop old FKs and columns
        Schema::table('munaqosah', function (Blueprint $table) {
            // Drop FKs. Names might be 'munaqosahs_id_penguji1_foreign' etc.
            // Using array syntax lets Laravel guess.
            // Note: table was renamed to 'munaqosah', but FKs might still have 'munaqosahs_' prefix if not renamed.
            // Better to try dropping by explicit name if guessing fails, but array syntax usually works if Laravel conventions followed.
            // Given migration history, '2025_06_17_085653_create_munaqosahs_table.php' created them.
            // FK names likely 'munaqosahs_id_penguji1_foreign'.
            // When renaming table, Laravel usually renames indexes/FKs? Not necessarily.
            // Let's rely on array syntax, references original column name.
            $table->dropForeign(['id_penguji1']);
            $table->dropForeign(['id_penguji2']);
            $table->dropColumn(['id_penguji1', 'id_penguji2']);
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->dropForeign(['id_penguji']);
            $table->dropIndex('jadwal_pengujis_id_penguji_tanggal_index');
            $table->dropColumn('id_penguji');
        });

        // 6. Switch PK in 'penguji' table to NIP (Recreate table)
        Schema::create('penguji_new', function (Blueprint $table) {
            $table->string('nip')->primary();
            $table->string('nama');
            $table->boolean('is_prioritas')->default(false);
            $table->string('keterangan_prioritas')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO penguji_new (nip, nama, is_prioritas, keterangan_prioritas, created_at, updated_at)
                       SELECT nip, nama, is_prioritas, keterangan_prioritas, created_at, updated_at FROM penguji');

        Schema::drop('penguji');
        Schema::rename('penguji_new', 'penguji');

        // 7. Restore Relationships
        // Munaqosah
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->renameColumn('penguji1_nip', 'id_penguji1');
            $table->renameColumn('penguji2_nip', 'id_penguji2');
        });

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->string('id_penguji1')->nullable(false)->change();
            $table->string('id_penguji2')->nullable()->change();

            $table->foreign('id_penguji1')->references('nip')->on('penguji')->onDelete('restrict');
            $table->foreign('id_penguji2')->references('nip')->on('penguji')->onDelete('restrict');
        });

        // JadwalPenguji
        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->renameColumn('penguji_nip', 'id_penguji');
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->string('id_penguji')->nullable(false)->change();
            $table->foreign('id_penguji')->references('nip')->on('penguji')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible without losing NIP-ID mapping if original IDs are lost.
        // But we can restore structure.

        // 1. Revert relationships
        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropForeign(['id_penguji1']);
            $table->dropForeign(['id_penguji2']);
            $table->renameColumn('id_penguji1', 'penguji1_nip');
            $table->renameColumn('id_penguji2', 'penguji2_nip');
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->dropForeign(['id_penguji']);
            $table->renameColumn('id_penguji', 'penguji_nip');
        });

        // 2. Revert Penguji Table (Add ID back)
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('penguji_old', function (Blueprint $table) {
                $table->id();
                $table->string('nip')->nullable();
                $table->string('nama');
                $table->boolean('is_prioritas')->default(false);
                $table->string('keterangan_prioritas')->nullable();
                $table->timestamps();
            });
            DB::statement('INSERT INTO penguji_old (nip, nama, is_prioritas, keterangan_prioritas, created_at, updated_at)
                           SELECT nip, nama, is_prioritas, keterangan_prioritas, created_at, updated_at FROM penguji');
            Schema::drop('penguji');
            Schema::rename('penguji_old', 'penguji');
        } else {
            Schema::table('penguji', function (Blueprint $table) {
                $table->dropPrimary(['nip']);
            });
            Schema::table('penguji', function (Blueprint $table) {
                $table->id()->first();
                $table->unique('nip');
            });
        }

        // 3. Restore Foreign Keys (This assumes we can map back, which we can't reliably without stored map.
        // But for structure, we add columns back and FKs. Data might be broken for FKs.)

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->unsignedBigInteger('id_penguji1')->nullable()->after('penguji1_nip');
            $table->unsignedBigInteger('id_penguji2')->nullable()->after('penguji2_nip');
        });

        // Populate IDs from NIPs
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('UPDATE munaqosah SET id_penguji1 = (SELECT id FROM penguji WHERE penguji.nip = munaqosah.penguji1_nip)');
            DB::statement('UPDATE munaqosah SET id_penguji2 = (SELECT id FROM penguji WHERE penguji.nip = munaqosah.penguji2_nip)');
        } else {
            DB::statement('UPDATE munaqosah SET id_penguji1 = penguji.id FROM penguji WHERE munaqosah.penguji1_nip = penguji.nip');
            DB::statement('UPDATE munaqosah SET id_penguji2 = penguji.id FROM penguji WHERE munaqosah.penguji2_nip = penguji.nip');
        }

        Schema::table('munaqosah', function (Blueprint $table) {
            $table->dropColumn(['penguji1_nip', 'penguji2_nip']);
            $table->foreign('id_penguji1')->references('id')->on('penguji');
            $table->foreign('id_penguji2')->references('id')->on('penguji');
        });

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->unsignedBigInteger('id_penguji')->nullable()->after('penguji_nip');
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('UPDATE jadwal_penguji SET id_penguji = (SELECT id FROM penguji WHERE penguji.nip = jadwal_penguji.penguji_nip)');
        } else {
            DB::statement('UPDATE jadwal_penguji SET id_penguji = penguji.id FROM penguji WHERE jadwal_penguji.penguji_nip = penguji.nip');
        }

        Schema::table('jadwal_penguji', function (Blueprint $table) {
            $table->dropColumn('penguji_nip');
            $table->foreign('id_penguji')->references('id')->on('penguji');
        });
    }
};
