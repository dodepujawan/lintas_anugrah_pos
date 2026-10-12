<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedingin extends Model
{
    use HasFactory;

    protected $table = 'pricedingins';

    protected $fillable = [
        'FROM', 'TO', 'HARGA', 'TANGGAL', 'USER', 'USEREDIT',
    ];

    public function pricendingincus()
    {
        return $this->hasMany(Pricedingincus::class, 'FROM', 'FROM');
    }
}
