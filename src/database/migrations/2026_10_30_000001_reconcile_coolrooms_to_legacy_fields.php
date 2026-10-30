<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coolrooms')) {
            return;
        }

        Schema::table('coolrooms', function (Blueprint $table) {
            if (! Schema::hasColumn('coolrooms', 'CUSTOMER_KODE')) $table->char('CUSTOMER_KODE', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'TGL')) $table->date('TGL')->nullable();
            if (! Schema::hasColumn('coolrooms', 'TGLSJ')) $table->date('TGLSJ')->nullable();
            if (! Schema::hasColumn('coolrooms', 'TGLINVOICE')) $table->date('TGLINVOICE')->nullable();
            if (! Schema::hasColumn('coolrooms', 'TGLJT')) $table->date('TGLJT')->nullable();
            if (! Schema::hasColumn('coolrooms', 'INVOICE')) $table->char('INVOICE', 30)->nullable();
            if (! Schema::hasColumn('coolrooms', 'AKUN_PENDAPATAN')) $table->char('AKUN_PENDAPATAN', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NAMA_AKUN_PENDAPATAN')) $table->char('NAMA_AKUN_PENDAPATAN', 100)->nullable();
            if (! Schema::hasColumn('coolrooms', 'AKUN_PIUTANG')) $table->char('AKUN_PIUTANG', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NAMA_AKUN_PIUTANG')) $table->char('NAMA_AKUN_PIUTANG', 100)->nullable();
            if (! Schema::hasColumn('coolrooms', 'UNIT')) $table->char('UNIT', 10)->nullable();
            if (! Schema::hasColumn('coolrooms', 'BOXING')) $table->boolean('BOXING')->default(false);
            if (! Schema::hasColumn('coolrooms', 'SUBTOTAL')) $table->decimal('SUBTOTAL', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'DPP')) $table->decimal('DPP', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NPPN')) $table->decimal('NPPN', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'BAYAR')) $table->decimal('BAYAR', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'PIUTANG')) $table->decimal('PIUTANG', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'TOP')) $table->integer('TOP')->nullable();
            if (! Schema::hasColumn('coolrooms', 'KWT')) $table->char('KWT', 30)->nullable();
            if (! Schema::hasColumn('coolrooms', 'TGLKW')) $table->date('TGLKW')->nullable();
            if (! Schema::hasColumn('coolrooms', 'STS')) $table->char('STS', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'USERINPUT')) $table->char('USERINPUT', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'USERINV')) $table->char('USERINV', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'CABANG')) $table->char('CABANG', 5)->nullable();

            // Keep fields already used by the resource-style src controllers available
            // when this migration runs against a database created from the legacy schema.
            if (! Schema::hasColumn('coolrooms', 'TGLMUAT')) $table->date('TGLMUAT')->nullable();
            if (! Schema::hasColumn('coolrooms', 'NOMUAT')) $table->char('NOMUAT', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NAMACUST')) $table->string('NAMACUST', 100)->nullable();
            if (! Schema::hasColumn('coolrooms', 'KENDARAAN')) $table->char('KENDARAAN', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NAMA_KENDARAAN')) $table->char('NAMA_KENDARAAN', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'DRIVER')) $table->char('DRIVER', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NAMA_DRIVER')) $table->char('NAMA_DRIVER', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'NDISCAW')) $table->decimal('NDISCAW', 12, 0)->nullable();
            if (! Schema::hasColumn('coolrooms', 'STATUS')) $table->char('STATUS', 20)->nullable();
            if (! Schema::hasColumn('coolrooms', 'user')) $table->char('user', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'USEREDIT')) $table->char('USEREDIT', 50)->nullable();
            if (! Schema::hasColumn('coolrooms', 'area_id')) $table->unsignedBigInteger('area_id')->nullable();
            if (! Schema::hasColumn('coolrooms', 'area_name')) $table->string('area_name')->nullable();
            if (! Schema::hasColumn('coolrooms', 'jurnal_id')) $table->unsignedBigInteger('jurnal_id')->nullable();
        });

        if (Schema::hasColumn('coolrooms', 'TGLSJ') && Schema::hasColumn('coolrooms', 'TGLMUAT')) {
            DB::table('coolrooms')->whereNull('TGLSJ')->whereNotNull('TGLMUAT')->update(['TGLSJ' => DB::raw('TGLMUAT')]);
            DB::table('coolrooms')->whereNull('TGLMUAT')->whereNotNull('TGLSJ')->update(['TGLMUAT' => DB::raw('TGLSJ')]);
        }

        if (Schema::hasColumn('coolrooms', 'TGL') && Schema::hasColumn('coolrooms', 'TGLSJ')) {
            DB::table('coolrooms')->whereNull('TGL')->whereNotNull('TGLSJ')->update(['TGL' => DB::raw('TGLSJ')]);
        }

        if (DB::getDriverName() === 'mysql') {
            Schema::table('coolrooms', function (Blueprint $table) {
                if (Schema::hasColumn('coolrooms', 'NOSJ')) $table->char('NOSJ', 30)->nullable()->change();
                if (Schema::hasColumn('coolrooms', 'JUMLAH')) $table->decimal('JUMLAH', 12, 3)->nullable()->change();
                if (Schema::hasColumn('coolrooms', 'KETERANGAN')) $table->text('KETERANGAN')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // This migration reconciles two different deployed schemas; dropping fields
        // during rollback could destroy legacy transaction and invoice data.
    }
};
