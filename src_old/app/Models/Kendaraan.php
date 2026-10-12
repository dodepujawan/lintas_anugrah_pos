<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    protected $fillable = [
        'KODE',
        'NAMA',
        'PLAT',
        'JENIS',
        'FNO_PRK_B',
        'FNO_PRK_P',
        'FNO_PRK_S',
        'FNO_PRK_O',
        'FNO_PRK_M',
    ];
}
