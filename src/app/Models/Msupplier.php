<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Msupplier extends Model
{
    use HasFactory;

    protected $table = 'msupplier';

    protected $primaryKey = 'SUPPLIER';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'SUPPLIER',
        'KATEGORI',
        'NAMA',
        'ALAMAT1',
        'ALAMAT2',
        'KOTA',
        'TELEPON',
        'FAX',
        'EMAIL',
        'KONTAK',
        'NOREK',
        'BANK',
        'ATASNAMA',
        'SALDO',
        'RETURAN',
        'FTOP',
        'DISC_REG',
        'area_id',
        'area_name',
        'jurnal_id',
    ];

    protected $casts = [
        'SALDO' => 'decimal:2',
        'RETURAN' => 'decimal:2',
        'FTOP' => 'integer',
        'DISC_REG' => 'decimal:2',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function aphs(): HasMany
    {
        return $this->hasMany(Aph::class, 'SUPPLIER', 'SUPPLIER');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->SUPPLIER) . ' - ' . (trim((string) $this->NAMA) ?: 'Tanpa Nama');
    }
}
