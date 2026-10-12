<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ARH — merge src_old kolom (SALES, AREA, DIVISI) + ledger integration
        Schema::table('arh', function (Blueprint $table) {
            if (! Schema::hasColumn('arh', 'SALES')) {
                $table->string('SALES', 50)->nullable()->after('CUSTOMER');
            }
            if (! Schema::hasColumn('arh', 'AREA')) {
                $table->string('AREA', 6)->nullable()->after('SALES');
            }
            if (! Schema::hasColumn('arh', 'DIVISI')) {
                $table->string('DIVISI', 3)->nullable()->after('AREA');
            }
            if (! Schema::hasColumn('arh', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('DIVISI');
            }
            if (! Schema::hasColumn('arh', 'area_name')) {
                $table->string('area_name')->nullable()->after('area_id');
            }
            if (! Schema::hasColumn('arh', 'jurnal_id')) {
                $table->unsignedBigInteger('jurnal_id')->nullable()->after('NOJURNAL');
            }
        });

        // APH — merge src_old kolom (UM, AUTO) + ledger integration
        Schema::table('aph', function (Blueprint $table) {
            if (! Schema::hasColumn('aph', 'UM')) {
                $table->decimal('UM', 15, 2)->default(0)->after('HUTANG');
            }
            if (! Schema::hasColumn('aph', 'AUTO')) {
                $table->decimal('AUTO', 1, 0)->default(0)->after('KETERANGAN');
            }
            if (! Schema::hasColumn('aph', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('SUPPLIER');
            }
            if (! Schema::hasColumn('aph', 'area_name')) {
                $table->string('area_name')->nullable()->after('area_id');
            }
            if (! Schema::hasColumn('aph', 'jurnal_id')) {
                $table->unsignedBigInteger('jurnal_id')->nullable()->after('NOJURNAL');
            }
        });

        // BARH — tambah area columns
        Schema::table('barh', function (Blueprint $table) {
            if (! Schema::hasColumn('barh', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('CABANG');
            }
            if (! Schema::hasColumn('barh', 'area_name')) {
                $table->string('area_name')->nullable()->after('area_id');
            }
        });

        // BAPH — tambah area columns
        Schema::table('baph', function (Blueprint $table) {
            if (! Schema::hasColumn('baph', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('KETERANGAN');
            }
            if (! Schema::hasColumn('baph', 'area_name')) {
                $table->string('area_name')->nullable()->after('area_id');
            }
        });
    }

    public function down(): void
    {
        foreach (['arh', 'aph', 'barh', 'baph'] as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $dropCols = [];
                foreach (['area_id', 'area_name'] as $col) {
                    if (Schema::hasColumn($tbl, $col)) {
                        $dropCols[] = $col;
                    }
                }
                if ($tbl === 'arh' && Schema::hasColumn($tbl, 'SALES')) $dropCols[] = 'SALES';
                if ($tbl === 'arh' && Schema::hasColumn($tbl, 'AREA')) $dropCols[] = 'AREA';
                if ($tbl === 'arh' && Schema::hasColumn($tbl, 'DIVISI')) $dropCols[] = 'DIVISI';
                if ($tbl === 'arh' && Schema::hasColumn($tbl, 'jurnal_id')) $dropCols[] = 'jurnal_id';
                if ($tbl === 'aph' && Schema::hasColumn($tbl, 'UM')) $dropCols[] = 'UM';
                if ($tbl === 'aph' && Schema::hasColumn($tbl, 'AUTO')) $dropCols[] = 'AUTO';
                if ($tbl === 'aph' && Schema::hasColumn($tbl, 'jurnal_id')) $dropCols[] = 'jurnal_id';
                if (! empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }
    }
};
