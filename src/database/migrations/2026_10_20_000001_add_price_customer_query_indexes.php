<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PRICECUS_INDEX = 'pricecus_kodecus_dari_sampai_id_index';

    public function up(): void
    {
        if (! Schema::hasTable('pricecus')) {
            return;
        }

        if (! Schema::hasIndex('pricecus', self::PRICECUS_INDEX)) {
            Schema::table('pricecus', function (Blueprint $table): void {
                $table->index(
                    ['KODECUS', 'DARI', 'SAMPAI', 'id'],
                    self::PRICECUS_INDEX
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('pricecus')
            && Schema::hasIndex('pricecus', self::PRICECUS_INDEX)
        ) {
            Schema::table('pricecus', function (Blueprint $table): void {
                $table->dropIndex(self::PRICECUS_INDEX);
            });
        }
    }
};
