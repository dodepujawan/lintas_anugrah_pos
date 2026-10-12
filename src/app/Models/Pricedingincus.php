<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedingincus extends Model
{
    use HasFactory;

    protected $table = 'pricedingincus';

    protected $fillable = [
        'KODECUS', 'NAMACUS', 'FROM', 'TO', 'HARGA', 'TANGGAL',
        'USER', 'USEREDIT',
    ];
}
