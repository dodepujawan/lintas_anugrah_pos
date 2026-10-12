<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcustomer', function (Blueprint $table) {
            $table->string('kode_cus', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('mcustomer')->whereNull('kode_cus')->update([
            'kode_cus' => DB::raw('CUSTOMER'),
        ]);

        Schema::table('mcustomer', function (Blueprint $table) {
            $table->string('kode_cus', 30)->nullable(false)->change();
        });
    }
};
