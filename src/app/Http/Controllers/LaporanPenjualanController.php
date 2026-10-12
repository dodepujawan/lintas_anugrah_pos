<?php

namespace App\Http\Controllers;

use App\Exports\PenjualanReportExport;
use App\Models\Arh;
use App\Services\FastTablePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPenjualanController extends Controller
{
    private const JENIS = [
        'EKS' => 'Ekspedisi',
        'REN' => 'Rental',
        'CO' => 'Cold Room',
    ];

    public function __construct(private readonly FastTablePdfService $fastTablePdf) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $baseQuery = $this->query($filters);
        $summary = $this->summary($baseQuery);
        $jenisTotals = $this->jenisTotals($this->query($filters));
        $rows = (clone $baseQuery)
            ->orderBy('arh.TGLFAKTUR')
            ->orderBy('arh.NOFAKTUR')
            ->paginate(50)
            ->withQueryString();

        $this->attachCustomerNames($rows->getCollection());

        return view('laporan.penjualan', [
            'rows' => $rows,
            'summary' => $summary,
            'jenisTotals' => $jenisTotals,
            'jenisOptions' => self::JENIS,
            'customerMode' => false,
        ] + $filters);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)
            ->orderBy('arh.TGLFAKTUR')
            ->orderBy('arh.NOFAKTUR')
            ->get();
        $this->attachCustomerNames($rows);

        $filename = 'laporan_penjualan_'
            .($filters['date_from'] ?: 'awal').'_'
            .($filters['date_to'] ?: 'akhir').'.xlsx';

        return Excel::download(new PenjualanReportExport($rows, $filters, self::JENIS), $filename);
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $rows = $this->query($filters)
            ->orderBy('arh.TGLFAKTUR')
            ->orderBy('arh.NOFAKTUR')
            ->get();
        $this->attachCustomerNames($rows);
        $summary = $this->summary($this->query($filters));

        $tableRows = $rows->map(function (Arh $row): array {
            return [
                $row->TGLFAKTUR?->format('d/m/Y') ?? '-',
                (string) $row->NOFAKTUR,
                trim((string) $row->CUSTOMER.' - '.($row->customer_name ?? ''), ' -'),
                $this->jenisLabel($this->resolveJenis($row->SOURCE, $row->KETERANGAN)),
                number_format((float) $row->PIUTANG, 2, ',', '.'),
                number_format((float) $row->BAYAR, 2, ',', '.'),
                number_format((float) $row->SALDO, 2, ',', '.'),
            ];
        })->all();

        $tableRows[] = [
            '', 'TOTAL', '', '',
            number_format($summary['piutang'], 2, ',', '.'),
            number_format($summary['bayar'], 2, ',', '.'),
            number_format($summary['saldo'], 2, ',', '.'),
        ];

        $period = ($filters['date_from'] ? Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal')
            .' s.d. '.($filters['date_to'] ? Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Akhir');
        $jenisLabel = $filters['jenis'] !== ''
            ? ($this->jenisLabel($filters['jenis']))
            : 'Semua Jenis';
        $customerLabel = $filters['customer'] !== '' ? $filters['customer'] : 'Semua Customer';

        return $this->fastTablePdf->stream([
            'title' => 'LAPORAN PENJUALAN',
            'subtitles' => [
                'Periode '.$period,
                $customerLabel.' | '.$jenisLabel,
                'Data '.number_format($summary['count'], 0, ',', '.')
                    .' | Nilai '.number_format($summary['piutang'], 2, ',', '.')
                    .' | Terbayar '.number_format($summary['bayar'], 2, ',', '.')
                    .' | Saldo '.number_format($summary['saldo'], 2, ',', '.'),
            ],
            'headings' => ['Tanggal', 'No. Faktur', 'Customer', 'Jenis', 'Nilai', 'Terbayar', 'Saldo'],
            'columns' => [48, 95, 150, 55, 70, 70, 70],
            'rows' => $tableRows,
            'numeric' => [4, 5, 6],
            'bold_columns' => [6],
            'wrap_columns' => [1, 2],
            'font_size' => $dotMatrix ? 7.5 : 8.5,
            'row_height' => 14,
            'rows_per_page' => 42,
            'dot_matrix' => $dotMatrix,
            'filename' => 'laporan_penjualan_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'customer' => ['nullable', 'string', 'max:30'],
            'jenis' => ['nullable', 'in:EKS,REN,CO'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to'] ?? now()->toDateString(),
            'customer' => strtoupper(trim((string) ($validated['customer'] ?? ''))),
            'jenis' => strtoupper(trim((string) ($validated['jenis'] ?? ''))),
            'search' => trim((string) ($validated['search'] ?? '')),
        ];
    }

    private function query(array $filters)
    {
        return Arh::query()
            ->from('arh')
            ->where(function ($query): void {
                $query->whereNull('SOURCE')
                    ->orWhere('SOURCE', 'not like', 'SALDO_AWAL_%');
            })
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('arh.TGLFAKTUR', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('arh.TGLFAKTUR', '<=', $filters['date_to']))
            ->when($filters['customer'] !== '', fn ($query) => $query->where('arh.CUSTOMER', $filters['customer']))
            ->when($filters['jenis'] !== '', function ($query) use ($filters): void {
                $this->applyJenisFilter($query, $filters['jenis']);
            })
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = '%'.$filters['search'].'%';
                $query->where(function ($filter) use ($search): void {
                    $filter->where('arh.NOFAKTUR', 'like', $search)
                        ->orWhere('arh.CUSTOMER', 'like', $search)
                        ->orWhere('arh.KETERANGAN', 'like', $search)
                        ->orWhere('arh.NOJURNAL', 'like', $search)
                        ->orWhereExists(function ($customer) use ($search): void {
                            $customer->selectRaw('1')
                                ->from('mcustomer')
                                ->whereColumn('mcustomer.CUSTOMER', 'arh.CUSTOMER')
                                ->where('mcustomer.NAMACUST', 'like', $search);
                        });
                });
            });
    }

    private function applyJenisFilter($query, string $jenis): void
    {
        $aliases = $jenis === 'EKS' ? ['EKS', 'EXSPEDISI'] : [$jenis];
        $syncSources = collect($aliases)->map(fn (string $code) => 'SYNC_'.$code)->all();
        $keywords = match ($jenis) {
            'EKS' => ['Pendapatan Ekspedisi', 'Ekspedisi'],
            'REN' => ['Pendapatan Sewa Unit Armada', 'Rental', 'Sewa Unit Armada'],
            'CO' => ['Pendapatan Sewa Cold Room', 'Cold Room'],
            default => [],
        };

        $query->where(function ($filter) use ($syncSources, $keywords): void {
            $filter->whereIn('SOURCE', $syncSources);
            foreach ($keywords as $keyword) {
                $filter->orWhere('KETERANGAN', 'like', '%'.$keyword.'%');
            }
        });
    }

    private function summary($query): array
    {
        $totals = (clone $query)->reorder()->selectRaw(
            'COUNT(*) as aggregate_count,
             COALESCE(SUM(PIUTANG),0) as aggregate_piutang,
             COALESCE(SUM(BAYAR),0) as aggregate_bayar,
             COALESCE(SUM(SALDO),0) as aggregate_saldo'
        )->first();

        return [
            'count' => (int) ($totals->aggregate_count ?? 0),
            'piutang' => (float) ($totals->aggregate_piutang ?? 0),
            'bayar' => (float) ($totals->aggregate_bayar ?? 0),
            'saldo' => (float) ($totals->aggregate_saldo ?? 0),
        ];
    }

    private function jenisTotals($query): array
    {
        $rows = (clone $query)->reorder()->get(['SOURCE', 'KETERANGAN', 'PIUTANG']);
        $totals = [
            'EKS' => 0.0,
            'REN' => 0.0,
            'CO' => 0.0,
            'LAINNYA' => 0.0,
        ];

        foreach ($rows as $row) {
            $jenis = $this->resolveJenis($row->SOURCE, $row->KETERANGAN);
            $key = array_key_exists($jenis, $totals) ? $jenis : 'LAINNYA';
            $totals[$key] += (float) $row->PIUTANG;
        }

        return $totals;
    }

    private function attachCustomerNames($rows): void
    {
        $collection = collect($rows);
        $codes = $collection->pluck('CUSTOMER')->filter()->map(fn ($code) => strtoupper(trim((string) $code)))->unique()->values();
        $journalNumbers = $collection->pluck('NOJURNAL')->filter()->map(fn ($no) => trim((string) $no))->unique()->values();

        $names = $codes->isEmpty()
            ? collect()
            : DB::table('mcustomer')->whereIn('CUSTOMER', $codes)->pluck('NAMACUST', 'CUSTOMER');

        $journalIds = $journalNumbers->isEmpty()
            ? collect()
            : DB::table('jurnals')->whereIn('no_jurnal', $journalNumbers)->pluck('id', 'no_jurnal');

        foreach ($rows as $row) {
            $code = strtoupper(trim((string) $row->CUSTOMER));
            $journalNo = trim((string) ($row->NOJURNAL ?? ''));
            $row->setAttribute('customer_name', (string) ($names[$code] ?? ''));
            $row->setAttribute('jenis_code', $this->resolveJenis($row->SOURCE, $row->KETERANGAN));
            $row->setAttribute('jenis_label', $this->jenisLabel($row->jenis_code));
            $row->setAttribute('journal_id', $journalNo !== '' ? ($journalIds[$journalNo] ?? null) : null);
        }
    }

    private function resolveJenis(?string $source, ?string $keterangan): string
    {
        $source = strtoupper(trim((string) $source));
        if (str_starts_with($source, 'SYNC_')) {
            $code = substr($source, 5);
            if ($code === 'EXSPEDISI') {
                return 'EKS';
            }
            if (array_key_exists($code, self::JENIS)) {
                return $code;
            }
        }

        $ket = strtoupper((string) $keterangan);
        if (str_contains($ket, 'COLD ROOM') || str_contains($ket, ' COLD ')) {
            return 'CO';
        }
        if (str_contains($ket, 'SEWA UNIT ARMADA') || str_contains($ket, 'RENTAL')) {
            return 'REN';
        }
        if (str_contains($ket, 'EKSPEDISI')) {
            return 'EKS';
        }

        return 'LAINNYA';
    }

    private function jenisLabel(string $code): string
    {
        return self::JENIS[$code] ?? 'Lainnya';
    }
}
