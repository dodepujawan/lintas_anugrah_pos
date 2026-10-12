<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bard', function (Blueprint $table) {
            $table->decimal('PPH23', 15, 2)->default(0)->after('NILAI_BAYAR');
        });

        Schema::table('barh', function (Blueprint $table) {
            $table->foreignId('PPH23_COA_ID')->nullable()->after('PAYMENT_COA_ID')->constrained('coas')->nullOnDelete();
            $table->foreignId('DISCOUNT_COA_ID')->nullable()->after('PPH23_COA_ID')->constrained('coas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('barh', function (Blueprint $table) {
            $table->dropConstrainedForeignId('DISCOUNT_COA_ID');
            $table->dropConstrainedForeignId('PPH23_COA_ID');
        });

        Schema::table('bard', function (Blueprint $table) {
            $table->dropColumn('PPH23');
        });
    }
};
