<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class FastTablePdfService
{
    public function stream(array $configuration)
    {
        $startedAt = microtime(true);
        $dotMatrix = (bool) ($configuration['dot_matrix'] ?? false);
        if ($dotMatrix) {
            return $this->streamText($configuration, $startedAt);
        }

        $landscape = ($configuration['paper_orientation'] ?? 'portrait') === 'landscape';
        $pageWidth = $landscape ? 792.0 : 612.0;
        $pageHeight = $landscape ? 612.0 : 792.0;
        $pdf = Pdf::loadHTML('<!doctype html><html><body></body></html>')
            ->setPaper('letter', $landscape ? 'landscape' : 'portrait');
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $fonts = $dompdf->getFontMetrics();
        $normal = $fonts->getFont($dotMatrix ? 'Courier' : 'Helvetica', 'normal');
        $bold = $fonts->getFont($dotMatrix ? 'Courier' : 'Helvetica', 'bold');
        $black = [0, 0, 0];
        $headerColor = $dotMatrix ? [1, 1, 1] : [0.90, 0.90, 0.90];
        $margin = (float) ($configuration['margin'] ?? 24.0);
        $tableWidth = $pageWidth - ($margin * 2);
        $rowHeight = (float) ($configuration['row_height'] ?? 10.0);
        $headerHeight = 15.0;
        $columns = $configuration['columns'];
        $headings = $configuration['headings'];
        $rows = $configuration['rows'];
        $numeric = $configuration['numeric'] ?? [];
        $boldColumns = $configuration['bold_columns'] ?? [];
        $wrapColumns = $configuration['wrap_columns'] ?? [];
        $maxWrapLines = (int) ($configuration['max_wrap_lines'] ?? 3);
        $rowCells = static fn (array $row): array => isset($row['cells']) && is_array($row['cells']) ? $row['cells'] : $row;
        $rowStyle = static fn (array $row): string => (string) ($row['style'] ?? '');
        $center = function (string $text, float $y, $font, float $size) use ($canvas, $fonts, $black, $pageWidth): void {
            $canvas->text(($pageWidth - $fonts->getTextWidth($text, $font, $size)) / 2, $y, $text, $font, $size, $black);
        };
        $wrap = function (string $text, float $width, float $size, $font) use ($fonts, $configuration, $maxWrapLines): array {
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ($text === '') {
                return [''];
            }

            $usableWidth = max(1, $width - 8);
            $splitLongWord = function (string $word) use ($fonts, $font, $size, $usableWidth): array {
                $chunks = [];
                $chunk = '';
                $length = mb_strlen($word);

                for ($i = 0; $i < $length; $i++) {
                    $candidate = $chunk.mb_substr($word, $i, 1);
                    if ($chunk !== '' && $fonts->getTextWidth($candidate, $font, $size) > $usableWidth) {
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

            $lines = [];
            $line = '';
            foreach (preg_split('/\s+/', $text) ?: [] as $word) {
                if ($fonts->getTextWidth($word, $font, $size) > $usableWidth) {
                    if ($line !== '') {
                        $lines[] = $line;
                        $line = '';
                    }

                    foreach ($splitLongWord($word) as $chunk) {
                        $lines[] = $chunk;
                        if (count($lines) >= $maxWrapLines) {
                            break 2;
                        }
                    }

                    continue;
                }

                $candidate = $line === '' ? $word : $line.' '.$word;
                if ($fonts->getTextWidth($candidate, $font, $size) <= $usableWidth) {
                    $line = $candidate;
                    continue;
                }

                if ($line !== '') {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $lines[] = $word;
                    $line = '';
                }

                if (count($lines) >= $maxWrapLines) {
                    break;
                }
            }

            if ($line !== '' && count($lines) < $maxWrapLines) {
                $lines[] = $line;
            }

            return array_slice($lines ?: [''], 0, $maxWrapLines);
        };
        $lineHeight = function () use ($configuration): float {
            $size = (float) ($configuration['font_size'] ?? 6.0);

            return $size + 2.2;
        };
        $rowVisualHeight = function (array $row) use ($columns, $wrapColumns, $wrap, $normal, $configuration, $rowHeight, $lineHeight, $rowCells, $rowStyle): float {
            $size = (float) ($configuration['font_size'] ?? 6.0);
            $maxLines = 1;
            $cells = $rowCells($row);

            foreach ($wrapColumns as $index) {
                $maxLines = max($maxLines, count($wrap((string) ($cells[$index] ?? ''), (float) ($columns[$index] ?? 0), $size, $normal)));
            }

            $minimum = match ($rowStyle($row)) {
                'customer' => $rowHeight + 7.0,
                'subtotal_customer', 'subtotal_group', 'grand_total' => $rowHeight + 3.0,
                default => $rowHeight,
            };

            return max($minimum, 5.0 + $maxLines * $lineHeight());
        };
        $topBase = 68.0 + count($configuration['subtitles'] ?? []) * 7;
        $bottomLimit = $pageHeight - 28.0;
        $pages = [];
        $currentPage = [];
        $currentHeight = $headerHeight;
        foreach ($rows as $row) {
            $visualHeight = $rowVisualHeight($row);
            if ($currentPage !== [] && $topBase + $currentHeight + $visualHeight > $bottomLimit) {
                $pages[] = $currentPage;
                $currentPage = [];
                $currentHeight = $headerHeight;
            }

            $currentPage[] = $row;
            $currentHeight += $visualHeight;
        }
        $pages[] = $currentPage;
        if ($pages === [[]]) $pages = [[]];

        $draw = function (string $text, float $x, float $y, float $width, bool $right = false, bool $isBold = false, bool $shouldWrap = false) use ($canvas, $fonts, $normal, $bold, $black, $configuration, $wrap): void {
            $font = $isBold ? $bold : $normal;
            $size = (float) ($configuration['font_size'] ?? 6.0);
            if ($shouldWrap && ! $right) {
                foreach ($wrap($text, $width, $size, $font) as $lineIndex => $line) {
                    $canvas->text($x + 2.5, $y + 2.0 + ($lineIndex * ($size + 2.2)), $line, $font, $size, $black);
                }
                return;
            }
            $averageWidth = $size * ($configuration['dot_matrix'] ?? false ? 0.60 : 0.52);
            $text = mb_strimwidth($text, 0, max(1, (int) floor(($width - 5) / $averageWidth)), '');
            $textX = $right ? $x + $width - 2.5 - $fonts->getTextWidth($text, $font, $size) : $x + 2.5;
            $canvas->text(max($x + 1, $textX), $y + 2.0, $text, $font, $size, $black);
        };

        foreach ($pages as $pageIndex => $pageRows) {
            if ($pageIndex > 0) $canvas->new_page();
            $center(strtoupper((string) config('app.company_name', config('app.name'))), 22, $bold, 11);
            $center((string) $configuration['title'], 36, $bold, 10);
            foreach (($configuration['subtitles'] ?? []) as $subtitleIndex => $subtitle) {
                $center((string) $subtitle, 49 + $subtitleIndex * 9, $normal, 7);
            }

            $top = $topBase;
            $height = $headerHeight + array_sum(array_map(fn ($row): float => $rowVisualHeight($row), $pageRows));
            $canvas->filled_rectangle($margin, $top, $tableWidth, $headerHeight, $headerColor);
            $canvas->rectangle($margin, $top, $tableWidth, $height, $black, 0.6);
            $x = $margin;
            foreach ($columns as $index => $width) {
                if ($index > 0) $canvas->line($x, $top, $x, $top + $height, $black, 0.35);
                $draw((string) $headings[$index], $x, $top + 2.2, $width, in_array($index, $numeric, true), true);
                $x += $width;
            }
            $canvas->line($margin, $top + $headerHeight, $margin + $tableWidth, $top + $headerHeight, $black, 0.6);

            $y = $top + $headerHeight;
            foreach ($pageRows as $row) {
                $visualHeight = $rowVisualHeight($row);
                $style = $rowStyle($row);
                $cells = $rowCells($row);
                $fillColor = match ($style) {
                'customer' => [0.93, 0.96, 1.00],
                'subtotal_customer' => [0.90, 0.98, 0.94],
                'subtotal_group' => [0.88, 0.95, 1.00],
                'summary_header' => [0.94, 0.94, 0.94],
                'summary_row' => [1, 1, 1],
                'summary_total' => [0.93, 0.97, 1.00],
                'grand_total' => [1.00, 0.94, 0.78],
                default => null,
            };

                if ($fillColor !== null) {
                    $canvas->filled_rectangle($margin, $y, $tableWidth, $visualHeight, $fillColor);
                }

                $x = $margin;
                foreach ($columns as $index => $width) {
                    $isStyledSubtotal = in_array($style, ['subtotal_customer', 'subtotal_group', 'summary_header', 'summary_total', 'grand_total'], true);
                    $isBold = in_array($index, $boldColumns, true)
                        || $style === 'customer'
                        || $isStyledSubtotal;
                    $draw((string) ($cells[$index] ?? ''), $x, $y + ($isStyledSubtotal ? 1.0 : 0.0), $width, in_array($index, $numeric, true), $isBold, in_array($index, $wrapColumns, true));
                    $x += $width;
                }
                $canvas->line($margin, $y + $visualHeight, $margin + $tableWidth, $y + $visualHeight, $black, 0.25);
                $y += $visualHeight;
            }
        }

        $content = $dompdf->output();
        $filename = preg_replace('/[^A-Za-z0-9_.-]+/', '_', (string) $configuration['filename']);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Report-Pdf-Ms' => (string) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    private function streamText(array $configuration, float $startedAt)
    {
        $columns = array_map('intval', $configuration['columns']);
        $headings = array_map('strval', $configuration['headings']);
        $rows = $configuration['rows'];
        $numeric = $configuration['numeric'] ?? [];
        $subtitles = $configuration['subtitles'] ?? [];
        $targetWidth = (int) ($configuration['text_width'] ?? 120);
        $linesPerPage = (int) ($configuration['text_lines_per_page'] ?? 60);
        $rowCells = static fn (array $row): array => isset($row['cells']) && is_array($row['cells']) ? $row['cells'] : $row;
        $rowStyle = static fn (array $row): string => (string) ($row['style'] ?? '');
        $separatorWidth = max(80, $targetWidth);
        $availableWidth = $targetWidth - max(0, count($columns) - 1);
        $columnTotal = max(1, array_sum($columns));
        $widths = [];
        $allocated = 0;

        foreach ($columns as $index => $column) {
            $width = max(6, (int) floor($column / $columnTotal * $availableWidth));
            $widths[$index] = $width;
            $allocated += $width;
        }

        $remaining = $availableWidth - $allocated;
        $lastIndex = max(0, count($widths) - 1);
        $widths[$lastIndex] = max(6, $widths[$lastIndex] + $remaining);

        $fit = static function (string $value, int $width, bool $right = false): string {
            $value = trim(preg_replace('/\s+/', ' ', $value));
            if (mb_strlen($value) > $width) {
                $value = mb_substr($value, 0, $width);
            }

            return $right ? str_pad($value, $width, ' ', STR_PAD_LEFT) : str_pad($value, $width);
        };
        $looksNumeric = static function (string $value): bool {
            $value = trim($value);
            if ($value === '' || preg_match('/[A-Za-z]/', $value)) {
                return false;
            }

            return (bool) preg_match('/^\(?-?\d[\d.,]*(?:\)?|)$/', $value);
        };
        $alignRight = static function (int $index, string $value) use ($numeric, $looksNumeric): bool {
            return in_array($index, $numeric, true) || $looksNumeric($value);
        };
        $wrapCell = static function (string $value, int $width, bool $right = false): array {
            $value = trim(preg_replace('/\s+/', ' ', $value));
            if ($value === '') {
                return [''];
            }

            $lines = [];
            $line = '';
            foreach (preg_split('/\s+/', $value) ?: [] as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;
                if (mb_strlen($candidate) <= $width) {
                    $line = $candidate;
                    continue;
                }

                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }

                while (mb_strlen($word) > $width) {
                    $lines[] = mb_substr($word, 0, $width);
                    $word = mb_substr($word, $width);
                }

                $line = $word;
            }

            if ($line !== '') {
                $lines[] = $line;
            }

            return $right ? array_map('trim', $lines) : $lines;
        };
        $line = function (array $values, bool $detectNumbers = false) use ($widths, $numeric, $fit, $alignRight): string {
            $parts = [];
            foreach ($widths as $index => $width) {
                $value = (string) ($values[$index] ?? '');
                $parts[] = $fit($value, $width, $detectNumbers ? $alignRight($index, $value) : in_array($index, $numeric, true));
            }

            return implode(' ', $parts);
        };
        $wrappedLines = function (array $values) use ($widths, $fit, $wrapCell, $alignRight): array {
            $cells = [];
            $alignments = [];
            $maxLines = 1;
            foreach ($widths as $index => $width) {
                $value = (string) ($values[$index] ?? '');
                $right = $alignRight($index, $value);
                $cellLines = $wrapCell($value, $width, $right);
                $cells[$index] = $cellLines;
                $alignments[$index] = $right;
                $maxLines = max($maxLines, count($cellLines));
            }

            $lines = [];
            for ($lineIndex = 0; $lineIndex < $maxLines; $lineIndex++) {
                $parts = [];
                foreach ($widths as $index => $width) {
                    $parts[] = $fit((string) ($cells[$index][$lineIndex] ?? ''), $width, (bool) ($alignments[$index] ?? false));
                }
                $lines[] = implode(' ', $parts);
            }

            return $lines;
        };

        $header = [
            strtoupper((string) config('app.company_name', config('app.name'))),
            (string) $configuration['title'],
        ];
        foreach ($subtitles as $subtitle) {
            $header[] = (string) $subtitle;
        }
        $header[] = str_repeat('-', $separatorWidth);
        $header[] = $line($headings, false);
        $header[] = str_repeat('-', $separatorWidth);

        $footer = str_repeat('-', $separatorWidth);
        $bodyLines = [];
        if ($rows === []) {
            $bodyLines[] = 'Tidak ada data.';
        } else {
            foreach ($rows as $row) {
                $style = $rowStyle($row);
                if ($style === 'summary_header') {
                    $bodyLines[] = str_repeat('-', $separatorWidth);
                }
                array_push($bodyLines, ...$wrappedLines($rowCells($row)));
                if (in_array($style, ['summary_header', 'summary_total'], true)) {
                    $bodyLines[] = str_repeat('-', $separatorWidth);
                }
            }
        }
        $bodyLimit = max(1, $linesPerPage - count($header) - 1);
        $output = [];

        foreach (array_chunk($bodyLines, $bodyLimit) as $pageIndex => $pageRows) {
            if ($pageIndex > 0) {
                $output[] = "\f";
            }

            array_push($output, ...$header);
            foreach ($pageRows as $rowLine) {
                $output[] = (string) $rowLine;
            }
            $output[] = $footer;
        }
        $content = implode("\r\n", $output)."\r\n";
        $filename = preg_replace('/\.pdf$/i', '.txt', preg_replace('/[^A-Za-z0-9_.-]+/', '_', (string) $configuration['filename']));
        if (! str_ends_with(strtolower($filename), '.txt')) {
            $filename .= '.txt';
        }

        if (request()->boolean('auto_print')) {
            $escapedContent = e($content);

            return response(<<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Dot Matrix</title>
    <style>
        @page { size: letter portrait; margin: 0.35in; }
        body { margin: 0; background: #fff; color: #000; }
        pre {
            margin: 0;
            font-family: "Courier New", Courier, monospace;
            font-size: 8pt;
            line-height: 1.05;
            white-space: pre;
        }
        @media screen {
            body { padding: 12px; }
            pre { border: 1px solid #ddd; padding: 10px; overflow: auto; }
        }
        @media print {
            body { padding: 0; }
            pre { border: 0; padding: 0; }
        }
    </style>
</head>
<body>
<pre>{$escapedContent}</pre>
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 250);
    });
</script>
</body>
</html>
HTML, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Report-Text-Ms' => (string) round((microtime(true) - $startedAt) * 1000),
            ]);
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Report-Text-Ms' => (string) round((microtime(true) - $startedAt) * 1000),
        ]);
    }
}
