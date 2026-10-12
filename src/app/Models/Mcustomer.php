<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mcustomer extends Model
{
    use HasFactory;
    protected $table = 'mcustomer';

    protected $fillable = [
        'CUSTOMER',
        'kode_cus',
        'NAMACUST',
        'ALAMAT1',
        'ALAMAT2',
        'KOTA',
        'TELEPON',
        'FAX',
        'EMAIL',
        'KONTAK',
        'NPWP',
        'AREA',
        'SUBAREA',
        'TYPECUST',
        'KOLEKTOR',
        'SETATUS',
        'SALDO',
        'RETURAN',
        'TOPKREDIT',
        'MAXKREDIT',
        'DISC1',
        'DISC2',
        'DISC3',
        'DISC_REG',
        'DISC_CASH',
        'TGL_UPDATE',
        'USERID',
        'desa',
        'camat',
        'kabupaten',
        'namapur',
        'em_pur',
        'hp_pur',
        'nama_sto',
        'em_sto',
        'hp_sto',
        'nama_p',
        'ktp_p',
        'tempat_l',
        'tgll_p',
        'alamat_p',
        'desa_p',
        'camat_p',
        'kab_p',
        'tlp_p',
        'fax_p',
        'email_p',
        'npwp_p',
        'agama_p',
        'kontak_l',
        'tlp_kl',
        'nama_ac',
        'em_ac',
        'hp_ac',
        'NM_PAJAK',
        'AL_PAJAK',
        'AL_PAJAK2',
        'NP_PAJAK',
        'DC',
        'KTP',
        'area_id',
        'area_name',
        'jurnal_id',
    ];

    protected $casts = [
        'TGL_UPDATE' => 'date',
        'tgll_p' => 'date',
        'SALDO' => 'decimal:2',
        'RETURAN' => 'decimal:2',
        'TOPKREDIT' => 'decimal:2',
        'MAXKREDIT' => 'decimal:2',
        'DISC1' => 'decimal:2',
        'DISC2' => 'decimal:2',
        'DISC3' => 'decimal:2',
        'DISC_REG' => 'decimal:2',
        'DISC_CASH' => 'decimal:2',
        'DC' => 'decimal:2',
        'KTP' => 'decimal:0',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function arhs(): HasMany
    {
        return $this->hasMany(Arh::class, 'CUSTOMER', 'CUSTOMER');
    }
}
