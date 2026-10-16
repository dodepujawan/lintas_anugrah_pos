<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing legacy columns to pricedingins
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

        // Add missing legacy columns to pricedingincus
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

        // Add missing legacy columns to pricedinginhis
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
