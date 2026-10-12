<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    use HasFactory;
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_POSTED = 'Posted';
    public const STATUS_VOID = 'Void';

    protected $fillable = [
        'no_jurnal',
        'tanggal',
        'tipe',
        'module_type',
        'status',
        'keterangan',
        'total_debit',
        'total_kredit',
        'referensi',
        'created_by',
        'is_posted',
        'posted_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_debit' => 'decimal:2',
        'total_kredit' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JurnalItem::class);
    }

    public function archivedItems(): HasMany
    {
        return $this->hasMany(JurnalItemArchive::class);
    }

    public function loadItemHistory(): self
    {
        $this->loadMissing(['items.coa', 'archivedItems.coa']);

        $items = $this->items
            ->concat($this->archivedItems)
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $this->setRelation('items', $items);

        return $this;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeVoid($query)
    {
        return $query->where('status', self::STATUS_VOID);
    }

    public function scopeNotPosted($query)
    {
        return $query->where('status', '!=', self::STATUS_POSTED);
    }
}
