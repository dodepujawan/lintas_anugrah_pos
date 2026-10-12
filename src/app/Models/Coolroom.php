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
        'TGLMUAT', 'NOSJ', 'NOMUAT', 'CUSTOMER', 'NAMACUST',
        'KENDARAAN', 'NAMA_KENDARAAN', 'DRIVER', 'NAMA_DRIVER',
        'HARGA', 'JUMLAH', 'DISC', 'NDISCAW', 'NDISC', 'TOTAL', 'PPN', 'GRAND',
        'SJ', 'KETERANGAN', 'STATUS', 'user', 'USEREDIT',
        'area_id', 'area_name', 'NOJURNAL', 'jurnal_id',
    ];

    protected $casts = [
        'TGLMUAT' => 'date',
        'HARGA' => 'decimal:0',
        'JUMLAH' => 'decimal:0',
        'DISC' => 'decimal:2',
        'NDISCAW' => 'decimal:0',
        'NDISC' => 'decimal:0',
        'TOTAL' => 'decimal:0',
        'PPN' => 'decimal:2',
        'GRAND' => 'decimal:0',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Mcustomer::class, 'CUSTOMER', 'CUSTOMER');
    }
}
