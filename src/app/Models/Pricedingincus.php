<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'KODE', 'KODE');
    }
}
