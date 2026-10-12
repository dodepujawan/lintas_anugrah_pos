<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kwitansi extends Model
{
    use HasFactory;

    protected $table = 'kwitansi';

    protected $fillable = [
        'NO_KWT', 'TGL_KWT', 'NOFAKTUR',
        'SALDO_FAKTUR', 'NILAI_BAYAR', 'DISCOUNT', 'RETUR', 'SISA_FAKTUR',
        'JENIS_KWT', 'CUSTOMER', 'NAMACUST', 'NOBAYAR', 'KETERANGAN',
        'user', 'USEREDIT', 'area_id', 'area_name', 'NOJURNAL', 'jurnal_id',
    ];

    protected $casts = [
        'TGL_KWT' => 'date',
        'SALDO_FAKTUR' => 'decimal:0',
        'NILAI_BAYAR' => 'decimal:0',
        'DISCOUNT' => 'decimal:0',
        'RETUR' => 'decimal:0',
        'SISA_FAKTUR' => 'decimal:0',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function arh(): BelongsTo
    {
        return $this->belongsTo(Arh::class, 'NOFAKTUR', 'NOFAKTUR');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }
}
