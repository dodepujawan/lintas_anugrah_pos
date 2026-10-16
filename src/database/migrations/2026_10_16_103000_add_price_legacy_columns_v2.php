<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Disable ALL strict sql_mode (old FoxPro data has 0000-00-00 timestamps + truncated TANGGAL)
        if ($driver === 'mysql') {
            DB::statement("SET SESSION sql_mode = ''");
            $tables = ['pricecus', 'pricecushis', 'prices', 'pricedingins', 'pricedingincus', 'pricedinginhis'];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    // Fix invalid timestamp values
                    DB::statement("UPDATE `$table` SET `updated_at` = NULL WHERE `updated_at` = '0000-00-00 00:00:00'");
                    DB::statement("UPDATE `$table` SET `created_at` = NULL WHERE `created_at` = '0000-00-00 00:00:00'");

                    if (Schema::hasColumn($table, 'TANGGAL')) {
                        // Clean truncated single-char values before column type change
                        DB::statement("UPDATE `$table` SET `TANGGAL` = NULL WHERE `TANGGAL` REGEXP '^[0-9]$'");
                        DB::statement("ALTER TABLE `$table` MODIFY COLUMN `TANGGAL` VARCHAR(10) NULL");
                    }
                }
            }
        }

        // Add missing legacy columns to pricecushis
        Schema::table('pricecushis', function (Blueprint $table) {
            if (! Schema::hasColumn('pricecushis', 'KODE')) {
                $table->string('KODE', 50)->nullable()->after('KODECUS');
            }
            if (! Schema::hasColumn('pricecushis', 'DARI')) {
                $table->decimal('DARI', 10, 0)->default(0)->after('TANGGAL');
            }
            if (! Schema::hasColumn('pricecushis', 'SAMPAI')) {
                $table->decimal('SAMPAI', 10, 0)->default(0)->after('DARI');
            }
            if (! Schema::hasColumn('pricecushis', 'RUTE')) {
                $table->string('RUTE', 30)->nullable()->after('SAMPAI');
            }
            if (! Schema::hasColumn('pricecushis', 'KETERANGAN')) {
                $table->string('KETERANGAN', 50)->nullable()->after('RUTE');
            }
            if (! Schema::hasColumn('pricecushis', 'HV')) {
                $table->decimal('HV', 10, 0)->default(0)->after('KETERANGAN');
            }
            if (! Schema::hasColumn('pricecushis', 'HKG')) {
                $table->decimal('HKG', 10, 0)->default(0)->after('HV');
            }
            if (! Schema::hasColumn('pricecushis', 'HBOK')) {
                $table->decimal('HBOK', 10, 0)->default(0)->after('HKG');
            }
            if (! Schema::hasColumn('pricecushis', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('HBOK');
            }
            if (! Schema::hasColumn('pricecushis', 'HG')) {
                $table->decimal('HG', 1, 0)->default(0)->after('KUNCI');
            }
            if (! Schema::hasColumn('pricecushis', 'JENIS')) {
                $table->string('JENIS', 1)->nullable()->after('HG');
            }
        });

        // Add missing legacy columns to prices (in case 093000 didn't run for prices)
        Schema::table('prices', function (Blueprint $table) {
            if (! Schema::hasColumn('prices', 'KODE')) {
                $table->string('KODE', 20)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('prices', 'DARI')) {
                $table->decimal('DARI', 10, 0)->default(0)->after('TANGGAL');
            }
            if (! Schema::hasColumn('prices', 'SAMPAI')) {
                $table->decimal('SAMPAI', 10, 0)->default(0)->after('DARI');
            }
            if (! Schema::hasColumn('prices', 'RUTE')) {
                $table->string('RUTE', 30)->nullable()->after('SAMPAI');
            }
            if (! Schema::hasColumn('prices', 'KETERANGAN')) {
                $table->string('KETERANGAN', 50)->nullable()->after('RUTE');
            }
            if (! Schema::hasColumn('prices', 'HV')) {
                $table->decimal('HV', 10, 0)->default(0)->after('KETERANGAN');
            }
            if (! Schema::hasColumn('prices', 'HKG')) {
                $table->decimal('HKG', 10, 0)->default(0)->after('HV');
            }
            if (! Schema::hasColumn('prices', 'HBOK')) {
                $table->decimal('HBOK', 10, 0)->default(0)->after('HKG');
            }
            if (! Schema::hasColumn('prices', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('HBOK');
            }
            if (! Schema::hasColumn('prices', 'HG')) {
                $table->decimal('HG', 1, 0)->default(0)->after('KUNCI');
            }
            if (! Schema::hasColumn('prices', 'JENIS')) {
                $table->string('JENIS', 1)->nullable()->after('HG');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode = DEFAULT");
        }
    }
};
