<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCushis extends Model
{
    use HasFactory;

    protected $table = 'pricecushis';

    protected $fillable = [
        'KODECUS', 'NAMACUS', 'FROM', 'TO', 'HARGA', 'TANGGAL',
        'USER', 'USEREDIT',
    ];
}
