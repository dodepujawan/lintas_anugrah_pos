<?php

namespace App\Http\Controllers;

use App\Exports\ImportTemplateExport;
use App\Services\ImportDataService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataController extends Controller
{
    public function __construct(private readonly ImportDataService $importService) {}

    public function index()
    {
        return view('import-data.index', [
            'templates' => ImportTemplateExport::templates(),
            'defaultCutoverDate' => now()->startOfMonth()->toDateString(),
        ]);
    }

    public function template(string $type)
    {
        abort_unless(array_key_exists($type, ImportTemplateExport::templates()), 404);

        return Excel::download(
            new ImportTemplateExport($type),
            'template_'.$type.'.xlsx'
        );
    }

    public function store(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, ImportTemplateExport::templates()), 404);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'cutover_date' => ['required', 'date'],
        ]);

        try {
            $count = $this->importService->import(
                $type,
                $validated['file'],
                $validated['cutover_date'],
                auth()->user()
            );

            $isReceivable = in_array($type, ['penjualan', 'saldo_piutang'], true);
            $isTransaction = in_array($type, ['penjualan', 'hutang'], true);

            return redirect()->route('import-data.index')
                ->with('success', "{$count} baris berhasil diimport.")
                ->with('import_result', [
                    'type' => $type,
                    'count' => $count,
                    'list_label' => $isReceivable ? 'Lihat Daftar Piutang' : 'Lihat Daftar Hutang',
                    'list_url' => $isReceivable ? route('piutang.index') : route('hutang.index'),
                    'report_label' => $isReceivable ? 'Buka Laporan Piutang' : 'Buka Laporan Hutang',
                    'report_url' => $isReceivable ? route('laporan.piutang') : route('laporan.hutang'),
                    'journal_url' => $isTransaction ? route('laporan.jurnal_umum', [
                        'jenis' => $type === 'penjualan' ? 'Penjualan' : 'Pembelian',
                    ]) : null,
                ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Import dibatalkan: '.$e->getMessage());
        }
    }
}
