<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Piutang extends Model
{
    protected $table = 'piutangs';

    protected $fillable = [
        'tanggal',
        'pelanggan',
        'nomor_nota_invoice',
        'deskripsi',
        'nominal',
        'diterima',
        'sisa',
        'is_lunas',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'diterima' => 'decimal:2',
        'sisa' => 'decimal:2',
        'is_lunas' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $piutang): void {
            $piutang->diterima = 0;
            $piutang->sisa = (float) $piutang->nominal;
            $piutang->is_lunas = false;
        });

        static::saving(function (self $piutang): void {
            if ((float) $piutang->sisa <= 0) {
                $piutang->sisa = 0;
                $piutang->is_lunas = true;
            } else {
                $piutang->is_lunas = false;
            }
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PiutangPembayaran::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
