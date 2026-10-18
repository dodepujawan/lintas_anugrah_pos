<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'is_active',
        'KODE',
        'NAMA',
        'ALAMAT',
        'PHONE',
        'MULAI',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'MULAI' => 'date',
        'user_id' => 'integer',
    ];

    protected $with = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expedisis(): HasMany
    {
        return $this->hasMany(Expedisi::class, 'DRIVER', 'KODE');
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(DriverReceivable::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) ($this->NAMA ?? $this->name ?? 'Tanpa Nama'));
    }
}
