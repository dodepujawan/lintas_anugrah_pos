<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedinginhis extends Model
{
    use HasFactory;

    protected $table = 'pricedinginhis';

    protected $fillable = [
        'KODECUS', 'NAMACUS', 'FROM', 'TO', 'HARGA', 'TANGGAL',
        'USER', 'USEREDIT',
    ];
}
