<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aph extends Model
{
    use HasFactory;

    protected $table = 'aph';

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
        'CABANG',
        'KETERANGAN',
        'AUTO',
        'STATUS',
        'SOURCE',
        'TGLLUNAS',
        'NOJURNAL', // Added for journal linking
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
        'HUTANG' => 'decimal:2',
        'UM' => 'decimal:2',
        'BAYAR' => 'decimal:2',
        'RETUR' => 'decimal:2',
        'DISCOUNT' => 'decimal:2',
        'SALDO' => 'decimal:2',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(Bapd::class, 'NOFAKTUR', 'NOFAKTUR');
    }

    public function supplierMaster(): BelongsTo
    {
        return $this->belongsTo(Msupplier::class, 'SUPPLIER', 'SUPPLIER');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function jurnalItem(): HasMany
    {
        return $this->hasMany(JurnalItem::class, 'NOJURNAL', 'no_jurnal');
    }

    public function getSupplierDisplayAttribute(): string
    {
        return $this->supplierMaster?->display_name ?? $this->SUPPLIER;
    }
}
