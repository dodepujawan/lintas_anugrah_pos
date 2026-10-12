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
        Schema::create('aph', function (Blueprint $table) {
            // Laravel standard primary key
            $table->id();

            // FoxPro legacy columns
            $table->char('NOFAKTUR', 50)->nullable();
            $table->date('TGLFAKTUR')->nullable();
            $table->date('TGLJT')->nullable();

            $table->char('SUPPLIER', 6)->nullable()->default('');

            $table->decimal('HUTANG', 10, 0)->nullable()->default(0);
            $table->decimal('UM', 10, 0)->nullable()->default(0);
            $table->decimal('BAYAR', 10, 0)->nullable()->default(0);
            $table->decimal('RETUR', 10, 0)->nullable()->default(0);
            $table->decimal('DISCOUNT', 10, 0)->nullable()->default(0);
            $table->decimal('SALDO', 10, 0)->nullable()->default(0);

            $table->char('KETERANGAN', 100)->nullable()->default('');

            $table->decimal('AUTO', 1, 0)->nullable()->default(0);

            // Optional timestamps
            $table->nullableTimestamps();

            // Charset & collation
            $table->charset = 'latin1';
            $table->collation = 'latin1_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aph');
    }
};
