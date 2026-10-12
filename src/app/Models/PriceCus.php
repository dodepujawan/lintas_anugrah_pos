<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCus extends Model
{
    use HasFactory;

    protected $table = 'pricecus';

    protected $fillable = [
        'KODECUS', 'NAMACUS', 'FROM', 'TO', 'HARGA', 'TANGGAL',
        'USER', 'USEREDIT', 'kode_index',
    ];
}
