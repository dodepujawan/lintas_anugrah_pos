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
        'NOKWT', 'TGL', 'FDOK_TRANS', 'TGL_TRANS',
        'CUSTOMER', 'NOSJ', 'FKETERANG', 'FNAMA',
        'FNIL_DOK', 'TOTAL', 'PPN', 'DISC', 'NDISC',
        'USERINPUT', 'JENIS',
    ];

    protected $casts = [
        'TGL' => 'date',
        'TGL_TRANS' => 'date',
        'FNIL_DOK' => 'decimal:0',
        'TOTAL' => 'decimal:0',
        'PPN' => 'decimal:2',
        'DISC' => 'decimal:2',
        'NDISC' => 'decimal:0',
    ];

    public function arh(): BelongsTo
    {
        return $this->belongsTo(Arh::class, 'FDOK_TRANS', 'NOFAKTUR');
    }
}
