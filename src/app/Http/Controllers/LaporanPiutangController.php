<?php

namespace App\Http\Controllers;

use App\Exports\PiutangReportExport;
use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Barh;
use App\Models\Bard;
use App\Services\JournalQueryService;
use App\Services\FastTablePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPiutangController extends Controller
{
    public function __construct(
        private readonly JournalQueryService $journalQueryService,
        private readonly FastTablePdfService $fastTablePdf,
    ) {}

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($filters);

        // Perhitungan laporan tetap memakai seluruh data, sedangkan tabel di layar
        // dibatasi agar browser tidak merender ribuan baris sekaligus.
        $allInvoices = $report['invoices'];
        $report['invoiceTotals'] = [
            'count' => $allInvoices->count(),
            'invoice_amount' => $allInvoices->sum('invoice_amount'),
            'paid' => $allInvoices->sum('paid'),
            'balance' => $allInvoices->sum('balance'),
        ];

        $printMode = $request->boolean('print');
        $perPage = $printMode ? max($allInvoices->count(), 1) : 50;
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $report['invoices'] = (new LengthAwarePaginator(
            $allInvoices->forPage($page, $perPage)->values(),
            $allInvoices->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        ))->appends($request->except('page'));
        $this->attachPaymentDetailsToInvoiceRows($report['invoices']->getCollection());

        $report['invoiceCards'] = $filters['reportSection'] === 'kartu'
            ? $this->paginateInvoiceCards($allInvoices, $filters, $request, $printMode)
            : collect();

        $report['customerTotals'] = [
            'count' => $report['customers']->count(),
            'invoice_count' => $report['customers']->sum('invoice_count'),
            'invoice_amount' => $report['customers']->sum('invoice_amount'),
            'paid' => $report['customers']->sum('paid'),
            'balance' => $report['customers']->sum('balance'),
        ];
        $report['periodInvoiceTotals'] = [
            'count' => $report['periodInvoices']->count(),
            'invoice_amount' => $report['periodInvoices']->sum('invoice_amount'),
        ];
        $report['paymentTotals'] = [
            'count' => $report['payments']->count(),
            'amount' => $report['payments']->sum(fn (Barh $payment): float => (float) $payment->details->sum('NILAI_BAYAR')),
        ];
        $report['customers'] = $this->paginateCollection($report['customers'], $request, 'customer_page', 50);
        $report['periodInvoices'] = $this->paginateCollection($report['periodInvoices'], $request, 'addition_page', 50);
        $report['payments'] = $this->paginateCollection($report['payments'], $request, 'payment_page', 50);

        $report['agingDetailPaginator'] = null;
        $report['agingGroupTotals'] = collect();
        if ($filters['reportSection'] === 'aging' && $filters['agingDetail'] === 'all') {
            $report['agingGroupTotals'] = $report['agingGroups']->map(fn (Collection $rows): array => [
                'count' => $rows->count(),
                'invoice_amount' => $rows->sum('invoice_amount'),
                'paid' => $rows->sum('paid'),
                'balance' => $rows->sum('balance'),
            ]);
            $agingRows = collect(['belum_jatuh_tempo', '1_30', '31_60', '61_90', 'lebih_90'])
                ->flatMap(fn (string $key) => $report['agingGroups']->get($key, collect()))
                ->values();
            $agingPage = LengthAwarePaginator::resolveCurrentPage('aging_page');
            $report['agingDetailPaginator'] = (new LengthAwarePaginator(
                $agingRows->forPage($agingPage, 100)->values(),
                $agingRows->count(),
                100,
                $agingPage,
                ['path' => $request->url(), 'pageName' => 'aging_page']
            ))->appends($request->except('aging_page'));
            $report['agingGroups'] = $report['agingDetailPaginator']->getCollection()
                ->groupBy(fn (array $row): string => $this->agingKey($row));
        }

        return view('laporan.piutang', array_merge($report, $filters, compact('printMode')));
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($filters);
        $section = $filters['reportSection'];
        return Excel::download(
            new PiutangReportExport($report, $filters),
            'laporan_piutang_'.$section.'_'.$filters['tanggalPosisi'].'.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $startedAt = microtime(true);
        $filters = $this->validatedFilters($request);
        $section = $filters['reportSection'];
        $report = $this->buildReport($filters, $section);
        $dataReadyAt = microtime(true);
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $report['invoiceTotals'] = [
            'count' => $report['invoices']->count(),
            'invoice_amount' => $report['invoices']->sum('invoice_amount'),
            'paid' => $report['invoices']->sum('paid'),
            'balance' => $report['invoices']->sum('balance'),
        ];

        if ($section === 'invoice') {
            return $this->renderInvoiceCanvasPdf($report, $filters, $dotMatrix, $startedAt, $dataReadyAt);
        }

        if ($section === 'kartu') {
            return $this->renderKartuFakturPdf($report, $filters, $dotMatrix, $startedAt, $dataReadyAt);
        }

        if ($section === 'aging' && $filters['agingDetail'] === 'all') {
            $labels = [
                'belum_jatuh_tempo' => 'Belum JT',
                '1_30' => '1-30 Hari',
                '31_60' => '31-60 Hari',
                '61_90' => '61-90 Hari',
                'lebih_90' => '> 90 Hari',
            ];
            $rows = collect($labels)->flatMap(function (string $label, string $key) use ($report): array {
                $groupRows = $report['agingGroups']->get($key, collect())
                    ->sortBy(fn (array $row): string => trim((string) $row['invoice']->CUSTOMER).'|'.trim((string) $row['invoice']->NOFAKTUR))
                    ->values();

                $detailRows = collect();
                foreach ($groupRows->groupBy(fn (array $row): string => trim((string) $row['invoice']->CUSTOMER)) as $customer => $customerRows) {
                    foreach ($customerRows->sortBy(fn (array $row): string => trim((string) $row['invoice']->NOFAKTUR))->values() as $row) {
                        $customerName = (string) ($row['customer_name'] ?? '');
                        $customerLabel = (string) $row['invoice']->CUSTOMER;
                        if ($customerName !== '') {
                            $customerLabel .= ' - '.$customerName;
                        }

                        $detailRows->push([
                            $label,
                            (string) $row['invoice']->NOFAKTUR,
                            $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                            $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                            $customerLabel,
                            number_format((float) $row['invoice_amount'], 2, ',', '.'),
                            number_format((float) $row['paid'], 2, ',', '.'),
                            number_format((float) $row['balance'], 2, ',', '.'),
                        ]);
                    }

                    $detailRows->push([
                        'style' => 'subtotal_customer',
                        'cells' => [
                            '',
                            '',
                            '',
                            'SUB TTL',
                            $customer,
                            number_format((float) $customerRows->sum('invoice_amount'), 2, ',', '.'),
                            number_format((float) $customerRows->sum('paid'), 2, ',', '.'),
                            number_format((float) $customerRows->sum('balance'), 2, ',', '.'),
                        ],
                    ]);
                }

                return $detailRows->all();
            })->values()->all();
            $allAgingRows = $report['agingGroups']->flatten(1);
            $rows[] = [
                'style' => 'summary_header',
                'cells' => ['Subtotal Per Kategori Umur', '', '', '', '', '', '', ''],
            ];
            foreach ($labels as $key => $label) {
                $groupRows = $report['agingGroups']->get($key, collect());
                $rows[] = [
                    'style' => 'summary_row',
                    'cells' => [
                        $label,
                        $groupRows->count().' inv',
                        '',
                        '',
                        '',
                        number_format((float) $groupRows->sum('invoice_amount'), 2, ',', '.'),
                        number_format((float) $groupRows->sum('paid'), 2, ',', '.'),
                        number_format((float) $groupRows->sum('balance'), 2, ',', '.'),
                    ],
                ];
            }
            $rows[] = [
                'style' => 'summary_total',
                'cells' => [
                    'Total Semua Umur',
                    $allAgingRows->count().' inv',
                    '',
                    '',
                    '',
                    number_format((float) $allAgingRows->sum('invoice_amount'), 2, ',', '.'),
                    number_format((float) $allAgingRows->sum('paid'), 2, ',', '.'),
                    number_format((float) $allAgingRows->sum('balance'), 2, ',', '.'),
                ],
            ];

            return $this->fastTablePdf->stream([
                'title' => 'DETAIL UMUR PIUTANG',
                'subtitles' => ['Posisi '.date('d/m/Y', strtotime($filters['tanggalPosisi']))],
                'headings' => ['Umur', 'Nomor Faktur', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Nilai', 'Terbayar', 'Sisa'],
                'columns' => [44, 72, 46, 52, 148, 68, 66, 68],
                'rows' => $rows,
                'numeric' => [5, 6, 7],
                'bold_columns' => [5, 6, 7],
                'wrap_columns' => [4],
                'max_wrap_lines' => 6,
                'dot_matrix' => $dotMatrix,
                'font_size' => $dotMatrix ? 7.5 : 6.2,
                'row_height' => 21,
                'paper_orientation' => 'portrait',
                'margin' => 24,
                'text_width' => 160,
                'text_lines_per_page' => 58,
                'filename' => 'detail_umur_piutang_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
            ]);
        }

        if ($section === 'pelanggan') {
            $rows = $report['customers']->map(fn (array $row): array => [
                (string) $row['customer'], (string) ($row['customer_name'] ?: '-'), (string) $row['invoice_count'],
                number_format((float) $row['invoice_amount'], 2, ',', '.'), number_format((float) $row['paid'], 2, ',', '.'), number_format((float) $row['balance'], 2, ',', '.'),
            ])->all();
            return $this->fastTablePdf->stream([
                'title' => 'DAFTAR PELANGGAN PIUTANG', 'subtitles' => ['Posisi '.date('d/m/Y', strtotime($filters['tanggalPosisi']))],
                'headings' => ['Customer', 'Nama Customer', 'Invoice', 'Nilai', 'Terbayar', 'Saldo'], 'columns' => [118, 190, 45, 70, 70, 71],
                'rows' => $rows, 'numeric' => [2, 3, 4, 5], 'bold_columns' => [5], 'wrap_columns' => [0, 1], 'max_wrap_lines' => 4, 'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 7.5 : 8.2, 'row_height' => 13,
                'filename' => 'pelanggan_piutang_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
            ]);
        }

        if ($section === 'penambahan') {
            $rows = $report['periodInvoices']->map(fn (array $row): array => [
                (string) $row['invoice']->NOFAKTUR, $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-', $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                (string) $row['invoice']->CUSTOMER, (string) ($row['customer_name'] ?: '-'),
                number_format((float) $row['invoice_amount'], 2, ',', '.'),
            ])->all();
            return $this->fastTablePdf->stream([
                'title' => 'DAFTAR PENAMBAHAN PIUTANG', 'subtitles' => ['Periode '.date('d/m/Y', strtotime($filters['tanggalAwal'])).' - '.date('d/m/Y', strtotime($filters['tanggalAkhir']))],
                'headings' => ['Invoice', 'Tanggal', 'J.Tempo', 'Customer', 'Nama Customer', 'Nilai'], 'columns' => [75, 55, 55, 70, 220, 89],
                'rows' => $rows, 'numeric' => [5], 'bold_columns' => [5], 'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 7.5 : 9, 'row_height' => 14,
                'filename' => 'penambahan_piutang_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
            ]);
        }

        if ($section === 'pembayaran') {
            $rows = $report['payments']->map(fn (Barh $payment): array => [
                (string) $payment->NOBAYAR, $payment->TGLBAYAR?->format('d/m/Y') ?? '-', (string) $payment->CUSTOMER,
                (string) ($payment->customer_name ?: '-'), (string) ($payment->METODE_PEMBAYARAN ?? '-'),
                $payment->details->pluck('NOFAKTUR')->join(', '), number_format((float) $payment->details->sum('NILAI_BAYAR'), 2, ',', '.'),
            ])->all();
            return $this->fastTablePdf->stream([
                'title' => 'LAPORAN PEMBAYARAN PIUTANG', 'subtitles' => ['Periode '.date('d/m/Y', strtotime($filters['tanggalAwal'])).' - '.date('d/m/Y', strtotime($filters['tanggalAkhir']))],
                'headings' => ['No. Bayar', 'Tanggal', 'Customer', 'Nama Customer', 'Metode', 'Invoice', 'Jumlah'], 'columns' => [65, 45, 55, 145, 65, 115, 74],
                'rows' => $rows, 'numeric' => [6], 'bold_columns' => [6], 'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 7.5 : 9, 'row_height' => 14,
                'filename' => 'pembayaran_piutang_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
            ]);
        }

        $pdf = Pdf::loadView('laporan.piutang_pdf', array_merge($report, $filters, compact('section', 'dotMatrix')))
            // Letter portrait: 8,5 x 11 inci (72 pt per inci).
            ->setPaper([0, 0, 612, 792], 'portrait')
            ->setOption('isRemoteEnabled', false);

        $response = $pdf->stream('laporan_piutang_'.$section.'_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf');

        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Piutang-Data-Ms', (string) round(($dataReadyAt - $startedAt) * 1000))
            ->header('X-Piutang-Pdf-Ms', (string) round((microtime(true) - $dataReadyAt) * 1000));
    }

    private function paginateInvoiceCards(Collection $allInvoices, array $filters, Request $request, bool $printMode): LengthAwarePaginator
    {
        $cardSource = $this->filterInvoiceRowsByStatus($allInvoices, $filters['status']);
        $cardPage = LengthAwarePaginator::resolveCurrentPage('kartu_page');
        $cardPerPage = $printMode ? max($cardSource->count(), 1) : 20;
        $paginator = (new LengthAwarePaginator(
            $cardSource->forPage($cardPage, $cardPerPage)->values(),
            $cardSource->count(),
            $cardPerPage,
            $cardPage,
            ['path' => $request->url(), 'pageName' => 'kartu_page']
        ))->appends($request->except('kartu_page'));

        $this->attachPaymentDetailsToInvoiceRows($paginator->getCollection(), $filters['tanggalPosisi']);
        $paginator->getCollection()->transform(
            fn (array $row): array => $this->buildInvoiceCard($row, $filters['tanggalPosisi'])
        );

        return $paginator;
    }

    private function buildInvoiceCards(Collection $invoices, array $filters, ?int $limit = null): Collection
    {
        $rows = $this->filterInvoiceRowsByStatus($invoices, $filters['status'])->values();
        if ($limit !== null) {
            $rows = $rows->take($limit)->values();
        }

        $this->attachPaymentDetailsToInvoiceRows($rows, $filters['tanggalPosisi']);

        return $rows->map(
            fn (array $row): array => $this->buildInvoiceCard($row, $filters['tanggalPosisi'])
        )->values();
    }

    private function filterInvoiceRowsByStatus(Collection $rows, string $status): Collection
    {
        return $rows->filter(fn (array $row): bool => match ($status) {
            'open' => $row['balance'] > 0,
            'lunas' => $row['balance'] <= 0,
            'jatuh_tempo' => $row['balance'] > 0 && $row['days_overdue'] > 0,
            default => true,
        })->values();
    }

    private function renderKartuFakturPdf(array $report, array $filters, bool $dotMatrix, float $startedAt, float $dataReadyAt)
    {
        if (! empty($report['kartuNeedsFilter'])) {
            abort(422, 'Isi No. Faktur dan/atau Pelanggan sebelum mencetak Kartu Faktur.');
        }

        $invoiceCards = $this->buildInvoiceCards($report['invoices'], $filters, 100);
        if ($invoiceCards->isEmpty()) {
            abort(404, 'Tidak ada faktur yang cocok untuk dicetak.');
        }

        $pdf = Pdf::loadView('laporan.piutang_kartu_pdf', array_merge($filters, [
            'invoiceCards' => $invoiceCards,
            'dotMatrix' => $dotMatrix,
        ]))
            ->setPaper([0, 0, 612, 792], 'portrait')
            ->setOption('isRemoteEnabled', false);

        return $pdf->stream('kartu_piutang_faktur_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Piutang-Data-Ms', (string) round(($dataReadyAt - $startedAt) * 1000))
            ->header('X-Piutang-Pdf-Ms', (string) round((microtime(true) - $dataReadyAt) * 1000));
    }

    private function renderInvoiceCanvasPdf(array $report, array $filters, bool $dotMatrix, float $startedAt, float $dataReadyAt)
    {
        if ($dotMatrix) {
            $rows = $report['invoices']->map(fn (array $row): array => [
                (string) $row['invoice']->NOFAKTUR,
                $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                (string) $row['invoice']->CUSTOMER,
                (string) ($row['customer_name'] ?: '-'),
                number_format((float) $row['invoice_amount'], 2, ',', '.'),
                number_format((float) $row['paid'], 2, ',', '.'),
                number_format((float) $row['balance'], 2, ',', '.'),
            ])->all();

            return $this->fastTablePdf->stream([
                'title' => 'LAPORAN PIUTANG PER FAKTUR',
                'subtitles' => ['Posisi '.date('d/m/Y', strtotime($filters['tanggalPosisi']))],
                'headings' => ['Invoice', 'Tanggal', 'J.Tempo', 'Customer', 'Nama Customer', 'Nilai', 'Terbayar', 'Sisa'],
                'columns' => [16, 11, 11, 12, 24, 15, 15, 15],
                'rows' => $rows,
                'numeric' => [5, 6, 7],
                'bold_columns' => [7],
                'dot_matrix' => true,
                'text_width' => 120,
                'text_lines_per_page' => 60,
                'filename' => 'laporan_piutang_invoice_dot_matrix_'.now()->format('Ymd_His').'.txt',
            ]);
        }

        $pdf = Pdf::loadHTML('<!doctype html><html><body></body></html>')
            ->setPaper([0, 0, 612, 792], 'portrait');
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $fonts = $dompdf->getFontMetrics();
        $normal = $fonts->getFont($dotMatrix ? 'Courier' : 'Helvetica', 'normal');
        $bold = $fonts->getFont($dotMatrix ? 'Courier' : 'Helvetica', 'bold');
        $black = [0, 0, 0];
        $gray = $dotMatrix ? [1, 1, 1] : [0.90, 0.90, 0.90];
        $margin = 24.0;
        $tableWidth = 564.0;
        $bodyFontSize = 6.2;
        $lineHeight = 7.4;
        $rowHeight = 14.0;
        $headerHeight = 15.0;
        $columns = [58, 43, 43, 128, 134, 53, 52, 53];
        $headings = ['Invoice', 'Tanggal', 'J.Tempo', 'Customer', 'Nama Customer', 'Nilai', 'Terbayar', 'Sisa'];
        $centerText = function (string $text, float $y, $font, float $size) use ($canvas, $fonts, $black): void {
            $canvas->text((612 - $fonts->getTextWidth($text, $font, $size)) / 2, $y, $text, $font, $size, $black);
        };
        $wrapText = function (string $text, float $width, float $size, int $maxLines = 4) use ($fonts, $normal): array {
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ($text === '') {
                return [''];
            }

            $splitLongWord = function (string $word) use ($fonts, $normal, $width, $size): array {
                $chunks = [];
                $chunk = '';
                $length = mb_strlen($word);

                for ($i = 0; $i < $length; $i++) {
                    $candidate = $chunk.mb_substr($word, $i, 1);
                    if ($chunk !== '' && $fonts->getTextWidth($candidate, $normal, $size) > $width - 16) {
                        $chunks[] = $chunk;
                        $chunk = mb_substr($word, $i, 1);
                        continue;
                    }

                    $chunk = $candidate;
                }

                if ($chunk !== '') {
                    $chunks[] = $chunk;
                }

                return $chunks ?: [''];
            };

            $words = preg_split('/\s+/', $text) ?: [];
            $lines = [];
            $line = '';

            foreach ($words as $word) {
                if ($fonts->getTextWidth($word, $normal, $size) > $width - 16) {
                    if ($line !== '') {
                        $lines[] = $line;
                        $line = '';
                    }

                    foreach ($splitLongWord($word) as $chunk) {
                        $lines[] = $chunk;
                        if (count($lines) >= $maxLines) {
                            break 2;
                        }
                    }

                    continue;
                }

                $candidate = $line === '' ? $word : $line.' '.$word;
                if ($fonts->getTextWidth($candidate, $normal, $size) <= $width - 16) {
                    $line = $candidate;
                    continue;
                }

                if ($line !== '') {
                    $lines[] = $line;
                    $line = $word;
                }

                if (count($lines) >= $maxLines) {
                    break;
                }
            }

            if ($line !== '' && count($lines) < $maxLines) {
                $lines[] = $line;
            }

            return array_slice($lines ?: [''], 0, $maxLines);
        };
        $rowVisualHeight = function (array $row) use ($columns, $wrapText, $rowHeight, $bodyFontSize, $lineHeight): float {
            $size = $bodyFontSize;
            $customerLines = count($wrapText((string) $row['invoice']->CUSTOMER, $columns[3], $size));
            $nameLines = count($wrapText((string) ($row['customer_name'] ?: '-'), $columns[4], $size));
            $maxLines = max(1, $customerLines, $nameLines);

            return max($rowHeight, 5.0 + ($maxLines * $lineHeight));
        };
        $pages = [];
        $currentPage = [];
        $currentHeight = $headerHeight;
        $top = 68.0;
        $bottomLimit = 760.0;
        foreach ($report['invoices']->all() as $row) {
            $visualHeight = $rowVisualHeight($row);
            if ($currentPage !== [] && $top + $currentHeight + $visualHeight > $bottomLimit) {
                $pages[] = $currentPage;
                $currentPage = [];
                $currentHeight = $headerHeight;
            }

            $currentPage[] = $row;
            $currentHeight += $visualHeight;
        }
        $pages[] = $currentPage;
        if ($pages === [[]]) $pages = [[]];
        $drawText = function (string $text, float $x, float $y, float $width, bool $right = false, bool $isBold = false, bool $wrap = false) use ($canvas, $fonts, $normal, $bold, $black, $wrapText, $bodyFontSize, $lineHeight): void {
            $font = $isBold ? $bold : $normal;
            $size = $bodyFontSize;
            if ($wrap && ! $right) {
                foreach ($wrapText($text, $width, $size) as $lineIndex => $line) {
                    $canvas->text($x + 2.0, $y + 2.4 + ($lineIndex * $lineHeight), $line, $font, $size, $black);
                }
                return;
            }
            $text = mb_strimwidth($text, 0, max(1, (int) floor($width / 3.1)), '');
            $textX = $right ? $x + $width - 2.5 - $fonts->getTextWidth($text, $font, $size) : $x + 2.5;
            $canvas->text(max($x + 1, $textX), $y + 2.2, $text, $font, $size, $black);
        };

        foreach ($pages as $pageIndex => $pageRows) {
            if ($pageIndex > 0) $canvas->new_page();
            $centerText(strtoupper((string) config('app.company_name', config('app.name'))), 24, $bold, 11);
            $centerText('LAPORAN PIUTANG PER FAKTUR', 38, $bold, 10);
            $centerText('Posisi '.date('d/m/Y', strtotime($filters['tanggalPosisi'])), 51, $normal, 7);

            $top = 68.0;
            $canvas->filled_rectangle($margin, $top, $tableWidth, $headerHeight, $gray);
            $tableHeight = $headerHeight + array_sum(array_map(fn (array $row): float => $rowVisualHeight($row), $pageRows));
            $canvas->rectangle($margin, $top, $tableWidth, $tableHeight, $black, 0.6);
            $x = $margin;
            foreach ($columns as $index => $width) {
                if ($index > 0) $canvas->line($x, $top, $x, $top + $tableHeight, $black, 0.35);
                $drawText($headings[$index], $x, $top + 2.2, $width, $index >= 5, true);
                $x += $width;
            }
            $canvas->line($margin, $top + $headerHeight, $margin + $tableWidth, $top + $headerHeight, $black, 0.6);

            $y = $top + $headerHeight;
            foreach ($pageRows as $row) {
                $visualHeight = $rowVisualHeight($row);
                $values = [
                    (string) $row['invoice']->NOFAKTUR,
                    $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                    $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                    (string) $row['invoice']->CUSTOMER,
                    (string) ($row['customer_name'] ?: '-'),
                    number_format((float) $row['invoice_amount'], 2, ',', '.'),
                    number_format((float) $row['paid'], 2, ',', '.'),
                    number_format((float) $row['balance'], 2, ',', '.'),
                ];
                $x = $margin;
                foreach ($columns as $index => $width) {
                    $drawText($values[$index], $x, $y, $width, $index >= 5, $index === 7, in_array($index, [3, 4], true));
                    $x += $width;
                }
                $canvas->line($margin, $y + $visualHeight, $margin + $tableWidth, $y + $visualHeight, $black, 0.25);
                $y += $visualHeight;
            }

            if ($pageIndex === array_key_last($pages)) {
                $totalY = $top + $tableHeight;
                $canvas->filled_rectangle($margin, $totalY, $tableWidth, $headerHeight, $gray);
                $canvas->rectangle($margin, $totalY, $tableWidth, $headerHeight, $black, 0.6);
                $labelWidth = array_sum(array_slice($columns, 0, 5));
                $drawText('TOTAL ('.$report['invoiceTotals']['count'].' invoice)', $margin, $totalY + 2.2, $labelWidth, false, true);
                $x = $margin + $labelWidth;
                foreach (['invoice_amount', 'paid', 'balance'] as $totalIndex => $totalKey) {
                    $totalWidth = $columns[5 + $totalIndex];
                    $canvas->line($x, $totalY, $x, $totalY + $headerHeight, $black, 0.35);
                    $drawText(number_format((float) $report['invoiceTotals'][$totalKey], 2, ',', '.'), $x, $totalY + 2.2, $totalWidth, true, true);
                    $x += $totalWidth;
                }
            }
        }

        $content = $dompdf->output();
        $filename = 'laporan_piutang_invoice_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Piutang-Data-Ms' => (string) round(($dataReadyAt - $startedAt) * 1000),
            'X-Piutang-Pdf-Ms' => (string) round((microtime(true) - $dataReadyAt) * 1000),
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $data = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'tanggal_posisi' => ['nullable', 'date'],
            'customer' => ['nullable', 'string', 'max:100'],
            'nofaktur' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:semua,open,lunas,jatuh_tempo'],
            'aging' => ['nullable', 'in:belum_jatuh_tempo,1_30,31_60,61_90,lebih_90'],
            'aging_detail' => ['nullable', 'in:all'],
            'section' => ['nullable', 'in:invoice,pelanggan,aging,mutasi,penambahan,pembayaran,kartu'],
        ]);

        $tanggalPosisi = Carbon::parse($data['tanggal_posisi'] ?? now())->format('Y-m-d');

        $agingFilter = $data['aging'] ?? '';

        return [
            'tanggalAwal' => Carbon::parse($data['tanggal_awal'] ?? Carbon::parse($tanggalPosisi)->startOfYear())->format('Y-m-d'),
            'tanggalAkhir' => Carbon::parse($data['tanggal_akhir'] ?? $tanggalPosisi)->format('Y-m-d'),
            'tanggalPosisi' => $tanggalPosisi,
            'customer' => trim((string) ($data['customer'] ?? '')),
            'nofaktur' => trim((string) ($data['nofaktur'] ?? '')),
            'status' => $data['status'] ?? 'semua',
            'agingFilter' => $agingFilter,
            'agingDetail' => $data['aging_detail'] ?? '',
            'reportSection' => $data['section'] ?? ($agingFilter !== '' ? 'aging' : 'invoice'),
        ];
    }

    private function buildReport(array $filters, ?string $onlySection = null): array
    {
        $fullReport = $onlySection === null;
        $asOf = Carbon::parse($filters['tanggalPosisi'])->endOfDay();
        $invoiceQuery = Arh::query()->where('TGLFAKTUR', '<=', $filters['tanggalPosisi']);
        if ($filters['customer'] !== '') {
            $invoiceQuery->where('CUSTOMER', $filters['customer']);
        }
        if ($filters['nofaktur'] !== '') {
            $invoiceQuery->where('NOFAKTUR', 'like', '%'.$filters['nofaktur'].'%');
        }

        // Kartu faktur: wajib filter no faktur atau customer agar query tetap ringan.
        $kartuNeedsFilter = $filters['reportSection'] === 'kartu'
            && $filters['nofaktur'] === ''
            && $filters['customer'] === '';
        if ($kartuNeedsFilter) {
            $invoiceQuery->whereRaw('1 = 0');
        }

        $invoiceModels = $invoiceQuery->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();
        $customerOptions = $fullReport
            ? Arh::query()->where('TGLFAKTUR', '<=', $filters['tanggalPosisi'])
                ->whereNotNull('CUSTOMER')->distinct()->orderBy('CUSTOMER')->pluck('CUSTOMER')
            : collect();
        $invoiceNumbers = $invoiceModels->pluck('NOFAKTUR');
        $paymentsByInvoice = collect();

        if ($invoiceNumbers->isNotEmpty()) {
            $paymentsByInvoice = \App\Models\Bard::query()
                ->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
                ->whereIn('bard.NOFAKTUR', $invoiceNumbers)
                ->where('barh.TGLBAYAR', '<=', $filters['tanggalPosisi'])
                ->where(function ($query) use ($filters): void {
                    $query->where('barh.STATUS', '!=', 'VOID')
                        ->orWhere('barh.VOIDED_AT', '>', Carbon::parse($filters['tanggalPosisi'])->endOfDay());
                })
                ->selectRaw('bard.NOFAKTUR, SUM(bard.NILAI_BAYAR + bard.PPH23 + bard.DISCOUNT + bard.BANK_ADMIN + bard.RETUR) as settled')
                ->groupBy('bard.NOFAKTUR')
                ->pluck('settled', 'bard.NOFAKTUR');
        }

        $customerNames = DB::table('mcustomer')
            ->whereIn('CUSTOMER', $invoiceModels->pluck('CUSTOMER')->filter()->unique())
            ->pluck('NAMACUST', 'CUSTOMER')
            ->mapWithKeys(fn ($name, $code): array => [strtoupper(trim((string) $code)) => trim((string) $name)]);

        $allInvoices = $invoiceModels->map(function (Arh $invoice) use ($paymentsByInvoice, $customerNames, $asOf): array {
            $amount = (float) $invoice->PIUTANG;
            $paid = min($amount, (float) ($paymentsByInvoice[$invoice->NOFAKTUR] ?? 0));
            $balance = max(0, $amount - $paid);
            $daysOverdue = $invoice->TGLJT && $invoice->TGLJT->lt($asOf)
                ? (int) $invoice->TGLJT->diffInDays($asOf)
                : 0;

            return [
                'invoice' => $invoice,
                'customer_name' => (string) ($customerNames[strtoupper(trim((string) $invoice->CUSTOMER))] ?? ''),
                'invoice_amount' => $amount,
                'paid' => $paid,
                'balance' => $balance,
                'days_overdue' => $daysOverdue,
                'age_label' => $this->ageLabel($invoice, $daysOverdue, $balance),
                'status' => $balance <= 0 ? 'Lunas' : ($daysOverdue > 0 ? 'Jatuh Tempo' : 'Belum Jatuh Tempo'),
            ];
        });

        $invoices = $allInvoices->filter(fn (array $row): bool => match ($filters['status']) {
            'open' => $row['balance'] > 0,
            'lunas' => $row['balance'] <= 0,
            'jatuh_tempo' => $row['balance'] > 0 && $row['days_overdue'] > 0,
            default => true,
        })->filter(fn (array $row): bool => match ($filters['agingFilter']) {
            'belum_jatuh_tempo' => $row['balance'] > 0 && $row['days_overdue'] <= 0,
            '1_30' => $row['balance'] > 0 && $row['days_overdue'] >= 1 && $row['days_overdue'] <= 30,
            '31_60' => $row['balance'] > 0 && $row['days_overdue'] >= 31 && $row['days_overdue'] <= 60,
            '61_90' => $row['balance'] > 0 && $row['days_overdue'] >= 61 && $row['days_overdue'] <= 90,
            'lebih_90' => $row['balance'] > 0 && $row['days_overdue'] > 90,
            default => true,
        })->values();

        $openInvoices = $allInvoices->where('balance', '>', 0);
        $aging = collect(['belum_jatuh_tempo' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'lebih_90' => 0]);
        if ($fullReport || $onlySection === 'aging') {
            foreach ($openInvoices as $row) {
                $key = $row['days_overdue'] <= 0 ? 'belum_jatuh_tempo'
                    : ($row['days_overdue'] <= 30 ? '1_30'
                    : ($row['days_overdue'] <= 60 ? '31_60'
                    : ($row['days_overdue'] <= 90 ? '61_90' : 'lebih_90')));
                $aging[$key] += $row['balance'];
            }
        }

        $agingGroups = ($fullReport || $onlySection === 'aging') ? $openInvoices->groupBy(function (array $row): string {
            return $row['days_overdue'] <= 0 ? 'belum_jatuh_tempo'
                : ($row['days_overdue'] <= 30 ? '1_30'
                : ($row['days_overdue'] <= 60 ? '31_60'
                : ($row['days_overdue'] <= 90 ? '61_90' : 'lebih_90')));
        }) : collect();

        $customers = ($fullReport || $onlySection === 'pelanggan') ? $allInvoices->groupBy(fn (array $row) => $row['invoice']->CUSTOMER)
            ->map(fn (Collection $rows, string $code): array => [
                'customer' => $code,
                'customer_name' => (string) ($rows->first()['customer_name'] ?? ''),
                'invoice_count' => $rows->count(),
                'invoice_amount' => $rows->sum('invoice_amount'),
                'paid' => $rows->sum('paid'),
                'balance' => $rows->sum('balance'),
            ])->sortBy('customer')->values() : collect();

        $payments = ($fullReport || in_array($onlySection, ['mutasi', 'pembayaran'], true)) ? Barh::query()
            ->with('details')
            ->whereBetween('TGLBAYAR', [$filters['tanggalAwal'], $filters['tanggalAkhir']])
            ->where(function ($query) use ($filters): void {
                $query->where('STATUS', '!=', 'VOID')->orWhereDate('VOIDED_AT', '>', $filters['tanggalAkhir']);
            })
            ->when($filters['customer'] !== '', fn ($query) => $query->where('CUSTOMER', $filters['customer']))
            ->orderBy('TGLBAYAR')->orderBy('NOBAYAR')->get() : collect();
        $payments->each(function (Barh $payment) use ($customerNames): void {
            $payment->setAttribute('customer_name', (string) ($customerNames[strtoupper(trim((string) $payment->CUSTOMER))] ?? ''));
        });

        $periodInvoices = ($fullReport || in_array($onlySection, ['mutasi', 'penambahan'], true)) ? $allInvoices->filter(fn (array $row): bool => $row['invoice']->TGLFAKTUR->betweenIncluded(
            Carbon::parse($filters['tanggalAwal']), Carbon::parse($filters['tanggalAkhir'])
        ))->sortBy(fn (array $row): string => $row['invoice']->TGLFAKTUR->format('Y-m-d').'|'.$row['invoice']->NOFAKTUR)->values() : collect();
        $periodPayments = $payments->sum(fn (Barh $payment): float => (float) $payment->details->sum('NILAI_BAYAR'));
        $periodAdjustments = $payments->sum(fn (Barh $payment): float => (float) $payment->details->sum(
            fn ($detail): float => (float) $detail->PPH23 + (float) $detail->DISCOUNT + (float) $detail->BANK_ADMIN + (float) $detail->RETUR
        ));
        $openingBalance = ($fullReport || $onlySection === 'mutasi')
            ? $this->subledgerBalanceAt(Carbon::parse($filters['tanggalAwal'])->subDay()->format('Y-m-d'), $filters['customer'])
            : 0;
        $movement = [
            'opening' => $openingBalance,
            'invoices' => $periodInvoices->sum('invoice_amount'),
            'payments' => $periodPayments,
            'adjustments' => $periodAdjustments,
            'ending' => $openingBalance + $periodInvoices->sum('invoice_amount') - $periodPayments - $periodAdjustments,
        ];

        $receivableCoaId = AccountMapping::getCoaId('accounts_receivable');
        $ledgerBalance = null;
        if ($fullReport && $receivableCoaId && $filters['customer'] === '') {
            $ledger = $this->journalQueryService->getSumByCoaId('1900-01-01', $filters['tanggalPosisi'], $receivableCoaId);
            $ledgerBalance = $ledger['debit'] - $ledger['kredit'];
        }
        $subledgerBalance = $openInvoices->sum('balance');

        return compact(
            'invoices',
            'customers',
            'customerOptions',
            'payments',
            'periodInvoices',
            'aging',
            'agingGroups',
            'movement',
            'ledgerBalance',
            'subledgerBalance',
            'receivableCoaId',
            'kartuNeedsFilter'
        );
    }

    private function subledgerBalanceAt(string $date, string $customer): float
    {
        if (Carbon::parse($date)->year < 1900) {
            return 0;
        }

        // Hitung saldo awal langsung di database. Cara ini menghindari pemuatan dan
        // pembentukan model seluruh invoice untuk kedua kalinya.
        $settled = DB::table('bard')->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->where('barh.TGLBAYAR', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->where('barh.STATUS', '!=', 'VOID')
                    ->orWhere('barh.VOIDED_AT', '>', Carbon::parse($date)->endOfDay());
            })
            ->selectRaw('bard.NOFAKTUR, SUM(bard.NILAI_BAYAR + bard.PPH23 + bard.DISCOUNT + bard.BANK_ADMIN + bard.RETUR) AS settled')
            ->groupBy('bard.NOFAKTUR');

        $result = DB::table('arh')
            ->leftJoinSub($settled, 'settled_payments', function ($join): void {
                $join->on('settled_payments.NOFAKTUR', '=', 'arh.NOFAKTUR');
            })
            ->where('arh.TGLFAKTUR', '<=', $date)
            ->when($customer !== '', fn ($query) => $query->where('arh.CUSTOMER', $customer))
            ->selectRaw('COALESCE(SUM(CASE WHEN arh.PIUTANG - COALESCE(settled_payments.settled, 0) > 0 THEN arh.PIUTANG - COALESCE(settled_payments.settled, 0) ELSE 0 END), 0) AS balance')
            ->first();

        return (float) ($result?->balance ?? 0);
    }

    private function attachPaymentDetailsToInvoiceRows(Collection $rows, ?string $asOfDate = null): void
    {
        $invoices = $rows
            ->pluck('invoice')
            ->filter(fn ($invoice) => $invoice instanceof Arh)
            ->values();

        $invoiceNumbers = $invoices
            ->pluck('NOFAKTUR')
            ->filter()
            ->map(fn ($invoice) => trim((string) $invoice))
            ->unique()
            ->values();

        if ($invoiceNumbers->isEmpty()) {
            return;
        }

        $paymentDetails = Bard::query()
            ->with(['header.paymentCoa'])
            ->whereIn('NOFAKTUR', $invoiceNumbers)
            ->whereHas('header', function ($query) use ($asOfDate): void {
                if ($asOfDate) {
                    $query->where('TGLBAYAR', '<=', $asOfDate)
                        ->where(function ($status) use ($asOfDate): void {
                            $status->where(function ($active): void {
                                $active->whereNull('STATUS')->orWhere('STATUS', '!=', 'VOID');
                            })->orWhere('VOIDED_AT', '>', Carbon::parse($asOfDate)->endOfDay());
                        });
                } else {
                    $query->where(function ($status): void {
                        $status->whereNull('STATUS')->orWhere('STATUS', '!=', 'VOID');
                    });
                }
            })
            ->get()
            ->sortBy(fn (Bard $detail) => optional($detail->header)->TGLBAYAR?->format('Y-m-d').'|'.$detail->NOBAYAR)
            ->values()
            ->groupBy(fn (Bard $detail) => trim((string) $detail->NOFAKTUR));

        $invoices->each(function (Arh $invoice) use ($paymentDetails): void {
            $details = $paymentDetails->get(trim((string) $invoice->NOFAKTUR), collect());
            $invoice->setRelation('paymentDetails', $details);
            $invoice->setAttribute('PAYMENT_DETAIL_COUNT', $details->count());
            $invoice->setAttribute('PAYMENT_SETTLED_TOTAL', $details->sum(fn (Bard $detail) =>
                (float) $detail->NILAI_BAYAR
                + (float) $detail->PPH23
                + (float) $detail->DISCOUNT
                + (float) $detail->BANK_ADMIN
                + (float) $detail->RETUR
            ));
        });
    }

    private function buildInvoiceCard(array $row, string $asOfDate): array
    {
        $invoice = $row['invoice'];
        $payments = collect($invoice->paymentDetails ?? []);
        $lines = collect();

        $lines->push([
            'date' => $invoice->TGLFAKTUR,
            'type' => 'invoice',
            'reference' => (string) $invoice->NOFAKTUR,
            'description' => 'Penambahan piutang'.($invoice->SOURCE ? ' ('.$invoice->SOURCE.')' : ''),
            'debit' => (float) $row['invoice_amount'],
            'credit' => 0.0,
            'url' => null,
        ]);

        foreach ($payments as $detail) {
            $settled = (float) $detail->NILAI_BAYAR
                + (float) $detail->PPH23
                + (float) $detail->DISCOUNT
                + (float) $detail->BANK_ADMIN
                + (float) $detail->RETUR;
            $header = $detail->header;
            $isVoid = $header && strtoupper((string) ($header->STATUS ?? '')) === 'VOID';

            $lines->push([
                'date' => $header?->TGLBAYAR,
                'type' => 'payment',
                'reference' => (string) $detail->NOBAYAR,
                'description' => trim(
                    'Pelunasan'
                    .($header?->METODE_PEMBAYARAN ? ' '.$header->METODE_PEMBAYARAN : '')
                    .(
                        $settled > (float) $detail->NILAI_BAYAR
                            ? ' (bayar '.number_format((float) $detail->NILAI_BAYAR, 2, ',', '.')
                                .', potongan/lain '.number_format($settled - (float) $detail->NILAI_BAYAR, 2, ',', '.').')'
                            : ''
                    )
                ),
                'debit' => 0.0,
                'credit' => $settled,
                'url' => $header
                    ? ($isVoid
                        ? route('pelunasan-piutang.show', $header->id)
                        : route('pelunasan-piutang.edit', $header->id))
                    : null,
            ]);
        }

        $balance = 0.0;
        $withBalance = $lines->values()->map(function (array $line) use (&$balance) {
            $balance += $line['debit'] - $line['credit'];
            $line['balance'] = max(0, $balance);

            return $line;
        });

        $journal = filled($invoice->NOJURNAL)
            ? \App\Models\Jurnal::query()->where('no_jurnal', $invoice->NOJURNAL)->first(['id', 'no_jurnal'])
            : null;

        return $row + [
            'as_of' => $asOfDate,
            'lines' => $withBalance,
            'journal_number' => (string) ($invoice->NOJURNAL ?: ''),
            'journal_url' => $journal ? route('jurnal.show', $journal->id) : null,
        ];
    }

    private function ageLabel(Arh $invoice, int $daysOverdue, float $balance): string
    {
        if ($balance <= 0) return 'Lunas';
        if (! $invoice->TGLJT || $daysOverdue <= 0) return 'Belum jatuh tempo';
        if ($daysOverdue <= 30) return '1–30 hari';
        if ($daysOverdue <= 60) return '31–60 hari';
        if ($daysOverdue <= 90) return '61–90 hari';
        return '> 90 hari';
    }

    private function agingKey(array $row): string
    {
        return $row['days_overdue'] <= 0 ? 'belum_jatuh_tempo'
            : ($row['days_overdue'] <= 30 ? '1_30'
            : ($row['days_overdue'] <= 60 ? '31_60'
            : ($row['days_overdue'] <= 90 ? '61_90' : 'lebih_90')));
    }

    private function paginateCollection(Collection $rows, Request $request, string $pageName, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return (new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page,
            ['path' => $request->url(), 'pageName' => $pageName]
        ))->appends($request->except($pageName));
    }
}
