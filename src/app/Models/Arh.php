<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arh extends Model
{
    use HasFactory;

    protected $table = 'arh';
    protected $primaryKey = 'id';
    public $incrementing = true;

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
        'STATUS',
        'SOURCE',
        'NOJURNAL', // Added for journal linking
        'TGLLUNAS',
        'USER',
        'USER_UPDATE',
        'area_id',
        'area_name',
        'jurnal_id',
    ];

    protected $casts = [
        'TGLFAKTUR' => 'date',
        'TGLJT' => 'date',
        'TGLLUNAS' => 'date',
        'PIUTANG' => 'decimal:2',
        'BAYAR' => 'decimal:2',
        'RETUR' => 'decimal:2',
        'DISCOUNT' => 'decimal:2',
        'SALDO' => 'decimal:2',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function jurnalItem(): HasMany
    {
        return $this->hasMany(JurnalItem::class, 'NOJURNAL', 'no_jurnal');
    }
}
