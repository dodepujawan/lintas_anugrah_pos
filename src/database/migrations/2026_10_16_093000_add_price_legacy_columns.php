<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricecus', function (Blueprint $table) {
            if (! Schema::hasColumn('pricecus', 'KODE')) {
                $table->string('KODE', 50)->nullable()->after('KODECUS');
            }
            if (! Schema::hasColumn('pricecus', 'DARI')) {
                $table->decimal('DARI', 10, 0)->default(0)->after('TANGGAL');
            }
            if (! Schema::hasColumn('pricecus', 'SAMPAI')) {
                $table->decimal('SAMPAI', 10, 0)->default(0)->after('DARI');
            }
            if (! Schema::hasColumn('pricecus', 'RUTE')) {
                $table->string('RUTE', 30)->nullable()->after('SAMPAI');
            }
            if (! Schema::hasColumn('pricecus', 'KETERANGAN')) {
                $table->string('KETERANGAN', 50)->nullable()->after('RUTE');
            }
            if (! Schema::hasColumn('pricecus', 'HV')) {
                $table->decimal('HV', 10, 0)->default(0)->after('KETERANGAN');
            }
            if (! Schema::hasColumn('pricecus', 'HKG')) {
                $table->decimal('HKG', 10, 0)->default(0)->after('HV');
            }
            if (! Schema::hasColumn('pricecus', 'HBOK')) {
                $table->decimal('HBOK', 10, 0)->default(0)->after('HKG');
            }
            if (! Schema::hasColumn('pricecus', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('HBOK');
            }
            if (! Schema::hasColumn('pricecus', 'HG')) {
                $table->decimal('HG', 1, 0)->default(0)->after('KUNCI');
            }
            if (! Schema::hasColumn('pricecus', 'JENIS')) {
                $table->string('JENIS', 1)->nullable()->after('HG');
            }
        });

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

        // Pricedingin tables
        Schema::table('pricedingins', function (Blueprint $table) {
            if (! Schema::hasColumn('pricedingins', 'KODEDGN')) {
                $table->string('KODEDGN', 20)->nullable()->after('id');
            }
            if (! Schema::hasColumn('pricedingins', 'KODE')) {
                $table->string('KODE', 20)->nullable()->after('KODEDGN');
            }
            if (! Schema::hasColumn('pricedingins', 'PERIODE')) {
                $table->string('PERIODE', 50)->nullable()->after('KODE');
            }
            if (! Schema::hasColumn('pricedingins', 'PLAT')) {
                $table->string('PLAT', 50)->nullable()->after('PERIODE');
            }
            if (! Schema::hasColumn('pricedingins', 'ITEM')) {
                $table->string('ITEM', 100)->nullable()->after('PLAT');
            }
            if (! Schema::hasColumn('pricedingins', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('ITEM');
            }
        });

        Schema::table('pricedingincus', function (Blueprint $table) {
            if (! Schema::hasColumn('pricedingincus', 'KODEDGN')) {
                $table->string('KODEDGN', 20)->nullable()->after('KODECUS');
            }
            if (! Schema::hasColumn('pricedingincus', 'KODE')) {
                $table->string('KODE', 20)->nullable()->after('KODEDGN');
            }
            if (! Schema::hasColumn('pricedingincus', 'PERIODE')) {
                $table->string('PERIODE', 50)->nullable()->after('KODE');
            }
            if (! Schema::hasColumn('pricedingincus', 'PLAT')) {
                $table->string('PLAT', 50)->nullable()->after('PERIODE');
            }
            if (! Schema::hasColumn('pricedingincus', 'ITEM')) {
                $table->string('ITEM', 100)->nullable()->after('PLAT');
            }
            if (! Schema::hasColumn('pricedingincus', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('ITEM');
            }
        });

        Schema::table('pricedinginhis', function (Blueprint $table) {
            if (! Schema::hasColumn('pricedinginhis', 'KODEDGN')) {
                $table->string('KODEDGN', 20)->nullable()->after('id');
            }
            if (! Schema::hasColumn('pricedinginhis', 'KODE')) {
                $table->string('KODE', 20)->nullable()->after('KODEDGN');
            }
            if (! Schema::hasColumn('pricedinginhis', 'PERIODE')) {
                $table->string('PERIODE', 50)->nullable()->after('KODE');
            }
            if (! Schema::hasColumn('pricedinginhis', 'PLAT')) {
                $table->string('PLAT', 50)->nullable()->after('PERIODE');
            }
            if (! Schema::hasColumn('pricedinginhis', 'ITEM')) {
                $table->string('ITEM', 100)->nullable()->after('PLAT');
            }
            if (! Schema::hasColumn('pricedinginhis', 'KUNCI')) {
                $table->string('KUNCI', 100)->nullable()->after('ITEM');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
