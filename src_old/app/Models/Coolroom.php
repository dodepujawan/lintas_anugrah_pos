<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coolroom extends Model
{
    protected $table = 'coolrooms';

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $casts = [

        // ======================
        // DATE
        // ======================
        'TGL'        => 'date',
        'TGLSJ'      =>'date',
        'TGLINVOICE' => 'date',
        'TGLJT'      => 'date',
        'TGLKW'      => 'date',

        // ======================
        // BOOLEAN
        // ======================
        'BOXING' => 'boolean',

        // ======================
        // DECIMAL
        // ======================
        'JUMLAH'   => 'decimal:3',

        'HARGA'    => 'decimal:0',

        'SUBTOTAL' => 'decimal:0',

        'DISC'     => 'decimal:2',
        'NDISC'    => 'decimal:0',

        'DPP'      => 'decimal:0',

        'PPN'      => 'decimal:2',
        'NPPN'     => 'decimal:0',

        'TOTAL'    => 'decimal:0',
        'GRAND'    => 'decimal:0',

        'BAYAR'    => 'decimal:0',
        'PIUTANG'  => 'decimal:0',

    ];
}
