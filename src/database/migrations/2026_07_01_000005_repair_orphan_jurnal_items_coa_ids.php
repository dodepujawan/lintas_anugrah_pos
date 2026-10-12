<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $cashParentId = DB::table('coas')->where('id', 3)->exists() ? 3 : null;
        $bankParentId = DB::table('coas')->where('id', 7)->exists() ? 7 : null;

        if (DB::table('coas')->where('id', 113)->doesntExist()) {
            DB::table('coas')->insert([
                'id' => 113,
                'kode_akun' => '11000000',
                'nama_akun' => 'Kas',
                'level' => 'Akun Posting',
                'parent_id' => $cashParentId,
                'tipe' => 'Aset',
                'kategori' => null,
                'saldo_normal' => 'Debit',
                'deskripsi' => 'Imported legacy cash account',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (DB::table('coas')->where('id', 114)->doesntExist()) {
            DB::table('coas')->insert([
                'id' => 114,
                'kode_akun' => '11010000',
                'nama_akun' => 'Bank',
                'level' => 'Akun Posting',
                'parent_id' => $bankParentId,
                'tipe' => 'Aset',
                'kategori' => null,
                'saldo_normal' => 'Debit',
                'deskripsi' => 'Imported legacy bank account',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            $constraintExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jurnal_items' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'jurnal_items_coa_id_foreign'");

            if (!empty($constraintExists)) {
                Schema::table('jurnal_items', function (Blueprint $table) {
                    $table->dropForeign(['coa_id']);
                });
            }

            Schema::table('jurnal_items', function (Blueprint $table) {
                $table->foreign('coa_id')->references('id')->on('coas')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('jurnal_items', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->foreign('coa_id')->references('id')->on('coasold')->restrictOnDelete();
        });

        DB::table('coas')->whereIn('id', [113, 114])->delete();
    }
};
