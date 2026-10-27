<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Samakan kolom tabel kwitansi dengan skema lama (src_old).
     * Idempotent: aman dijalankan di DB yang sudah memakai kolom baru
     * maupun DB fresh yang langsung memakai kolom lama.
     */
    public function up(): void
    {
        if (! Schema::hasTable('kwitansi')) {
            return;
        }

        // ---------- Rename: kolom baru -> nama lama (data dipertahankan) ----------
        $renames = [
            'NO_KWT' => 'NOKWT',
            'TGL_KWT' => 'TGL',
            'NOFAKTUR' => 'FDOK_TRANS',
            'SALDO_FAKTUR' => 'FNIL_DOK',
            'NAMACUST' => 'FNAMA',
            'KETERANGAN' => 'FKETERANG',
            'JENIS_KWT' => 'JENIS',
            'DISCOUNT' => 'NDISC',
            'user' => 'USERINPUT',
        ];
        Schema::table('kwitansi', function (Blueprint $table) use ($renames) {
            foreach ($renames as $from => $to) {
                if (Schema::hasColumn('kwitansi', $from) && ! Schema::hasColumn('kwitansi', $to)) {
                    $table->renameColumn($from, $to);
                }
            }
        });

        // ---------- Tambah kolom lama yang belum ada ----------
        Schema::table('kwitansi', function (Blueprint $table) {
            if (! Schema::hasColumn('kwitansi', 'TGL_TRANS')) {
                $table->date('TGL_TRANS')->nullable()->after('FDOK_TRANS');
            }
            if (! Schema::hasColumn('kwitansi', 'NOSJ')) {
                $table->char('NOSJ', 30)->nullable()->after('CUSTOMER');
            }
            if (! Schema::hasColumn('kwitansi', 'FNIL_DOK')) {
                $table->decimal('FNIL_DOK', 12, 0)->nullable()->after('FNAMA');
            }
            if (! Schema::hasColumn('kwitansi', 'TOTAL')) {
                $table->decimal('TOTAL', 12, 0)->nullable()->after('FNIL_DOK');
            }
            if (! Schema::hasColumn('kwitansi', 'PPN')) {
                $table->decimal('PPN', 5, 2)->nullable()->after('TOTAL');
            }
            if (! Schema::hasColumn('kwitansi', 'DISC')) {
                $table->decimal('DISC', 5, 2)->nullable()->after('PPN');
            }
            if (! Schema::hasColumn('kwitansi', 'NDISC')) {
                $table->decimal('NDISC', 12, 0)->nullable()->after('DISC');
            }
        });

        // ---------- Hapus kolom tambahan yang tidak ada di skema lama ----------
        $drop = ['NILAI_BAYAR', 'RETUR', 'SISA_FAKTUR', 'NOBAYAR', 'USEREDIT', 'area_id', 'area_name', 'NOJURNAL', 'jurnal_id'];
        Schema::table('kwitansi', function (Blueprint $table) use ($drop) {
            foreach ($drop as $column) {
                if (Schema::hasColumn('kwitansi', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        // Tidak ada rollback otomatis; perubahan dilakukan via rename/tambah/hapus kolom.
    }
};
