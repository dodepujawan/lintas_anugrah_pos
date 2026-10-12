<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'level')) {
                $table->enum('level', ['Header', 'Sub Header', 'Kelompok Akun', 'Akun Posting'])
                    ->nullable()
                    ->after('kategori');
            }

            if (!Schema::hasColumn('coas', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->constrained('coas')
                    ->nullOnDelete()
                    ->after('level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'level']);
        });
    }
};
