<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'jurnal_id',
        'coa_id',
        'deskripsi',
        'debit',
        'kredit'
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

    /**
     * Scope untuk menggabungkan data aktif dan arsip dengan UNION.
     * Gunakan scope ini ketika query melewati batas 2 tahun.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate Format: YYYY-MM-DD
     * @param string $endDate Format: YYYY-MM-DD
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithArchived(Builder $query, string $startDate, string $endDate): Builder
    {
        $activeQuery = self::query()
            ->whereHas('jurnal', fn($q) => $q->whereDate('tanggal', '>=', $startDate)
                ->whereDate('tanggal', '<=', $endDate))
            ->select(['id', 'jurnal_id', 'coa_id', 'deskripsi', 'debit', 'kredit', 'created_at', 'updated_at']);

        $archivedQuery = JurnalItemArchive::query()
            ->whereHas('jurnal', fn($q) => $q->whereDate('tanggal', '>=', $startDate)
                ->whereDate('tanggal', '<=', $endDate))
            ->select(['id', 'jurnal_id', 'coa_id', 'deskripsi', 'debit', 'kredit', 'created_at', 'updated_at']);

        return $query->fromSub(
            $activeQuery->unionAll($archivedQuery),
            'combined_items'
        );
    }

    /**
     * Scope untuk filter berdasarkan akun tertentu pada query gabungan.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array $coaIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCoaIds(Builder $query, int|array $coaIds): Builder
    {
        $coaIds = is_array($coaIds) ? $coaIds : [$coaIds];
        return $query->whereIn('coa_id', $coaIds);
    }

    /**
     * Scope untuk filter hanya data aktif (tidak termasuk arsip).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveOnly(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subYears(2));
    }
}
