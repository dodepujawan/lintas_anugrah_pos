<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraans';

    public $timestamps = true;

    protected $fillable = [
        'KODE', 'NAMA', 'PLAT', 'FNO_PRK_B', 'FNO_PRK_P', 'FNO_PRK_S',
        'FNO_PRK_O', 'FNO_PRK_M', 'FNO_PRK_T',
        'JENIS', 'FNO_PRK', 'NO_BKK', 'KENDARAAN_TYPE',
        'USER', 'USEREDIT',
    ];

    protected $casts = [
        //
    ];

    public function expedisis(): HasMany
    {
        return $this->hasMany(Expedisi::class, 'KENDARAAN', 'KODE');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->NAMA) ?: 'Tanpa Nama';
    }
}
