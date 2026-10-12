<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class aph extends Model
{
    protected $table = 'aph';

    // Primary key tetap id (default Laravel)
    protected $primaryKey = 'id';

    // Karena pakai auto increment
    public $incrementing = true;

    // protected $keyType = 'int';

    // Timestamps aktif (karena tadi pakai nullableTimestamps)
    public $timestamps = true;

    // Mass assignment
    protected $fillable = [
        'NOFAKTUR',
        'TGLFAKTUR',
        'TGLJT',
        'SUPPLIER',
        'HUTANG',
        'UM',
        'BAYAR',
        'RETUR',
        'DISCOUNT',
        'SALDO',
        'KETERANGAN',
        'AUTO',
    ];

    protected $casts = [
        'TGLFAKTUR' => 'date',
        'TGLJT'     => 'date',

        'HUTANG'   => 'decimal:2',
        'UM'       => 'decimal:2',
        'BAYAR'    => 'decimal:2',
        'RETUR'    => 'decimal:2',
        'DISCOUNT' => 'decimal:2',
        'SALDO'    => 'decimal:2',
    ];
}
