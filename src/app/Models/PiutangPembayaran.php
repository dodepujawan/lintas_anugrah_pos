<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiutangPembayaran extends Model
{
    protected $table = 'piutang_pembayarans';

    protected $fillable = [
        'piutang_id',
        'tanggal',
        'jumlah',
        'metode_pembayaran',
        'keterangan',
        'jurnal_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function piutang(): BelongsTo
    {
        return $this->belongsTo(Piutang::class);
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
