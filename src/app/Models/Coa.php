<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coa extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Coa $coa): void {
            $digits = preg_replace('/\D/', '', (string) $coa->kode_akun);

            if (strlen($digits) === 5) {
                $digits .= '000';
            }

            $coa->kode_akun = str_pad($digits, 8, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe',
        'kategori',
        'level',
        'parent_id',
        'saldo_normal',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Coa::class, 'parent_id');
    }

    public function jurnalItems(): HasMany
    {
        return $this->hasMany(JurnalItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    public function scopePosting($query)
    {
        return $query->where('level', 'Akun Posting');
    }

    public function setSaldoNormalAttribute($value): void
    {
        $normalized = trim((string) $value);

        if (strtolower($normalized) === 'kredit') {
            $this->attributes['saldo_normal'] = 'Kredit';
            return;
        }

        if (strtolower($normalized) === 'debit') {
            $this->attributes['saldo_normal'] = 'Debit';
            return;
        }

        if (is_numeric($value)) {
            $this->attributes['saldo_normal'] = ((float) $value > 0) ? 'Kredit' : 'Debit';
            return;
        }

        $this->attributes['saldo_normal'] = 'Debit';
    }

    public function getLevelIndentAttribute(): int
    {
        return match ($this->level) {
            'Header' => 0,
            'Sub Header' => 1,
            'Kelompok Akun' => 2,
            'Akun Posting' => 3,
            default => 0,
        };
    }

    public function getLevelBadgeClassAttribute(): string
    {
        return match ($this->level) {
            'Header' => 'secondary',
            'Sub Header' => 'primary',
            'Kelompok Akun' => 'info',
            'Akun Posting' => 'success',
            default => 'dark',
        };
    }

    public function getSaldoAttribute()
    {
        $totalDebit = (float) $this->jurnalItems()->sum('debit');
        $totalKredit = (float) $this->jurnalItems()->sum('kredit');
        $runningBalance = $totalDebit - $totalKredit;

        return $this->saldo_normal === 'Kredit' ? -$runningBalance : $runningBalance;
    }

    public function getSaldoNormalLabelAttribute(): string
    {
        return match (strtolower((string) $this->saldo_normal)) {
            'debit' => 'Debit',
            'kredit' => 'Kredit',
            default => 'Debit',
        };
    }
}
