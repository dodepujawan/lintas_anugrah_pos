<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barh extends Model
{
    protected $table = 'barh';

    protected $fillable = [
        'NOBAYAR',
        'TGLBAYAR',
        'CUSTOMER',
        'JUMLAH_BAYAR',
        'METODE_PEMBAYARAN',
        'PAYMENT_COA_ID',
        'PPH23_COA_ID',
        'DISCOUNT_COA_ID',
        'BANK_ADMIN_COA_ID',
        'NO_REFERENSI',
        'KETERANGAN',
        'CABANG',
        'USER',
        'NOJURNAL', // Added for journal linking
        'STATUS', 'VOID_REASON', 'VOIDED_AT', 'VOIDED_BY', 'NOJURNAL_PEMBALIK',
        'USER_UPDATE',
    ];

    protected $casts = [
        'TGLBAYAR' => 'date',
        'JUMLAH_BAYAR' => 'decimal:2',
        'PAYMENT_COA_ID' => 'integer',
        'PPH23_COA_ID' => 'integer',
        'DISCOUNT_COA_ID' => 'integer',
        'BANK_ADMIN_COA_ID' => 'integer',
        'VOIDED_AT' => 'datetime',
        'VOIDED_BY' => 'integer',
    ];

    public function paymentCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'PAYMENT_COA_ID');
    }

    public function details(): HasMany
    {
        return $this->hasMany(Bard::class, 'NOBAYAR', 'NOBAYAR');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'VOIDED_BY');
    }
}
