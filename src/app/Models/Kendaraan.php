<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraans';

    public $timestamps = true;

    protected $fillable = [
        'KODE', 'NAMA', 'PLAT', 'FNO_PRK_B', 'FNO_PRK_T',
        'JENIS', 'FNO_PRK', 'NO_BKK', 'KENDARAAN_TYPE',
        'USER', 'USEREDIT',
    ];

    protected $casts = [
        //
    ];

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->NAMA) ?: 'Tanpa Nama';
    }
}
