<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedingin extends Model
{
    use HasFactory;

    protected $table = 'pricedingins';

    protected $fillable = [
        'KODEDGN', 'KODE', 'TANGGAL', 'PERIODE', 'PLAT',
        'JENIS', 'ITEM', 'HARGA', 'KUNCI',
        'USER', 'USEREDIT',
        'FROM', 'TO',
    ];

    protected $casts = [
        'TANGGAL' => 'date',
    ];

    public function pricendingincus()
    {
        return $this->hasMany(Pricedingincus::class, 'FROM', 'FROM');
    }
}
