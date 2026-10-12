<?php

namespace App\Http\Controllers;

use App\Services\CoaSaldoNormalAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoaSaldoNormalAuditController extends Controller
{
    public function index(Request $request, CoaSaldoNormalAuditService $auditService): View|RedirectResponse
    {
        if (! $this->isAdministrator($request)) {
            return redirect()->route('dashboard')->with('error', 'Hanya Administrator yang boleh menjalankan audit saldo normal COA.');
        }

        $rows = $auditService->audit();
        $suspiciousRows = $rows->where('perlu_update', true)->values();

        return view('settings.coa_saldo_normal', [
            'rows' => $rows,
            'suspiciousRows' => $suspiciousRows,
        ]);
    }

    public function fix(Request $request, CoaSaldoNormalAuditService $auditService): RedirectResponse
    {
        if (! $this->isAdministrator($request)) {
            return redirect()->route('dashboard')->with('error', 'Hanya Administrator yang boleh memperbaiki saldo normal COA.');
        }

        $fixedRows = $auditService->fix();

        if ($fixedRows->isEmpty()) {
            return redirect()
                ->route('coa-saldo-normal')
                ->with('success', 'Semua saldo normal COA sudah sesuai rekomendasi.');
        }

        return redirect()
            ->route('coa-saldo-normal')
            ->with('success', $fixedRows->count() . ' akun berhasil diperbaiki.');
    }

    private function isAdministrator(Request $request): bool
    {
        return $request->user()?->role?->name === 'Administrator';
    }
}
