<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arh extends Model
{
    protected $table = 'arh';

    // Pakai id sebagai primary key
    protected $primaryKey = 'id';

    // id auto increment
    public $incrementing = true;

    // Tipe primary key
    // protected $keyType = 'int';

    // Timestamp aktif
    public $timestamps = true;

    // Kolom yang boleh diisi
    protected $fillable = [
        'NOFAKTUR',
        'TGLFAKTUR',
        'TGLJT',
        'CUSTOMER',
        'SALES',
        'AREA',
        'DIVISI',
        'PIUTANG',
        'BAYAR',
        'RETUR',
        'DISCOUNT',
        'SALDO',
        'CABANG',
        'KETERANGAN',
        'USER',
        'USER_UPDATE',
    ];

    // Casting biar aman
    protected $casts = [
        'TGLFAKTUR' => 'date',
        'TGLJT'     => 'date',
        'PIUTANG'   => 'decimal:2',
        'BAYAR'     => 'decimal:2',
        'RETUR'     => 'decimal:2',
        'DISCOUNT'  => 'decimal:2',
        'SALDO'     => 'decimal:2',
    ];
}
