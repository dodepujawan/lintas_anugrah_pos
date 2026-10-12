<?php

namespace App\Http\Controllers;

use App\Models\AccountMapping;
use App\Models\Coa;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountMappingController extends Controller
{
    public function index()
    {
        $mappingLabels = AccountMapping::mappingKeys();
        $mappingDescriptions = AccountMapping::mappingDescriptions();
        $mappingGroups = AccountMapping::mappingGroups();
        $currentMappings = AccountMapping::pluck('account_id', 'mapping_key')->toArray();
        $recommendedDefaults = [
            'receivable_transfer_bank' => '01010201',
            'receivable_pph23' => '01010502',
            'receivable_payment_loss' => '05040103',
            'receivable_bank_admin' => '05040102',
            'sales_revenue_eks' => '04010101',
            'sales_revenue_ren' => '04010201',
            'sales_revenue_cold_room' => '04010202',
        ];

        foreach ($recommendedDefaults as $mappingKey => $accountCode) {
            if (! isset($currentMappings[$mappingKey])) {
                $accountId = Coa::query()->where('kode_akun', $accountCode)->value('id');
                if ($accountId) {
                    $currentMappings[$mappingKey] = (int) $accountId;
                }
            }
        }
        $accountOptions = [];

        foreach ($mappingLabels as $mappingKey => $label) {
            $accountOptions[$mappingKey] = $this->allowedAccounts($mappingKey)
                ->orderBy('kode_akun')
                ->get();
        }

        return view('account_mappings.index', compact('mappingLabels', 'mappingDescriptions', 'mappingGroups', 'currentMappings', 'accountOptions'));
    }

    public function store(Request $request)
    {
        $mappingLabels = AccountMapping::mappingKeys();
        $rules = [];

        foreach ($mappingLabels as $mappingKey => $label) {
            $rules["mappings.{$mappingKey}"] = [
                'nullable',
                'integer',
                Rule::exists('coas', 'id'),
            ];
        }

        $validated = $request->validate($rules);

        foreach ($mappingLabels as $mappingKey => $label) {
            $accountId = $validated['mappings'][$mappingKey] ?? null;

            if ($accountId && ! $this->allowedAccounts($mappingKey)->where('id', $accountId)->exists()) {
                throw ValidationException::withMessages([
                    "mappings.{$mappingKey}" => 'Akun tidak valid untuk kategori mapping ini.',
                ]);
            }

            if ($accountId) {
                AccountMapping::updateOrCreate(
                    ['mapping_key' => $mappingKey],
                    ['account_id' => $accountId]
                );
            } else {
                AccountMapping::where('mapping_key', $mappingKey)->delete();
            }
        }

        AccountMap::refreshAll();

        return redirect()
            ->route('account-mappings.index')
            ->with('success', 'Mapping akun berhasil disimpan.');
    }

    private function allowedAccounts(string $mappingKey)
    {
        return match ($mappingKey) {
            'sales_revenue_eks', 'sales_revenue_ren', 'sales_revenue_cold_room' => Coa::active()->posting()->where('tipe', 'Pendapatan'),
            'receivable_transfer_bank' => Coa::active()->posting()->where('tipe', 'Aset')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Bank%')
                        ->orWhere('kategori', 'like', '%Bank%');
                }),
            'receivable_pph23' => Coa::active()->posting()->where('tipe', 'Aset')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%PPh%23%')
                        ->orWhere('nama_akun', 'like', '%PPh Pasal 23%');
                }),
            'receivable_payment_loss' => Coa::active()->posting()->where('tipe', 'Beban')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Kerugian%Piutang%')
                        ->orWhere('nama_akun', 'like', '%Potongan%Piutang%');
                }),
            'receivable_bank_admin' => Coa::active()->posting()->where('tipe', 'Beban')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Administrasi%Bank%')
                        ->orWhere('nama_akun', 'like', '%Admin%Bank%');
                }),
            'retained_earnings' => Coa::active()->posting()->where('tipe', 'Ekuitas')->where('saldo_normal', 'Kredit'),
            'current_year_profit' => Coa::active()->posting()->where('tipe', 'Ekuitas')->where('saldo_normal', 'Kredit'),
            'cash_main' => Coa::active()->posting()->where('tipe', 'Aset')
                ->where(function ($query) {
                    $query->where('kategori', 'like', '%Kas%')
                        ->orWhere('kategori', 'like', '%Bank%');
                }),
            'accounts_payable' => Coa::active()->posting()->where('tipe', 'Kewajiban')
                ->where(function ($query) {
                    $query->where('kategori', 'like', '%Hutang%')
                        ->orWhere('nama_akun', 'like', '%Hutang%')
                        ->orWhere('kategori', 'like', '%Utang%')
                        ->orWhere('nama_akun', 'like', '%Utang%');
                }),
            'input_tax' => Coa::active()->posting()->where('tipe', 'Aset')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Pajak Masukan%')
                        ->orWhere('nama_akun', 'like', '%PPN Masukan%')
                        ->orWhere('kategori', 'like', '%Pajak Masukan%');
                }),
            'output_tax' => Coa::active()->posting()->where('tipe', 'Kewajiban')
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Pajak Keluaran%')
                        ->orWhere('nama_akun', 'like', '%PPN Keluaran%')
                        ->orWhere('kategori', 'like', '%Pajak Keluaran%');
                }),
            'equity_capital' => Coa::active()->posting()->where('tipe', 'Ekuitas')
                ->where(function ($query) {
                    $query->where('kategori', 'like', '%Modal%')
                        ->orWhere('nama_akun', 'like', '%Modal%');
                }),
            'prive' => Coa::active()->posting()->where(function ($query) {
                $query->where('kategori', 'like', '%Prive%')
                    ->orWhere('nama_akun', 'like', '%Prive%')
                    ->orWhere('kategori', 'like', '%Dividen%')
                    ->orWhere('nama_akun', 'like', '%Dividen%')
                    ->orWhere('kategori', 'like', '%Deviden%')
                    ->orWhere('nama_akun', 'like', '%Deviden%');
            }),
            default => Coa::active()->posting(),
        };
    }
}
