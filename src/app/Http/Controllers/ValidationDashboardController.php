<?php

namespace App\Http\Controllers;

use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Services\DatabaseBackupService;
use Illuminate\View\View;

class ValidationDashboardController extends Controller
{
    public function index(DatabaseBackupService $backupService): View
    {
        $unbalancedJournals = Jurnal::query()
            ->whereRaw('ABS(COALESCE(total_debit, 0) - COALESCE(total_kredit, 0)) > 0.01')
            ->latest('tanggal')
            ->limit(10)
            ->get();

        $oldDraftJournals = Jurnal::query()
            ->where('is_posted', false)
            ->whereDate('tanggal', '<=', now()->subDays(7)->toDateString())
            ->latest('tanggal')
            ->limit(10)
            ->get();

        $coaWithoutSaldoNormal = Coa::active()
            ->posting()
            ->where(function ($query) {
                $query->whereNull('saldo_normal')
                    ->orWhere('saldo_normal', '');
            })
            ->orderBy('kode_akun')
            ->limit(10)
            ->get();

        $requiredMappings = ['cash_main', 'equity_capital', 'retained_earnings', 'current_year_profit', 'prive'];
        $existingMappings = AccountMapping::whereIn('mapping_key', $requiredMappings)
            ->whereNotNull('account_id')
            ->pluck('mapping_key')
            ->all();
        $missingMappings = array_values(array_diff($requiredMappings, $existingMappings));
        $latestBackup = collect($backupService->files())->first();

        $summary = [
            [
                'label' => 'Jurnal Tidak Balance',
                'count' => $unbalancedJournals->count(),
                'variant' => $unbalancedJournals->isEmpty() ? 'success' : 'danger',
            ],
            [
                'label' => 'Draft Lebih 7 Hari',
                'count' => $oldDraftJournals->count(),
                'variant' => $oldDraftJournals->isEmpty() ? 'success' : 'warning',
            ],
            [
                'label' => 'COA Tanpa Saldo Normal',
                'count' => $coaWithoutSaldoNormal->count(),
                'variant' => $coaWithoutSaldoNormal->isEmpty() ? 'success' : 'warning',
            ],
            [
                'label' => 'Mapping Belum Lengkap',
                'count' => count($missingMappings),
                'variant' => empty($missingMappings) ? 'success' : 'warning',
            ],
        ];

        return view('validation-dashboard.index', compact(
            'summary',
            'unbalancedJournals',
            'oldDraftJournals',
            'coaWithoutSaldoNormal',
            'missingMappings',
            'latestBackup'
        ));
    }
}
