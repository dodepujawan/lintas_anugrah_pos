<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PanduanController extends Controller
{
    public function transaksi(string $slug)
    {
        $guides = [
            'saldo-awal-akun' => ['README_SALDO_AWAL_AKUN.md', 'Panduan Saldo Awal Akun', 'saldo-awal.index', 'saldo-awal.create'],
            'saldo-awal-piutang' => ['README_SALDO_AWAL_PIUTANG.md', 'Panduan Saldo Awal Piutang', 'piutang.index', 'piutang.create'],
            'piutang-berjalan' => ['README_PIUTANG_BERJALAN.md', 'Panduan Piutang Berjalan', 'piutang-berjalan.index', 'piutang-berjalan.create'],
            'saldo-awal-hutang' => ['README_SALDO_AWAL_HUTANG.md', 'Panduan Saldo Awal Hutang', 'hutang.index', 'saldo-awal-hutang.create'],
            'hutang-berjalan' => ['README_HUTANG_BERJALAN.md', 'Panduan Hutang Berjalan', 'hutang.index', 'hutang.create'],
            'pelunasan-hutang' => ['README_PELUNASAN_HUTANG.md', 'Panduan Pelunasan Hutang', 'pelunasan-hutang.index', 'pelunasan-hutang.create'],
            'import-data' => ['README_IMPORT_DATA.md', 'Panduan Import Data', 'import-data.index', 'import-data.index'],
        ];

        abort_unless(isset($guides[$slug]), 404);
        [$file, $title, $indexRoute, $createRoute] = $guides[$slug];
        $path = base_path($file);
        $html = File::exists($path)
            ? $this->markdownToHtml(File::get($path))
            : '<div class="alert alert-danger">File panduan tidak ditemukan.</div>';

        return view('panduan.transaksi', compact('html', 'title', 'indexRoute', 'createRoute'));
    }

    /**
     * Tampilkan panduan Tutup Buku dari file README_TUTUP_BUKU.md
     * Dirender sebagai HTML sederhana (heading, list, table, code).
     */
    public function tutupBuku()
    {
        $path = base_path('README_TUTUP_BUKU.md');

        if (!File::exists($path)) {
            $html = '<div class="alert alert-danger">File README_TUTUP_BUKU.md tidak ditemukan di root project.</div>';
        } else {
            $markdown = File::get($path);
            $html = $this->markdownToHtml($markdown);
        }

        return view('panduan.tutup_buku', [
            'content' => $html,
            'title'   => 'Panduan Tutup Buku',
        ]);
    }

    /**
     * Tampilkan panduan input Jurnal Umum dari file Markdown.
     */
    public function jurnalUmum()
    {
        $path = base_path('README_JURNAL_UMUM.md');

        if (!File::exists($path)) {
            $html = '<div class="alert alert-danger">File README_JURNAL_UMUM.md tidak ditemukan di root project.</div>';
        } else {
            $html = $this->markdownToHtml(File::get($path));
        }

        return view('panduan.jurnal_umum', [
            'content' => $html,
            'title' => 'Panduan Input Jurnal Umum',
        ]);
    }

    /**
     * Tampilkan panduan input dan koreksi Jurnal Penyesuaian.
     */
    public function jurnalPenyesuaian()
    {
        $path = base_path('README_JURNAL_PENYESUAIAN.md');

        $html = File::exists($path)
            ? $this->markdownToHtml(File::get($path))
            : '<div class="alert alert-danger">File README_JURNAL_PENYESUAIAN.md tidak ditemukan di root project.</div>';

        return view('panduan.jurnal_penyesuaian', [
            'content' => $html,
            'title' => 'Panduan Jurnal Penyesuaian',
        ]);
    }

    /**
     * Tampilkan panduan proses pelunasan piutang.
     */
    public function pelunasanPiutang()
    {
        $path = base_path('README_PELUNASAN_PIUTANG.md');

        if (!File::exists($path)) {
            $html = '<div class="alert alert-danger">File README_PELUNASAN_PIUTANG.md tidak ditemukan di root project.</div>';
        } else {
            $html = $this->markdownToHtml(File::get($path));
        }

        return view('panduan.pelunasan_piutang', [
            'content' => $html,
            'title' => 'Panduan Pelunasan Piutang',
        ]);
    }

    /**
     * Tampilkan panduan input dan pelunasan piutang sopir.
     */
    public function piutangSopir()
    {
        $path = base_path('README_PIUTANG_SOPIR.md');

        $html = File::exists($path)
            ? $this->markdownToHtml(File::get($path))
            : '<div class="alert alert-danger">File README_PIUTANG_SOPIR.md tidak ditemukan di root project.</div>';

        return view('panduan.piutang_sopir', [
            'content' => $html,
            'title' => 'Panduan Piutang Sopir',
        ]);
    }

    /**
     * Renderer markdown sederhana (tanpa package eksternal):
     *  - Heading  # ## ### ####
     *  - Bold **, Italic *
     *  - Inline code `code`
     *  - Code block ```
     *  - Checkbox - [ ] / - [x]
     *  - List - atau *
     *  - Tabel | ... |
     *  - Link [text](url)
     *  - Emoji tetap apa adanya
     */
    private function markdownToHtml(string $md): string
    {
        $lines  = preg_split("/\r\n|\n|\r/", $md);
        $html   = [];
        $inUl   = false;
        $inOl   = false;
        $inCode = false;
        $inPre  = false;
        $inTable = false;
        $tableHeader = [];
        $tableAligns = [];

        $flushLists = function () use (&$html, &$inUl, &$inOl) {
            if ($inUl) { $html[] = '</ul>'; $inUl = false; }
            if ($inOl) { $html[] = '</ol>'; $inOl = false; }
        };

        $closeTable = function () use (&$html, &$inTable, &$tableHeader, &$tableAligns) {
            if ($inTable) {
                $html[] = '</tbody></table></div>';
                $inTable = false;
                $tableHeader = [];
                $tableAligns = [];
            }
        };

        $inline = function ($text) {
            // escape dulu
            $text = e($text);
            // code block inline
            $text = preg_replace('/`([^`]+)`/', '<code class="px-1 py-0.5 rounded bg-gray-100 text-pink-600 text-sm">$1</code>', $text);
            // bold
            $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
            // italic
            $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $text);
            // link
            $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" class="text-blue-600 hover:underline">$1</a>', $text);
            // checkbox
            $text = preg_replace('/^\s*-\s*\[ \]\s*/', '<input type="checkbox" disabled class="mr-1"> ', $text);
            $text = preg_replace('/^\s*-\s*\[x\]\s*/i', '<input type="checkbox" checked disabled class="mr-1"> ', $text);
            return $text;
        };

        foreach ($lines as $rawLine) {
            $line = $rawLine;

            // Code block ```
            if (preg_match('/^```/', $line)) {
                if ($inPre) {
                    $html[] = '</code></pre>';
                    $inPre = false;
                } else {
                    $flushLists();
                    $closeTable();
                    $html[] = '<pre class="bg-gray-900 text-gray-100 rounded-lg p-4 overflow-x-auto text-sm"><code>';
                    $inPre = true;
                }
                continue;
            }
            if ($inPre) {
                $html[] = e($line) . "\n";
                continue;
            }

            // Horizontal rule
            if (preg_match('/^---+$/', trim($line))) {
                $flushLists();
                $closeTable();
                $html[] = '<hr class="my-6 border-gray-200">';
                continue;
            }

            // Heading
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
                $flushLists();
                $closeTable();
                $level = strlen($m[1]);
                $cls = [
                    1 => 'text-3xl font-bold mt-6 mb-4 text-gray-900 border-b pb-2',
                    2 => 'text-2xl font-bold mt-6 mb-3 text-gray-900 border-b pb-1',
                    3 => 'text-xl font-semibold mt-5 mb-2 text-gray-800',
                    4 => 'text-lg font-semibold mt-4 mb-2 text-gray-800',
                    5 => 'text-base font-semibold mt-3 mb-1 text-gray-700',
                    6 => 'text-sm font-semibold mt-2 mb-1 text-gray-700',
                ];
                $html[] = '<h' . $level . ' class="' . $cls[$level] . '">' . $inline($m[2]) . '</h' . $level . '>';
                continue;
            }

            // Blockquote
            if (preg_match('/^>\s?(.*)$/', $line, $m)) {
                $flushLists();
                $closeTable();
                $html[] = '<blockquote class="border-l-4 border-blue-400 bg-blue-50 px-4 py-2 my-3 text-gray-700 italic">' . $inline($m[1]) . '</blockquote>';
                continue;
            }

            // Table
            if (preg_match('/^\s*\|(.+)\|\s*$/', $line)) {
                $cells = array_map('trim', explode('|', trim($line, ' |')));
                // Deteksi baris alignment |---|---|
                if (preg_match('/^\s*\|?\s*:?-+:?\s*(\|\s*:?-+:?\s*)+\|?\s*$/', $line)) {
                    $tableAligns = array_map(function ($c) {
                        $c = trim($c, ' |');
                        $right  = str_ends_with($c, ':') && !str_starts_with($c, ':');
                        $left   = str_starts_with($c, ':') && !str_ends_with($c, ':');
                        if ($right) return 'text-right';
                        if ($left)  return 'text-left';
                        return 'text-center';
                    }, explode('|', trim($line, ' |')));
                    continue;
                }
                if (!$inTable) {
                    $flushLists();
                    $html[] = '<div class="overflow-x-auto my-4"><table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden"><thead class="bg-gray-100">';
                    $inTable = true;
                    $isHeader = true;
                }
                if (!empty($tableHeader) && !$isHeader) {
                    $html[] = '<tr>';
                }
                $tag = $isHeader ? 'th' : 'td';
                $cls = $isHeader
                    ? 'px-3 py-2 border-b border-gray-200 text-left font-semibold text-gray-700'
                    : 'px-3 py-2 border-b border-gray-100 text-gray-700';
                $html[] = '<tr>';
                foreach ($cells as $i => $c) {
                    $align = $tableAligns[$i] ?? '';
                    $html[] = '<' . $tag . ' class="' . $cls . ' ' . $align . '">' . $inline($c) . '</' . $tag . '>';
                }
                $html[] = '</tr>';
                if ($isHeader) {
                    $html[] = '</thead><tbody>';
                    $isHeader = false;
                }
                continue;
            } else {
                $closeTable();
            }

            // List item
            if (preg_match('/^\s*-\s+(.*)$/', $line, $m)) {
                if ($inOl) { $html[] = '</ol>'; $inOl = false; }
                if (!$inUl) { $html[] = '<ul class="list-disc pl-6 my-2 space-y-1 text-gray-700">'; $inUl = true; }
                if (preg_match('/^\[([ xX])\]\s*(.*)$/', $m[1], $checkbox)) {
                    $checked = strtolower($checkbox[1]) === 'x' ? ' checked' : '';
                    $html[] = '<li class="list-none"><input type="checkbox" disabled' . $checked . ' class="mr-1"> ' . $inline($checkbox[2]) . '</li>';
                } else {
                    $html[] = '<li>' . $inline($m[1]) . '</li>';
                }
                continue;
            }
            if (preg_match('/^\s*\d+\.\s+(.*)$/', $line, $m)) {
                if ($inUl) { $html[] = '</ul>'; $inUl = false; }
                if (!$inOl) { $html[] = '<ol class="list-decimal pl-6 my-2 space-y-1 text-gray-700">'; $inOl = true; }
                $html[] = '<li>' . $inline($m[1]) . '</li>';
                continue;
            }

            // Empty line
            if (trim($line) === '') {
                $flushLists();
                continue;
            }

            // Paragraph
            $flushLists();
            $html[] = '<p class="my-2 text-gray-700 leading-relaxed">' . $inline($line) . '</p>';
        }

        $flushLists();
        $closeTable();
        if ($inPre) $html[] = '</code></pre>';

        return implode("\n", $html);
    }
}
