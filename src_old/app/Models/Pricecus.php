<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricecus extends Model
{
    use HasFactory;

    protected $table = 'pricecus';
    protected $primaryKey = 'id';
    public $timestamps = true; // true untuk menggunakan created_at & updated_at

    protected $fillable = [
        'KODECUS',
        'KODE',
        'TANGGAL',
        'KETERANGAN',
        'DARI',
        'SAMPAI',
        'RUTE',
        'HARGA',
        'HV',
        'HKG',
        'HBOK',
        'USER',
        'USEREDIT',
        'KUNCI',
        'HG',
        'JENIS'
        // created_at & updated_at otomatis, tidak perlu dimasukkan fillable
    ];

    protected $casts = [
        'TANGGAL' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
