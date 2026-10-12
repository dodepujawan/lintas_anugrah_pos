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
        Schema::create('expedisi', function (Blueprint $table) {
            // Primary key (WAJIB)
            $table->id();

            // ======================
            // IDENTITAS & DOKUMEN
            // ======================
            $table->char('NOMUAT', 20)->nullable();
            $table->date('TGLMUAT')->nullable();
            $table->date('TGLPO')->nullable();
            $table->char('NOPO', 20)->nullable();
            $table->char('NOSJ', 20)->nullable();
            $table->char('NOJALAN', 20)->nullable();
            $table->date('tglsj')->nullable();
            $table->char('KET_SJ', 100)->nullable();
            $table->date('TGLINVOICE')->nullable();
            $table->char('INVOICE', 20)->nullable();
            $table->char('CUSTOMER_KODE', 20)->nullable();
            $table->char('CUSTOMER', 30)->nullable();

            // ======================
            // KENDARAAN & DRIVER
            // ======================
            $table->char('KENDARAAN', 20)->nullable();
            $table->char('NAMA_KENDARAAN', 50)->nullable();

            $table->char('DRIVER', 20)->nullable();
            $table->char('NAMA_DRIVER', 50)->nullable();
            $table->char('KET_DRIVER', 100)->nullable();

            $table->char('DRIVER2', 20)->nullable();
            $table->char('NAMA_DRIVER2', 50)->nullable();
            $table->char('KET_DRIVER2', 100)->nullable();

            // ======================
            // BIAYA & PESANAN
            // ======================
            $table->decimal('UT', 12, 0)->nullable();
            $table->char('KET_UT', 100)->nullable();

            $table->char('PESANAN', 100)->nullable();
            $table->char('PESANANGB', 100)->nullable();
            $table->char('GB', 15)->nullable();

            $table->char('rute', 30)->nullable();
            $table->char('RUTE2', 30)->nullable();

            $table->decimal('JUMLAH', 12, 3)->nullable();
            $table->char('UNIT', 10)->nullable();

            $table->decimal('HARGA', 12, 0)->nullable();
            $table->decimal('hargaaw', 12, 0)->nullable();

            $table->char('JENISHRG', 1)->nullable();
            $table->char('VOL', 3)->nullable();
            $table->char('type', 1)->nullable();

            // ======================
            // DISKON & TOTAL
            // ======================
            $table->decimal('DISC', 5, 2)->nullable();
            $table->decimal('NDISC', 12, 0)->nullable();
            $table->decimal('NDISCAW', 12, 0)->nullable();

            $table->decimal('TOTAL', 12, 0)->nullable();
            $table->decimal('PPN', 5, 2)->nullable();
            $table->decimal('GRAND', 12, 0)->nullable();

            // ======================
            // DC & PEMBAYARAN
            // ======================
            $table->double('KODEDC', 12, 0)->nullable();
            $table->double('DC', 12, 0)->nullable();
            $table->double('DCAW', 12, 0)->nullable();

            $table->double('BAYAR', 12, 0)->nullable();
            $table->double('PIUTANG', 12, 0)->nullable();

            $table->double('claim', 12, 0)->nullable();
            $table->string('KETCLAIM', 100)->nullable();

            $table->double('TOP', 12, 0)->nullable();
            $table->date('TGLJT')->nullable();

            // ======================
            // STATUS & USER
            // ======================
            $table->char('SIMPAN', 6)->nullable();
            $table->double('SUHU', 12, 2)->nullable();

            $table->char('kunci', 100)->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->char('STS', 20)->nullable();
            $table->char('JENIS', 3)->nullable();

            $table->char('user', 50)->nullable();
            $table->char('USEREDIT', 50)->nullable();
            $table->char('USERINV', 50)->nullable();
            $table->char('USERKENDARAAN', 50)->nullable();

            // ======================
            // KEUANGAN & PENERIMA
            // ======================
            $table->double('REFUND', 12, 0)->nullable();

            $table->string('PENERIMA', 100)->nullable();
            $table->char('BANK', 50)->nullable();
            $table->char('NOREK', 50)->nullable();

            $table->char('CT', 1)->nullable();

            $table->char('P_PENERIMA', 100)->nullable();
            $table->char('P_ALAMAT', 100)->nullable();
            $table->char('P_NAMA', 50)->nullable();
            $table->char('P_PHONE', 50)->nullable();

            // ======================
            // CLOSING & CABANG
            // ======================
            $table->char('READY', 1)->nullable();
            $table->char('CLOSSING', 1)->nullable();
            $table->date('TGLCLOSSING')->nullable();
            $table->char('USERCLOSSING', 50)->nullable();

            $table->char('kwt', 30)->nullable();
            $table->date('TGLKW')->nullable();

            $table->char('CABANG', 2)->nullable();
            $table->decimal('POTONGAN', 12, 0)->nullable();
            $table->char('WILAYAH', 10)->nullable();

            // ======================
            // JURNAL & AKUNTANSI
            // ======================
            $table->char('REK_BIAYA', 5)->nullable();
            $table->decimal('BIAYA', 12, 0)->nullable();

            $table->char('FNO_PRKB', 5)->nullable();
            $table->char('FJUR_TRANSB', 13)->nullable();
            $table->char('NO_BKK', 22)->nullable();
            $table->char('JURNAL', 18)->nullable();

            // ======================
            // TIMESTAMPS
            // ======================
            $table->nullableTimestamps();

            // ======================
            // CHARSET
            // ======================
            $table->charset = 'latin1';
            $table->collation = 'latin1_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedisi');
    }
};
