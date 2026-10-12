<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalItemArchive extends Model
{
    use HasFactory;

    protected $table = 'jurnal_items_archives';

    protected $fillable = [
        'jurnal_id',
        'coa_id',
        'deskripsi',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }
}
