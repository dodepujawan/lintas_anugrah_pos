<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HANYA pricecus — bagian ini SUDAH DIJALANKAN oleh user
        // pricecushis + pricedingin ada di migration 103000 + 103500
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
    }

    public function down(): void
    {
        //
    }
};
