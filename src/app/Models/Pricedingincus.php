<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedingincus extends Model
{
    use HasFactory;

    protected $table = 'pricedingincus';

    protected $fillable = [
        'KODECUS', 'NAMACUS', 'KODEDGN', 'KODE',
        'TANGGAL', 'PERIODE', 'PLAT', 'JENIS', 'ITEM',
        'HARGA', 'KUNCI', 'USER', 'USEREDIT',
        'FROM', 'TO',
    ];
}
