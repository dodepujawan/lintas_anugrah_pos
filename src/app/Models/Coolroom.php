<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coolroom extends Model
{
    use HasFactory;

    protected $table = 'coolrooms';

    protected $fillable = [
        'CUSTOMER_KODE', 'CUSTOMER', 'TGL', 'NOSJ', 'TGLSJ', 'TGLMUAT', 'NOMUAT',
        'INVOICE', 'TGLINVOICE', 'TGLJT',
        'AKUN_PENDAPATAN', 'NAMA_AKUN_PENDAPATAN', 'AKUN_PIUTANG', 'NAMA_AKUN_PIUTANG',
        'UNIT', 'BOXING', 'SUBTOTAL', 'DPP', 'NPPN', 'BAYAR', 'PIUTANG', 'TOP', 'KWT', 'TGLKW',
        'STS', 'USERINPUT', 'USERINV', 'CABANG', 'NAMACUST',
        'KENDARAAN', 'NAMA_KENDARAAN', 'DRIVER', 'NAMA_DRIVER',
        'HARGA', 'JUMLAH', 'DISC', 'NDISCAW', 'NDISC', 'TOTAL', 'PPN', 'GRAND',
        'SJ', 'KETERANGAN', 'STATUS', 'user', 'USEREDIT',
        'area_id', 'area_name', 'NOJURNAL', 'jurnal_id',
    ];

    protected $casts = [
        'TGL' => 'date',
        'TGLSJ' => 'date',
        'TGLMUAT' => 'date',
        'TGLINVOICE' => 'date',
        'TGLJT' => 'date',
        'TGLKW' => 'date',
        'BOXING' => 'boolean',
        'HARGA' => 'decimal:0',
        'JUMLAH' => 'decimal:3',
        'SUBTOTAL' => 'decimal:0',
        'DISC' => 'decimal:2',
        'NDISCAW' => 'decimal:0',
        'NDISC' => 'decimal:0',
        'DPP' => 'decimal:0',
        'NPPN' => 'decimal:0',
        'TOTAL' => 'decimal:0',
        'PPN' => 'decimal:2',
        'GRAND' => 'decimal:0',
        'BAYAR' => 'decimal:0',
        'PIUTANG' => 'decimal:0',
        'TOP' => 'integer',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Mcustomer::class, 'CUSTOMER_KODE', 'CUSTOMER');
    }
}
