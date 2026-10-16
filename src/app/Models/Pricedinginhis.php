<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedinginhis extends Model
{
    use HasFactory;

    protected $table = 'pricedinginhis';

    protected $fillable = [
        'KODEDGN', 'KODE',
        'TANGGAL', 'PERIODE', 'PLAT', 'JENIS', 'ITEM',
        'HARGA', 'KUNCI', 'USER', 'USEREDIT',
    ];

    protected $casts = [
        'TANGGAL' => 'date',
    ];
}
