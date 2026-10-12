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
    Schema::create('mcustomer', function (Blueprint $table) {
        $table->charset = 'latin1';
        $table->collation = 'latin1_general_ci';

        // PRIMARY KEY BARU LARAVEL
        $table->bigIncrements('id');

        // PRIMARY KEY LAMA FOXPRO
        $table->string('CUSTOMER', 30)->nullable()->unique();
        $table->string('kode_cus', 30)->nullable()->unique(); // alias modern

        // DATA UTAMA
        $table->string('NAMACUST', 100)->nullable();
        $table->string('ALAMAT1', 200)->nullable();
        $table->string('ALAMAT2', 200)->nullable();
        $table->string('KOTA', 100)->nullable();
        $table->string('TELEPON', 100)->nullable();
        $table->string('FAX', 100)->nullable();
        $table->string('EMAIL', 100)->nullable();
        $table->string('KONTAK', 100)->nullable();
        $table->string('NPWP', 100)->nullable();

        // WILAYAH & STATUS
        $table->string('AREA', 6)->nullable();
        $table->string('SUBAREA', 6)->nullable();
        $table->string('TYPECUST', 6)->nullable();
        $table->string('KOLEKTOR', 6)->nullable();
        $table->char('SETATUS', 1)->nullable();

        // KEUANGAN
        $table->decimal('SALDO', 10, 0)->nullable();
        $table->decimal('RETURAN', 10, 0)->nullable();
        $table->decimal('TOPKREDIT', 10, 0)->nullable();
        $table->decimal('MAXKREDIT', 10, 0)->nullable();

        // DISKON
        $table->decimal('DISC1', 10, 2)->nullable();
        $table->decimal('DISC2', 10, 2)->nullable();
        $table->decimal('DISC3', 10, 2)->nullable();
        $table->decimal('DISC_REG', 10, 2)->nullable();
        $table->decimal('DISC_CASH', 10, 0)->nullable();

        // INFO UPDATE
        $table->date('TGL_UPDATE')->nullable();
        $table->string('USERID', 20)->nullable();

        // DESA
        $table->string('desa', 100)->nullable();
        $table->string('camat', 100)->nullable();
        $table->string('kabupaten', 100)->nullable();

        // PURCHASING
        $table->string('namapur', 100)->nullable();
        $table->string('em_pur', 100)->nullable();
        $table->string('hp_pur', 100)->nullable();

        // STO (tambahan dari schema FoxPro)
        $table->string('nama_sto', 100)->nullable();
        $table->string('em_sto', 100)->nullable();
        $table->string('hp_sto', 100)->nullable();

        // PEMILIK
        $table->string('nama_p', 100)->nullable();
        $table->string('ktp_p', 100)->nullable();
        $table->string('tempat_l', 100)->nullable();
        $table->date('tgll_p')->nullable();
        $table->string('alamat_p', 100)->nullable();
        $table->string('desa_p', 100)->nullable();
        $table->string('camat_p', 100)->nullable();
        $table->string('kab_p', 100)->nullable();
        $table->string('tlp_p', 100)->nullable();
        $table->string('fax_p', 100)->nullable();
        $table->string('email_p', 100)->nullable();
        $table->string('npwp_p', 100)->nullable();
        $table->string('agama_p', 100)->nullable();

        // KONTAK LAIN
        $table->string('kontak_l', 100)->nullable();
        $table->string('tlp_kl', 100)->nullable();

        // ACCOUNTING
        $table->string('nama_ac', 100)->nullable();
        $table->string('em_ac', 100)->nullable();
        $table->string('hp_ac', 100)->nullable();

        // PAJAK
        $table->string('NM_PAJAK', 100)->nullable();
        $table->string('AL_PAJAK', 500)->nullable();
        $table->string('AL_PAJAK2', 500)->nullable();
        $table->string('NP_PAJAK', 100)->nullable();

        // DC
        $table->double('DC', 10, 0)->nullable();

        // Timestamp Laravel optional
        $table->nullableTimestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcustomer');
    }
};
