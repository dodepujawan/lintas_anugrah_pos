<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCus extends Model
{
    use HasFactory;

    protected $table = 'pricecus';

    protected $fillable = [
        'KODECUS', 'KODE', 'NAMACUS', 'FROM', 'TO',
        'HARGA', 'TANGGAL', 'DARI', 'SAMPAI', 'RUTE',
        'KETERANGAN', 'HV', 'HKG', 'HBOK',
        'KUNCI', 'HG', 'JENIS',
        'USER', 'USEREDIT', 'kode_index',
    ];

    protected $casts = [
        'TANGGAL' => 'date',
    ];
}
