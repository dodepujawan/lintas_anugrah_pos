<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expedisis')) {
            return;
        }

        Schema::table('expedisis', function (Blueprint $table) {
            if (! Schema::hasColumn('expedisis', 'barang')) {
                $table->string('barang', 100)->nullable();
            }
            if (! Schema::hasColumn('expedisis', 'penyimpanan')) {
                $table->string('penyimpanan', 1)->nullable();
            }
            if (! Schema::hasColumn('expedisis', 'koli')) {
                $table->decimal('koli', 15, 3)->nullable();
            }
            if (! Schema::hasColumn('expedisis', 'catatan')) {
                $table->text('catatan')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expedisis')) {
            return;
        }

        Schema::table('expedisis', function (Blueprint $table) {
            foreach (['barang', 'penyimpanan', 'koli', 'catatan'] as $column) {
                if (Schema::hasColumn('expedisis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
