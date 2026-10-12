<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Baph extends Model
{
    protected $table = 'baph';

    protected $fillable = [
        'NOBAYAR',
        'TGLBAYAR',
        'SUPPLIER',
        'JUMLAH_BAYAR',
        'METODE_PEMBAYARAN',
        'PAYMENT_COA_ID',
        'NO_REFERENSI',
        'NOJURNAL',
        'STATUS', 'VOID_REASON', 'VOIDED_AT', 'VOIDED_BY', 'NOJURNAL_PEMBALIK',
        'KETERANGAN',
        'CABANG',
        'USER',
        'USER_UPDATE',
    ];

    protected $casts = [
        'TGLBAYAR' => 'date',
        'JUMLAH_BAYAR' => 'decimal:2',
        'PAYMENT_COA_ID' => 'integer',
        'VOIDED_AT' => 'datetime',
        'VOIDED_BY' => 'integer',
    ];

    public function paymentCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'PAYMENT_COA_ID');
    }

    public function supplierMaster(): BelongsTo
    {
        return $this->belongsTo(Msupplier::class, 'SUPPLIER', 'SUPPLIER');
    }

    public function getSupplierDisplayAttribute(): string
    {
        return $this->supplierMaster?->display_name ?? $this->SUPPLIER;
    }

    public function details(): HasMany
    {
        return $this->hasMany(Bapd::class, 'NOBAYAR', 'NOBAYAR');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'VOIDED_BY');
    }
}
