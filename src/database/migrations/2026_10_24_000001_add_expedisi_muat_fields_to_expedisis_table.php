<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'PLAT_NOMOR' => ['string', 30],
        'KM_AWAL' => ['decimal', 12, 2],
        'KM_AKHIR' => ['decimal', 12, 2],
        'UANG_JALAN' => ['decimal', 15, 2],
        'UANG_DRIVER_MAKAN' => ['decimal', 15, 2],
        'UANG_LAIN_LAIN' => ['decimal', 15, 2],
        'PENGIRIM' => ['string', 100],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('expedisis')) {
            return;
        }

        Schema::table('expedisis', function (Blueprint $table) {
            foreach ($this->columns as $column => $definition) {
                if (Schema::hasColumn('expedisis', $column)) {
                    continue;
                }

                if ($definition[0] === 'decimal') {
                    $table->decimal($column, $definition[1], $definition[2])->nullable();
                } else {
                    $table->string($column, $definition[1])->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expedisis')) {
            return;
        }

        Schema::table('expedisis', function (Blueprint $table) {
            foreach (array_keys($this->columns) as $column) {
                if (Schema::hasColumn('expedisis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
