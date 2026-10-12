<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_items', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->foreign('coa_id')->references('id')->on('coas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_items', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->foreign('coa_id')->references('id')->on('coasold')->restrictOnDelete();
        });
    }
};
