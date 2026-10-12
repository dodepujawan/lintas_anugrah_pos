<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) {
            return;
        }

        $missingColumns = [
            'status_jurnal' => ! Schema::hasColumn('t_penjualan_hosting', 'status_jurnal'),
            'no_jurnal' => ! Schema::hasColumn('t_penjualan_hosting', 'no_jurnal'),
            'jurnal_id' => ! Schema::hasColumn('t_penjualan_hosting', 'jurnal_id'),
            'posted_at' => ! Schema::hasColumn('t_penjualan_hosting', 'posted_at'),
            'posted_by' => ! Schema::hasColumn('t_penjualan_hosting', 'posted_by'),
            'posting_error' => ! Schema::hasColumn('t_penjualan_hosting', 'posting_error'),
        ];

        Schema::table('t_penjualan_hosting', function (Blueprint $table) use ($missingColumns): void {
            if ($missingColumns['status_jurnal']) {
                $table->string('status_jurnal', 30)->default('BELUM DIPROSES')->index();
            }
            if ($missingColumns['no_jurnal']) {
                $table->string('no_jurnal', 50)->nullable()->index();
            }
            if ($missingColumns['jurnal_id']) {
                $table->unsignedBigInteger('jurnal_id')->nullable()->index();
            }
            if ($missingColumns['posted_at']) {
                $table->timestamp('posted_at')->nullable();
            }
            if ($missingColumns['posted_by']) {
                $table->unsignedBigInteger('posted_by')->nullable();
            }
            if ($missingColumns['posting_error']) {
                $table->text('posting_error')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Kolom mungkin berasal dari migration lama; jangan hapus saat rollback repair.
    }
};
