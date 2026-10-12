<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    protected $table = 'kwitansi';

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $casts = [

        'TGL' => 'date',
        'TGL_TRANS' => 'date',

        'FNIL_DOK' => 'decimal:0',
        'TOTAL'    => 'decimal:0',
        'PPN'      => 'decimal:2',
        'DISC'     => 'decimal:2',
        'NDISC'    => 'decimal:0',

    ];
}
