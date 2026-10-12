<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bard', function (Blueprint $table) {
            $table->decimal('BANK_ADMIN', 15, 2)->default(0)->after('DISCOUNT');
        });

        Schema::table('barh', function (Blueprint $table) {
            $table->foreignId('BANK_ADMIN_COA_ID')->nullable()->after('DISCOUNT_COA_ID')->constrained('coas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('barh', function (Blueprint $table) {
            $table->dropConstrainedForeignId('BANK_ADMIN_COA_ID');
        });

        Schema::table('bard', function (Blueprint $table) {
            $table->dropColumn('BANK_ADMIN');
        });
    }
};
