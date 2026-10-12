<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Msupplier extends Model
{
    protected $table = 'msupplier';

    protected $fillable = [
        'SUPPLIER',
        'KATEGORI',
        'NAMA',
        'ALAMAT1',
        'ALAMAT2',
        'KOTA',
        'TELEPON',
        'FAX',
        'EMAIL',
        'KONTAK',
        'NOREK',
        'BANK',
        'ATASNAMA',
        'SALDO',
        'RETURAN',
        'FTOP',
        'DISC_REG'
    ];

    public $timestamps = true;
}
