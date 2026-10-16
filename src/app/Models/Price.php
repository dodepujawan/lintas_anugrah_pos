<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $table = 'prices';

    protected $fillable = [
        'KODE', 'FROM', 'TO', 'HARGA', 'TANGGAL',
        'DARI', 'SAMPAI', 'RUTE', 'KETERANGAN',
        'HV', 'HKG', 'HBOK', 'KUNCI', 'HG', 'JENIS',
        'USER', 'USEREDIT',
    ];

    protected $casts = [
        'TANGGAL' => 'date',
    ];
}
