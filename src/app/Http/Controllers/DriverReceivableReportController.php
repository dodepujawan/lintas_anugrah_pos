<?php

namespace App\Http\Controllers;

use App\Exports\DriverReceivableReportExport;
use App\Models\Driver;
use App\Models\DriverReceivable;
use App\Models\DriverReceivablePayment;
use App\Services\FastTablePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverReceivableReportController extends Controller
{
    public function __construct(private readonly FastTablePdfService $fastTablePdf) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->query($filters)
            ->when($filters['section'] === 'penambahan', fn ($query) => $query->where('origin', 'TRANSACTION'));
        $summary = $this->summary($query, $filters['as_of']);
        $rows = $query->paginate(50)->withQueryString();
        $paymentQuery = $this->paymentQuery($filters);
        $payments = $paymentQuery->paginate(50, ['*'], 'payment_page')->withQueryString();
        $paymentSummary = $this->paymentSummary($this->paymentQuery($filters));

        return view('driver-receivable-report.index', [
            'rows' => $rows,
            'drivers' => Driver::orderBy('code')->get(),
            'summary' => $summary,
            'payments' => $payments,
            'paymentSummary' => $paymentSummary,
            'printMode' => false,
        ] + $filters);
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        if ($filters['section'] === 'pembayaran') {
            return $this->paymentPdf($filters, $dotMatrix);
        }

        $rows = $this->query($filters)
            ->when($filters['section'] === 'penambahan', fn ($query) => $query->where('origin', 'TRANSACTION'))
            ->get();
        $summary = $this->summary($this->query($filters), $filters['as_of']);
        $asOf = Carbon::parse($filters['as_of']);
        $selectedDriver = $filters['driver_id']
            ? Driver::find($filters['driver_id'])
            : null;

        $tableRows = $rows->map(function (DriverReceivable $row): array {
            return [
                $row->date?->format('d/m/Y') ?? '-',
                (string) $row->number,
                trim(($row->driver?->code ?? '').' - '.($row->driver?->name ?? ''), ' -'),
                $row->due_date?->format('d/m/Y') ?? '-',
                number_format((float) $row->amount, 2, ',', '.'),
                number_format((float) $row->paid, 2, ',', '.'),
                number_format((float) $row->balance, 2, ',', '.'),
            ];
        })->all();

        $tableRows[] = [
            '', 'TOTAL', '', '',
            number_format($summary['amount'], 2, ',', '.'),
            number_format($summary['paid'], 2, ',', '.'),
            number_format($summary['balance'], 2, ',', '.'),
        ];

        $period = ($filters['date_from'] ? Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal')
            .' s.d. '.($filters['date_to'] ? Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Posisi');
        $driverLabel = $selectedDriver ? $selectedDriver->code.' - '.$selectedDriver->name : 'Semua Sopir';

        return $this->fastTablePdf->stream([
            'title' => $filters['section'] === 'penambahan' ? 'LAPORAN PENAMBAHAN PIUTANG SOPIR' : 'LAPORAN PIUTANG SOPIR',
            'subtitles' => [
                'Posisi '.$asOf->format('d/m/Y').' | Periode '.$period,
                $driverLabel.' | Status '.$filters['status'].' | Asal '.$filters['origin'],
                'Data '.number_format($summary['count'], 0, ',', '.').' | Saldo '.number_format($summary['balance'], 2, ',', '.').' | Jatuh Tempo '.number_format($summary['overdue'], 2, ',', '.'),
            ],
            'headings' => ['Tanggal', 'Nomor', 'Sopir', 'J. Tempo', 'Nilai', 'Dibayar', 'Saldo'],
            'columns' => [50, 105, 145, 54, 70, 70, 70],
            'rows' => $tableRows,
            'numeric' => [4, 5, 6],
            'bold_columns' => [6],
            'wrap_columns' => [1, 2],
            'font_size' => $dotMatrix ? 7.5 : 8.5,
            'row_height' => 14,
            'rows_per_page' => 42,
            'dot_matrix' => $dotMatrix,
            'filename' => 'laporan_'.$filters['section'].'_piutang_sopir_'.($dotMatrix ? 'dot_matrix_' : 'standard_').$filters['as_of'].'.pdf',
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)->get();

        return Excel::download(new DriverReceivableReportExport($rows), 'laporan_piutang_sopir_'.$filters['as_of'].'.xlsx');
    }

    private function summary($query, string $asOf): array
    {
        $totals = (clone $query)->reorder()->selectRaw(
            'COUNT(*) as aggregate_count, COALESCE(SUM(amount),0) as aggregate_amount, COALESCE(SUM(paid),0) as aggregate_paid, COALESCE(SUM(balance),0) as aggregate_balance'
        )->first();
        $overdue = (clone $query)->reorder()
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $asOf)
            ->sum('balance');

        return [
            'count' => (int) ($totals->aggregate_count ?? 0),
            'amount' => (float) ($totals->aggregate_amount ?? 0),
            'paid' => (float) ($totals->aggregate_paid ?? 0),
            'balance' => (float) ($totals->aggregate_balance ?? 0),
            'overdue' => (float) $overdue,
        ];
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'as_of' => ['nullable', 'date'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['nullable', 'in:ALL,OPEN,LUNAS,OVERDUE'],
            'origin' => ['nullable', 'in:ALL,OPENING,TRANSACTION'],
            'search' => ['nullable', 'string', 'max:100'],
            'section' => ['nullable', 'in:saldo,penambahan,pembayaran'],
        ]);

        return [
            'date_from' => $validated['date_from'] ?? '',
            'date_to' => $validated['date_to'] ?? '',
            'as_of' => $validated['as_of'] ?? now()->toDateString(),
            'driver_id' => $validated['driver_id'] ?? '',
            'status' => $validated['status'] ?? 'ALL',
            'origin' => $validated['origin'] ?? 'ALL',
            'search' => trim($validated['search'] ?? ''),
            'section' => $validated['section'] ?? 'saldo',
        ];
    }

    private function query(array $filters)
    {
        return DriverReceivable::with('driver')
            ->when($filters['date_from'], fn ($query) => $query->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('date', '<=', $filters['date_to']))
            ->when($filters['driver_id'], fn ($query) => $query->where('driver_id', $filters['driver_id']))
            ->when($filters['origin'] !== 'ALL', fn ($query) => $query->where('origin', $filters['origin']))
            ->when($filters['status'] === 'OPEN', fn ($query) => $query->where('balance', '>', 0))
            ->when($filters['status'] === 'LUNAS', fn ($query) => $query->where('balance', '<=', 0))
            ->when($filters['status'] === 'OVERDUE', fn ($query) => $query->where('balance', '>', 0)->whereDate('due_date', '<', $filters['as_of']))
            ->when($filters['search'], function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(fn ($nested) => $nested
                    ->where('number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('driver', fn ($driver) => $driver
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")));
            })
            ->orderBy('driver_id')
            ->orderBy('date')
            ->orderBy('number');
    }

    private function paymentQuery(array $filters)
    {
        return DriverReceivablePayment::with(['receivable.driver', 'journal'])
            ->where('status', 'POSTED')
            ->when($filters['date_from'], fn ($query) => $query->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('date', '<=', $filters['date_to']))
            ->when($filters['driver_id'], fn ($query) => $query->whereHas('receivable', fn ($receivable) => $receivable->where('driver_id', $filters['driver_id'])))
            ->when($filters['search'], function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(fn ($nested) => $nested
                    ->where('number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('method', 'like', "%{$search}%")
                    ->orWhereHas('receivable', fn ($receivable) => $receivable
                        ->where('number', 'like', "%{$search}%")
                        ->orWhereHas('driver', fn ($driver) => $driver
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))));
            })
            ->orderBy('date')
            ->orderBy('number');
    }

    private function paymentSummary($query): array
    {
        $totals = (clone $query)->reorder()->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(amount),0) as aggregate_amount')->first();

        return [
            'count' => (int) ($totals->aggregate_count ?? 0),
            'amount' => (float) ($totals->aggregate_amount ?? 0),
        ];
    }

    private function paymentPdf(array $filters, bool $dotMatrix)
    {
        $rows = $this->paymentQuery($filters)->get();
        $summary = $this->paymentSummary($this->paymentQuery($filters));
        $selectedDriver = $filters['driver_id'] ? Driver::find($filters['driver_id']) : null;
        $period = ($filters['date_from'] ? Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal')
            .' s.d. '.($filters['date_to'] ? Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Posisi');

        $tableRows = $rows->map(fn (DriverReceivablePayment $payment): array => [
            $payment->date?->format('d/m/Y') ?? '-',
            (string) $payment->number,
            (string) ($payment->receivable?->number ?? '-'),
            trim(($payment->receivable?->driver?->code ?? '').' - '.($payment->receivable?->driver?->name ?? ''), ' -'),
            (string) ($payment->journal?->no_jurnal ?? '-'),
            number_format((float) $payment->amount, 2, ',', '.'),
        ])->all();

        $tableRows[] = ['', 'TOTAL', '', '', '', number_format($summary['amount'], 2, ',', '.')];

        return $this->fastTablePdf->stream([
            'title' => 'LAPORAN PEMBAYARAN SOPIR',
            'subtitles' => [
                'Periode '.$period,
                ($selectedDriver ? $selectedDriver->code.' - '.$selectedDriver->name : 'Semua Sopir').' | Transaksi '.number_format($summary['count'], 0, ',', '.'),
            ],
            'headings' => ['Tanggal', 'No. Bayar', 'No. Piutang', 'Sopir', 'Jurnal', 'Jumlah'],
            'columns' => [48, 78, 90, 170, 88, 90],
            'rows' => $tableRows,
            'numeric' => [5],
            'bold_columns' => [5],
            'wrap_columns' => [2, 3],
            'font_size' => $dotMatrix ? 7.5 : 8.5,
            'row_height' => 14,
            'dot_matrix' => $dotMatrix,
            'filename' => 'laporan_pembayaran_sopir_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
        ]);
    }
}
