<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function pricendingincus()
    {
        return $this->hasMany(Pricedingincus::class, 'FROM', 'FROM');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'KODE', 'KODE');
    }
}
