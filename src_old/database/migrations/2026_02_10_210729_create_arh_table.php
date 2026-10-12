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
        Schema::create('arh', function (Blueprint $table) {
            $table->id();

            $table->string('NOFAKTUR', 50)->unique();
            $table->date('TGLFAKTUR')->nullable();
            $table->date('TGLJT')->nullable();

            $table->string('CUSTOMER', 30);
            $table->string('SALES', 6)->nullable();
            $table->string('AREA', 6)->nullable();
            $table->string('DIVISI', 3)->nullable();

            $table->decimal('PIUTANG', 12, 2)->default(0);
            $table->decimal('BAYAR', 12, 2)->default(0);
            $table->decimal('RETUR', 12, 2)->default(0);
            $table->decimal('DISCOUNT', 12, 2)->default(0);
            $table->decimal('SALDO', 12, 2)->default(0);

            $table->string('CABANG', 2);
            $table->string('KETERANGAN', 100)->nullable();

            // Index
            // $table->index('CUSTOMER');
            // $table->index('NOFAKTUR');

            // User
            $table->string('USER', 30)->nullable();
            $table->string('USER_UPDATE', 30)->nullable();

            // Laravel timestamps
            $table->nullableTimestamps();

            // FoxPro / legacy compatibility
            $table->charset = 'latin1';
            $table->collation = 'latin1_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arh');
    }
};
