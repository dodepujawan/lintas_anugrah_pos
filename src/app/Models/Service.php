<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'TGL_SERVICE', 'KENDARAAN', 'NAMA_KENDARAAN', 'NOSJ', 'NOBAYAR',
        'HARGA', 'JUMLAH', 'KETERANGAN', 'REK_BIAYA', 'tipe',
        'USER', 'USEREDIT', 'NO_JURNAL', 'TGL_TEMP', 'TGLJT',
        'area_id', 'area_name', 'NOJURNAL', 'jurnal_id',
    ];

    protected $casts = [
        'TGL_SERVICE' => 'date',
        'TGL_TEMP' => 'date',
        'TGLJT' => 'date',
        'HARGA' => 'decimal:0',
        'JUMLAH' => 'decimal:0',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];
}
