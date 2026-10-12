<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mcustomer extends Model
{
    use HasFactory;

    protected $table = 'mcustomer';

    protected $fillable = [
        'CUSTOMER', 'kode_cus', 'NAMACUST', 'ALAMAT1', 'ALAMAT2', 'KOTA',
        'TELEPON', 'FAX', 'EMAIL', 'KONTAK', 'NPWP',
        'AREA', 'SUBAREA', 'TYPECUST', 'KOLEKTOR', 'SETATUS',
        'SALDO', 'RETURAN', 'TOPKREDIT', 'MAXKREDIT',
        'DISC1', 'DISC2', 'DISC3', 'DISC_REG', 'DISC_CASH',
        'TGL_UPDATE', 'USERID',
        'desa', 'camat', 'kabupaten',
        'namapur', 'em_pur', 'hp_pur',
        'nama_sto', 'em_sto', 'hp_sto',
        'nama_p', 'ktp_p', 'tempat_l', 'tgll_p', 'alamat_p', 'desa_p', 'camat_p', 'kab_p', 'tlp_p', 'fax_p', 'email_p', 'npwp_p', 'agama_p',
        'kontak_l', 'tlp_kl',
        'nama_ac', 'em_ac', 'hp_ac',
        'NM_PAJAK', 'AL_PAJAK', 'AL_PAJAK2', 'NP_PAJAK',
        'DC'
    ];
    protected $casts = [
        'TGL_UPDATE' => 'date',
    ];
}
