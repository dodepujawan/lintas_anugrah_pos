<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricedinginhis extends Model
{
    use HasFactory;

    protected $table = 'pricedinginhis';
    protected $primaryKey = 'id';
    public $timestamps = true; // true untuk menggunakan created_at & updated_at

    protected $fillable = [
        'KODEDGN',
        'TANGGAL',
        'KODE',
        'PERIODE',
        'PLAT',
        'JENIS',
        'ITEM',
        'HARGA',
        'USER',
        'USEREDIT',
        'KUNCI'
        // created_at & updated_at otomatis, tidak perlu dimasukkan fillable
    ];

    protected $casts = [
        'TANGGAL' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
