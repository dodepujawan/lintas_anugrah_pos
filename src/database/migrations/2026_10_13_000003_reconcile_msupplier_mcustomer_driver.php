<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MSUPPLIER — src uses SUPPLIER as PK (string, non-incrementing)
        // src_old uses id (auto-increment) as PK, SUPPLIER as char(8)
        // Reconciliation: keep src style (SUPPLIER as PK), add kolom src_old yang hilang
        if (Schema::hasTable('msupplier')) {
            if (! Schema::hasColumn('msupplier', 'KATEGORI')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->char('KATEGORI', 4)->nullable()->after('SUPPLIER');
                });
            }
            if (! Schema::hasColumn('msupplier', 'RETURAN')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->decimal('RETURAN', 15, 2)->default(0)->after('SALDO');
                });
            }
            if (! Schema::hasColumn('msupplier', 'area_id')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->unsignedBigInteger('area_id')->nullable()->after('DISC_REG');
                });
            }
            if (! Schema::hasColumn('msupplier', 'area_name')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->string('area_name')->nullable()->after('area_id');
                });
            }
            if (! Schema::hasColumn('msupplier', 'jurnal_id')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->unsignedBigInteger('jurnal_id')->nullable()->after('area_name');
                });
            }
            if (! Schema::hasColumn('msupplier', 'created_at')) {
                Schema::table('msupplier', function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }

        // MCUSTOMER — src has fewer columns, src_old has FoxPro columns
        // Reconciliation: tambah kolom FoxPro yang hilang ke src schema
        if (Schema::hasTable('mcustomer')) {
            $foxproColumns = [
                'desa'      => ['type' => 'string', 'length' => 100],
                'camat'     => ['type' => 'string', 'length' => 100],
                'kabupaten' => ['type' => 'string', 'length' => 100],
                'namapur'   => ['type' => 'string', 'length' => 100],
                'em_pur'    => ['type' => 'string', 'length' => 100],
                'hp_pur'    => ['type' => 'string', 'length' => 100],
                'nama_sto'  => ['type' => 'string', 'length' => 100],
                'em_sto'    => ['type' => 'string', 'length' => 100],
                'hp_sto'    => ['type' => 'string', 'length' => 100],
                'nama_p'    => ['type' => 'string', 'length' => 100],
                'ktp_p'     => ['type' => 'string', 'length' => 100],
                'tempat_l'  => ['type' => 'string', 'length' => 100],
                'tgll_p'    => ['type' => 'date', 'length' => null],
                'alamat_p'  => ['type' => 'string', 'length' => 100],
                'desa_p'    => ['type' => 'string', 'length' => 100],
                'camat_p'   => ['type' => 'string', 'length' => 100],
                'kab_p'     => ['type' => 'string', 'length' => 100],
                'tlp_p'     => ['type' => 'string', 'length' => 100],
                'fax_p'     => ['type' => 'string', 'length' => 100],
                'email_p'   => ['type' => 'string', 'length' => 100],
                'npwp_p'    => ['type' => 'string', 'length' => 100],
                'agama_p'   => ['type' => 'string', 'length' => 100],
                'kontak_l'  => ['type' => 'string', 'length' => 100],
                'tlp_kl'    => ['type' => 'string', 'length' => 100],
                'nama_ac'   => ['type' => 'string', 'length' => 100],
                'em_ac'     => ['type' => 'string', 'length' => 100],
                'hp_ac'     => ['type' => 'string', 'length' => 100],
                'NM_PAJAK'  => ['type' => 'string', 'length' => 100],
                'AL_PAJAK'  => ['type' => 'string', 'length' => 500],
                'AL_PAJAK2' => ['type' => 'string', 'length' => 500],
                'NP_PAJAK'  => ['type' => 'string', 'length' => 100],
                'DC'        => ['type' => 'double', 'length' => null],
                'KTP'       => ['type' => 'decimal', 'length' => '5,0'],
            ];

            foreach ($foxproColumns as $col => $def) {
                if (Schema::hasColumn('mcustomer', $col)) {
                    continue;
                }

                Schema::table('mcustomer', function (Blueprint $table) use ($col, $def) {
                    switch ($def['type']) {
                        case 'string':
                            $table->string($col, $def['length'] ?? 100)->nullable();
                            break;
                        case 'date':
                            $table->date($col)->nullable();
                            break;
                        case 'double':
                            $table->double($col, 12, 0)->nullable();
                            break;
                        case 'decimal':
                            $table->decimal($col, (int) explode(',', $def['length'])[0], (int) explode(',', $def['length'])[1])->default(0);
                            break;
                    }
                });
            }
        }

        // DRIVER → DRIVERS: src uses 'drivers' (plural), src_old uses 'driver' (singular)
        // src_old columns: KODE, NAMA, ALAMAT, PHONE, MULAI, user_id
        // src columns: code, name, phone, address, is_active
        // Reconciliation: add UPPERCASE alias kolom untuk src_old compatibility
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (! Schema::hasColumn('drivers', 'KODE')) {
                    $table->string('KODE', 30)->nullable()->after('code');
                }
                if (! Schema::hasColumn('drivers', 'NAMA')) {
                    $table->string('NAMA', 100)->nullable()->after('name');
                }
                if (! Schema::hasColumn('drivers', 'ALAMAT')) {
                    $table->string('ALAMAT', 100)->nullable()->after('address');
                }
                if (! Schema::hasColumn('drivers', 'MULAI')) {
                    $table->date('MULAI')->nullable()->after('ALAMAT');
                }
                if (! Schema::hasColumn('drivers', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('msupplier')) {
            Schema::table('msupplier', function (Blueprint $table) {
                $cols = [];
                foreach (['KATEGORI', 'RETURAN', 'area_id', 'area_name', 'jurnal_id'] as $c) {
                    if (Schema::hasColumn('msupplier', $c)) $cols[] = $c;
                }
                if (! empty($cols)) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('mcustomer')) {
            Schema::table('mcustomer', function (Blueprint $table) {
                $cols = [];
                foreach (['desa', 'camat', 'kabupaten', 'namapur', 'em_pur', 'hp_pur',
                    'nama_sto', 'em_sto', 'hp_sto', 'nama_p', 'ktp_p', 'tempat_l',
                    'tgll_p', 'alamat_p', 'desa_p', 'camat_p', 'kab_p', 'tlp_p',
                    'fax_p', 'email_p', 'npwp_p', 'agama_p', 'kontak_l', 'tlp_kl',
                    'nama_ac', 'em_ac', 'hp_ac', 'NM_PAJAK', 'AL_PAJAK', 'AL_PAJAK2',
                    'NP_PAJAK', 'DC', 'KTP'] as $c) {
                    if (Schema::hasColumn('mcustomer', $c)) $cols[] = $c;
                }
                if (! empty($cols)) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                $cols = ['KODE', 'NAMA', 'ALAMAT', 'MULAI', 'user_id'];
                $cols = array_filter($cols, fn($c) => Schema::hasColumn('drivers', $c));
                if (! empty($cols)) $table->dropColumn($cols);
            });
        }
    }
};
