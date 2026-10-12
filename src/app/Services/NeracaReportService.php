<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NeracaReportService
{
    public function build(array $input): array
    {
        $periodType = filled($input['period_type'] ?? null)
            ? $input['period_type']
            : (filled($input['month'] ?? null) && filled($input['year'] ?? null) ? 'month_year' : 'date_range');
        $viewMode = $input['view_mode'] ?? 'all';
        $scope = $viewMode;
        $onlyBalance = filter_var($input['only_balance'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $onlyTransactions = filter_var($input['only_transactions'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! isset($input['view_mode']) && isset($input['scope'])) {
            if ($input['scope'] === 'saldo') {
                $viewMode = 'all';
                $scope = 'saldo';
                $onlyBalance = true;
            }

            if ($input['scope'] === 'transaksi') {
                $viewMode = 'all';
                $scope = 'transaksi';
                $onlyTransactions = true;
            }
        }

        $month = $input['month'] ?? null;
        $year = $input['year'] ?? null;
        if ($periodType === 'month_year') {
            $periodType = 'month_year';
            $month = $month ?? now()->format('m');
            $year = $year ?? now()->format('Y');
            $fromDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $toDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $fromDate = $input['from_date'] ?? $input['tanggal'] ?? now()->toDateString();
            $toDate = $input['to_date'] ?? $input['tanggal'] ?? now()->toDateString();
        }

        $showCode = filter_var($input['show_code'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $displayLevel = match ($viewMode) {
            'header' => 1,
            'header_subheader_kelompok' => 3,
            default => (int) ($input['display_level'] ?? 4),
        };

        $coas = Coa::active()
            ->select(['id', 'parent_id', 'kode_akun', 'nama_akun', 'level', 'tipe', 'saldo_normal', 'is_active'])
            ->orderBy('kode_akun')
            ->get();

        // For balance snapshot report, include all posted journals up to the end date
        // so monthly snapshots are cumulative and will "connect" across months.
        $journalTotals = $this->getJournalTotalsUntil($toDate);

        if (app()->runningUnitTests()) {
            file_put_contents(storage_path('logs/neraca-debug.log'), json_encode([
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'view_mode' => $viewMode,
                'only_balance' => $onlyBalance,
                'only_transactions' => $onlyTransactions,
                'coas' => $coas->map(fn($c) => [
                    'id' => $c->id,
                    'kode_akun' => $c->kode_akun,
                    'nama_akun' => $c->nama_akun,
                    'tipe' => $c->tipe,
                    'level' => $c->level,
                    'parent_id' => $c->parent_id,
                ])->values(),
                'journal_totals' => $journalTotals->map(fn($entry) => [
                    'coa_id' => $entry->coa_id,
                    'debit' => $entry->total_debit,
                    'kredit' => $entry->total_kredit,
                ])->values(),
                'jurnal_count' => \App\Models\Jurnal::where('is_posted', true)->whereBetween('tanggal', [$fromDate, $toDate])->count(),
                'jurnals' => \App\Models\Jurnal::all()->map(fn($j) => [
                    'id' => $j->id,
                    'tanggal' => $j->tanggal?->format('Y-m-d'),
                    'is_posted' => $j->is_posted,
                    'status' => $j->status,
                    'created_by' => $j->created_by,
                ])->values(),
                'jurnal_items' => \App\Models\JurnalItem::all()->map(fn($item) => [
                    'id' => $item->id,
                    'jurnal_id' => $item->jurnal_id,
                    'coa_id' => $item->coa_id,
                    'debit' => $item->debit,
                    'kredit' => $item->kredit,
                ])->values(),
                'jurnal_item_total_count' => \App\Models\JurnalItem::whereHas('jurnal', fn($q) => $q->where('is_posted', true)->whereBetween('tanggal', [$fromDate, $toDate]))->count(),
                'jurnal_items_filtered' => \App\Models\JurnalItem::whereHas('jurnal', fn($q) => $q->where('is_posted', true)->whereBetween('tanggal', [$fromDate, $toDate]))->get()->map(fn($item) => [
                    'id' => $item->id,
                    'jurnal_id' => $item->jurnal_id,
                    'coa_id' => $item->coa_id,
                    'debit' => $item->debit,
                    'kredit' => $item->kredit,
                ])->values(),
                'raw_jurnals' => \Illuminate\Support\Facades\DB::table('jurnals')->get()->map(fn($j) => (array) $j)->values(),
                'raw_jurnal_items' => \Illuminate\Support\Facades\DB::table('jurnal_items')->get()->map(fn($i) => (array) $i)->values(),
            ], JSON_PRETTY_PRINT));
        }

        $assetTree = $this->buildTree($coas, $journalTotals, ['Aset'], null, $onlyBalance, $onlyTransactions, $displayLevel, 0);
        $liabilityTree = $this->buildTree($coas, $journalTotals, ['Kewajiban'], null, $onlyBalance, $onlyTransactions, $displayLevel, 0);
        $equityTree = $this->buildTree($coas, $journalTotals, ['Ekuitas'], null, $onlyBalance, $onlyTransactions, $displayLevel, 0);

        $totalPendapatan = $this->hitungTotalLabaRugi($coas, $journalTotals, 'Pendapatan');
        $totalBeban = $this->hitungTotalLabaRugi($coas, $journalTotals, 'Beban');
        $labaBerjalan = $totalPendapatan - $totalBeban;

        $totalAset = $this->sumTree($assetTree);
        $totalKewajiban = $this->sumTree($liabilityTree);
        $totalEkuitas = $this->sumTree($equityTree) + $labaBerjalan;
        $totalPasiva = $totalKewajiban + $totalEkuitas;
        $selisih = abs($totalAset - $totalPasiva);
        $isBalance = $selisih < 1;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'month' => $month,
            'year' => $year,
            'scope' => $scope,
            'period_type' => $periodType,
            'view_mode' => $viewMode,
            'only_balance' => $onlyBalance,
            'only_transactions' => $onlyTransactions,
            'show_code' => $showCode,
            'display_level' => $displayLevel,
            'asset_tree' => $assetTree,
            'liability_tree' => $liabilityTree,
            'equity_tree' => $equityTree,
            'laba_berjalan' => $labaBerjalan,
            'total_asset' => $totalAset,
            'total_kewajiban' => $totalKewajiban,
            'total_ekuitas' => $totalEkuitas,
            'total_pasiva' => $totalPasiva,
            'selisih' => $selisih,
            'is_balance' => $isBalance,
            'flat_rows' => array_merge(
                $this->flattenTree($assetTree, 'Aset'),
                $this->flattenTree($liabilityTree, 'Kewajiban'),
                $this->flattenTree($equityTree, 'Ekuitas')
            ),
        ];
    }

    private function buildTree(Collection $coas, Collection $journalTotals, array $allowedTypes, ?int $parentId, bool $onlyBalance, bool $onlyTransactions, int $displayLevel, int $level): array
    {
        $nodes = $coas->filter(function ($coa) use ($parentId, $allowedTypes): bool {
            if ($parentId === null) {
                return is_null($coa->parent_id) && in_array($coa->tipe, $allowedTypes, true);
            }

            return (int) $coa->parent_id === (int) $parentId;
        })->sortBy('kode_akun')->values();

        $result = [];

        foreach ($nodes as $coa) {
            $children = $this->buildTree($coas, $journalTotals, $allowedTypes, $coa->id, $onlyBalance, $onlyTransactions, $displayLevel, $level + 1);
            $levelDepth = $this->levelDepth($coa->level);
            $isPosting = $coa->level === 'Akun Posting';
            $balance = $this->getBalance($coa, $journalTotals);
            $hasTransactions = $this->hasTransactions($coa, $journalTotals);
            $visibleChildren = array_values(array_filter($children, fn($child) => $child['visible']));
            $visible = $this->shouldShowNode($coa, $balance, $hasTransactions, $onlyBalance, $onlyTransactions, $displayLevel, $levelDepth, $visibleChildren);

            if (! $visible) {
                continue;
            }

            $subtotal = $isPosting
                ? $balance
                : $this->calculateVisibleSubtotal($coa, $coas, $journalTotals);

            $result[] = [
                'coa' => $coa,
                'level' => $level + 1,
                'level_depth' => $levelDepth,
                'balance' => $balance,
                'subtotal' => $subtotal,
                'visible' => true,
                'children' => $visibleChildren,
            ];
        }

        return $result;
    }

    private function shouldShowNode($coa, float $balance, bool $hasTransactions, bool $onlyBalance, bool $onlyTransactions, int $displayLevel, int $levelDepth, array $visibleChildren): bool
    {
        // If node is deeper than requested display level, normally hide it.
        // However, when filters target posting-account detail (`onlyBalance` or `onlyTransactions`),
        // we should still allow `Akun Posting` nodes to be evaluated so their values
        // can make parent/header rows visible.
        if ($levelDepth > $displayLevel) {
            if ($coa->level !== 'Akun Posting' || (! $onlyBalance && ! $onlyTransactions)) {
                return false;
            }
            // otherwise: allow falling through to posting-account logic below
        }

        if ($coa->level === 'Akun Posting') {
            if ($onlyBalance && $onlyTransactions) {
                return abs($balance) > 0 && $hasTransactions;
            }

            if ($onlyBalance) {
                return abs($balance) > 0;
            }

            if ($onlyTransactions) {
                return $hasTransactions;
            }

            return true;
        }

        if (! $onlyBalance && ! $onlyTransactions) {
            return true;
        }

        return count($visibleChildren) > 0;
    }

    private function getBalance($coa, Collection $journalTotals): float
    {
        $entry = $journalTotals->get($coa->id);
        $debit = (float) ($entry->total_debit ?? 0);
        $kredit = (float) ($entry->total_kredit ?? 0);

        // Tanda mengikuti sisi laporan (tipe akun), bukan hanya saldo_normal.
        // Contoh:
        // - Akumulasi penyusutan (Aset, saldo kredit) -> debit - kredit = negatif (mengurangi aset)
        // - Prive (Ekuitas, saldo debit) -> kredit - debit = negatif (mengurangi ekuitas)
        return match ($coa->tipe) {
            'Aset', 'Beban' => $debit - $kredit,
            'Kewajiban', 'Ekuitas', 'Pendapatan' => $kredit - $debit,
            default => strtolower((string) $coa->saldo_normal) === 'kredit'
                ? $kredit - $debit
                : $debit - $kredit,
        };
    }

    private function getJournalTotalsUntil(string $toDate): Collection
    {
        $activeTotals = $this->sumJournalItemsUntil(JurnalItem::query(), $toDate);
        $archivedTotals = $this->sumJournalItemsUntil(JurnalItemArchive::query(), $toDate);

        return $activeTotals
            ->concat($archivedTotals)
            ->groupBy('coa_id')
            ->map(function (Collection $rows) {
                return (object) [
                    'coa_id' => $rows->first()->coa_id,
                    'total_debit' => $rows->sum(fn($row) => (float) $row->total_debit),
                    'total_kredit' => $rows->sum(fn($row) => (float) $row->total_kredit),
                ];
            });
    }

    private function sumJournalItemsUntil($query, string $toDate): Collection
    {
        return $query
            ->selectRaw('coa_id, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->whereHas('jurnal', function ($query) use ($toDate): void {
                $query->where('is_posted', true)
                    ->whereDate('tanggal', '<=', $toDate);
            })
            ->groupBy('coa_id')
            ->get();
    }

    private function calculateVisibleSubtotal($coa, Collection $coas, Collection $journalTotals): float
    {
        $balance = $this->getBalance($coa, $journalTotals);
        $isPosting = $coa->level === 'Akun Posting';

        if ($isPosting) {
            return $balance;
        }

        $children = $coas->filter(fn($child) => (int) $child->parent_id === (int) $coa->id)->values();

        return $balance + collect($children)->sum(fn($child) => $this->calculateVisibleSubtotal($child, $coas, $journalTotals));
    }

    private function hasTransactions($coa, Collection $journalTotals): bool
    {
        $entry = $journalTotals->get($coa->id);
        $debit = (float) ($entry->total_debit ?? 0);
        $kredit = (float) ($entry->total_kredit ?? 0);

        return $debit > 0 || $kredit > 0;
    }

    private function levelDepth(string $level): int
    {
        return match ($level) {
            'Header' => 1,
            'Sub Header' => 2,
            'Kelompok Akun' => 3,
            'Akun Posting' => 4,
            default => 1,
        };
    }

    private function sumTree(array $nodes): float
    {
        return collect($nodes)->sum(fn($node) => $node['subtotal']);
    }

    private function flattenTree(array $nodes, string $section): array
    {
        $rows = [];

        foreach ($nodes as $node) {
            $rows[] = [
                'section' => $section,
                'kode_akun' => $node['coa']->kode_akun,
                'nama_akun' => $node['coa']->nama_akun,
                'level' => $node['coa']->level,
                'balance' => $node['subtotal'],
            ];

            if (! empty($node['children'])) {
                $rows = array_merge($rows, $this->flattenTree($node['children'], $section));
            }
        }

        return $rows;
    }

    private function hitungTotalLabaRugi(Collection $coas, Collection $journalTotals, string $tipe): float
    {
        $coaIds = $coas->filter(fn($coa) => $coa->tipe === $tipe && $coa->level === 'Akun Posting')->pluck('id');
        $total = 0;

        foreach ($coaIds as $coaId) {
            $entry = $journalTotals->get($coaId);
            $debit = (float) ($entry->total_debit ?? 0);
            $kredit = (float) ($entry->total_kredit ?? 0);
            $balance = $tipe === 'Pendapatan' ? $kredit - $debit : $debit - $kredit;
            $total += $balance;
        }

        return $total;
    }
}
