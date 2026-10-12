<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bapd extends Model
{
    protected $table = 'bapd';

    protected $fillable = [
        'NOBAYAR',
        'NOFAKTUR',
        'TGLFAKTUR',
        'SALDO_FAKTUR',
        'NILAI_BAYAR',
        'DISCOUNT',
        'RETUR',
        'SISA_FAKTUR',
        'KETERANGAN',
    ];

    protected $casts = [
        'TGLFAKTUR' => 'date',
        'SALDO_FAKTUR' => 'decimal:2',
        'NILAI_BAYAR' => 'decimal:2',
        'DISCOUNT' => 'decimal:2',
        'RETUR' => 'decimal:2',
        'SISA_FAKTUR' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(Baph::class, 'NOBAYAR', 'NOBAYAR');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Aph::class, 'NOFAKTUR', 'NOFAKTUR');
    }
}
