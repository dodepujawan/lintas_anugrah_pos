<?php

namespace App\Services;

use App\Exports\ImportTemplateExport;
use App\Models\Aph;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportDataService
{
    public function __construct(private readonly JournalService $journalService) {}

    public function import(string $type, UploadedFile $file, string $cutoverDate, User $user): int
    {
        $rows = Excel::toCollection(null, $file)->first();
        if (! $rows || $rows->count() < 2) {
            throw ValidationException::withMessages(['file' => 'File tidak memiliki baris data.']);
        }

        $headings = $rows->shift()
            ->map(fn ($value) => strtolower(trim((string) $value, " \t\n\r\0\x0B\xEF\xBB\xBF")))
            ->all();
        while ($headings !== [] && end($headings) === '') {
            array_pop($headings);
        }
        $expected = (new ImportTemplateExport($type))->headings();
        if ($headings !== $expected) {
            throw ValidationException::withMessages([
                'file' => 'Susunan kolom tidak sesuai template terbaru. Baris pertama wajib berisi: '.implode(', ', $expected).'.',
            ]);
        }

        $data = $rows->filter(fn ($row) => $row->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()->map(fn ($row) => array_combine($headings, array_pad($row->take(count($headings))->all(), count($headings), null)));

        if ($data->isEmpty()) {
            throw ValidationException::withMessages(['file' => 'File tidak memiliki baris data.']);
        }

        $this->validateRows($type, $data, Carbon::parse($cutoverDate)->startOfDay());

        return DB::transaction(function () use ($type, $data, $user) {
            foreach ($data as $row) {
                match ($type) {
                    'penjualan' => $this->sale($row, $user),
                    'hutang' => $this->purchase($row, $user),
                    'saldo_piutang' => $this->openingReceivable($row, $user),
                    'saldo_hutang' => $this->openingPayable($row, $user),
                };
            }

            return $data->count();
        });
    }

    private function validateRows(string $type, Collection $rows, Carbon $cutover): void
    {
        $invoiceField = in_array($type, ['penjualan', 'saldo_piutang'], true) ? 'no_invoice' : 'no_faktur';
        $dateField = match ($type) {
            'penjualan', 'hutang' => 'tanggal', 'saldo_piutang' => 'tanggal_invoice', default => 'tanggal_faktur'
        };
        $amountField = match ($type) {
            'penjualan' => 'total_invoice', 'hutang' => 'total_hutang', 'saldo_piutang' => 'saldo_piutang', default => 'saldo_hutang'
        };
        $opening = str_starts_with($type, 'saldo_');
        $seen = [];
        $customersInFile = [];
        $suppliersInFile = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            try {
                $date = $this->date($row[$dateField]);
            } catch (\Throwable) {
                $errors[] = "Baris {$line}: tanggal tidak valid.";

                continue;
            }
            $number = trim((string) $row[$invoiceField]);
            $amount = $this->number($row[$amountField]);
            if ($number === '') {
                $errors[] = "Baris {$line}: nomor faktur wajib diisi.";
            }
            if ($amount <= 0) {
                $errors[] = "Baris {$line}: nilai harus lebih dari nol.";
            }
            if (isset($seen[$number])) {
                $errors[] = "Baris {$line}: nomor faktur duplikat dalam file.";
            }
            $seen[$number] = true;
            if (($opening && ! $date->lt($cutover)) || (! $opening && $date->lt($cutover))) {
                $operator = $opening ? 'sebelum' : 'pada/ setelah';
                $errors[] = "Baris {$line}: tanggal harus {$operator} tanggal cutover.";
            }
            $model = in_array($type, ['penjualan', 'saldo_piutang'], true) ? Arh::class : Aph::class;
            if ($model::where('NOFAKTUR', $number)->exists()) {
                $errors[] = "Baris {$line}: faktur {$number} sudah ada.";
            }

            if (in_array($type, ['penjualan', 'saldo_piutang'], true)) {
                $customerCode = $this->customerCode($row['kode_pelanggan'] ?? null);
                $customerName = trim((string) ($row['nama_pelanggan'] ?? ''));

                if ($customerCode === '') {
                    $errors[] = "Baris {$line}: kode_pelanggan wajib diisi.";
                } elseif (mb_strlen($customerCode) > 30) {
                    $errors[] = "Baris {$line}: kode_pelanggan maksimal 30 karakter.";
                }

                if ($customerName === '') {
                    $errors[] = "Baris {$line}: nama_pelanggan wajib diisi agar customer baru dapat dibuat.";
                } elseif (mb_strlen($customerName) > 100) {
                    $errors[] = "Baris {$line}: nama_pelanggan maksimal 100 karakter.";
                }

                if ($customerCode !== '') {
                    if (isset($customersInFile[$customerCode]) && strcasecmp($customersInFile[$customerCode], $customerName) !== 0) {
                        $errors[] = "Baris {$line}: kode pelanggan {$customerCode} memiliki nama berbeda dalam file yang sama.";
                    } else {
                        $customersInFile[$customerCode] = $customerName;
                    }
                }
            }

            if (in_array($type, ['hutang', 'saldo_hutang'], true)) {
                $supplierCode = $this->supplierCode($row['kode_supplier'] ?? null);
                $supplierName = trim((string) ($row['nama_supplier'] ?? ''));

                if ($supplierCode === '') {
                    $errors[] = "Baris {$line}: kode_supplier wajib diisi.";
                } elseif (mb_strlen($supplierCode) > 6) {
                    $errors[] = "Baris {$line}: kode_supplier maksimal 6 karakter sesuai master supplier desktop.";
                }

                if ($supplierName === '') {
                    $errors[] = "Baris {$line}: nama_supplier wajib diisi agar supplier baru dapat dibuat.";
                } elseif (mb_strlen($supplierName) > 40) {
                    $errors[] = "Baris {$line}: nama_supplier maksimal 40 karakter sesuai master supplier desktop.";
                }

                if ($supplierCode !== '') {
                    if (isset($suppliersInFile[$supplierCode]) && strcasecmp($suppliersInFile[$supplierCode], $supplierName) !== 0) {
                        $errors[] = "Baris {$line}: kode supplier {$supplierCode} memiliki nama berbeda dalam file yang sama.";
                    } else {
                        $suppliersInFile[$supplierCode] = $supplierName;
                    }
                }
            }

            foreach ($this->accountFields($type, $row) as $field => $required) {
                $code = trim((string) ($row[$field] ?? ''));
                if ($required && $code === '') {
                    $errors[] = "Baris {$line}: {$field} wajib diisi.";

                    continue;
                }
                if ($code !== '' && ! Coa::where('kode_akun', $this->accountCode($code))->where('is_active', true)->exists()) {
                    $errors[] = "Baris {$line}: akun {$code} tidak ditemukan/aktif.";
                }
            }

            if ($type === 'penjualan' && abs($this->number($row['nilai_penjualan']) + $this->number($row['nilai_pajak']) - $amount) > .01) {
                $errors[] = "Baris {$line}: total invoice tidak sama dengan penjualan + pajak.";
            }
            if ($type === 'hutang' && abs($this->number($row['nilai_dpp']) + $this->number($row['nilai_pajak']) - $amount) > .01) {
                $errors[] = "Baris {$line}: total hutang tidak sama dengan DPP + pajak.";
            }
        }

        if ($opening && ! $errors) {
            $this->reconcileOpening($type, $rows, $cutover, $errors);
        }
        if ($errors) {
            throw ValidationException::withMessages(['file' => $errors]);
        }
    }

    private function reconcileOpening(string $type, Collection $rows, Carbon $cutover, array &$errors): void
    {
        $accountField = $type === 'saldo_piutang' ? 'kode_akun_piutang' : 'kode_akun_hutang';
        $amountField = $type === 'saldo_piutang' ? 'saldo_piutang' : 'saldo_hutang';
        foreach ($rows->groupBy(fn ($row) => $this->accountCode($row[$accountField])) as $code => $group) {
            $coa = Coa::where('kode_akun', $code)->first();
            $totals = collect(['jurnal_items', 'jurnal_items_archives'])
                ->map(function (string $table) use ($coa, $cutover) {
                    return DB::table($table)
                        ->join('jurnals', 'jurnals.id', '=', "{$table}.jurnal_id")
                        ->where("{$table}.coa_id", $coa->id)
                        ->where('jurnals.no_jurnal', 'like', 'SA%')
                        ->where('jurnals.is_posted', true)
                        ->whereDate('jurnals.tanggal', '<=', $cutover->toDateString())
                        ->selectRaw('COALESCE(SUM(debit),0) debit, COALESCE(SUM(kredit),0) kredit')
                        ->first();
                });
            $debit = $totals->sum(fn ($item) => (float) $item->debit);
            $kredit = $totals->sum(fn ($item) => (float) $item->kredit);
            $ledger = $type === 'saldo_piutang' ? $debit - $kredit : $kredit - $debit;
            $detail = $group->sum(fn ($row) => $this->number($row[$amountField]));

            // Rincian saldo awal boleh diimpor lebih dahulu sebelum jurnal saldo
            // awal dibuat. Jika saldo buku besar sudah tersedia, nilainya tetap
            // wajib sama agar subledger dan buku besar tidak berbeda.
            if (abs($ledger) <= .01) {
                continue;
            }

            if (abs($ledger - $detail) > .01) {
                $errors[] = "Rekonsiliasi akun {$code} gagal: jurnal saldo awal ".number_format($ledger, 2, ',', '.').", rincian faktur ".number_format($detail, 2, ',', '.').'. Pastikan akun dan total rincian sama dengan jurnal saldo awal yang sudah diposting.';
            }
        }
    }

    private function sale(array $r, User $user): void
    {
        $total = $this->number($r['total_invoice']);
        $tax = $this->number($r['nilai_pajak']);
        $items = [$this->item($r['kode_akun_piutang'], $total, 0, 'Piutang '.$r['no_invoice']), $this->item($r['kode_akun_penjualan'], 0, $this->number($r['nilai_penjualan']), 'Penjualan '.$r['no_invoice'])];
        if ($tax > 0) {
            $items[] = $this->item($r['kode_akun_pajak_keluaran'], 0, $tax, 'Pajak keluaran '.$r['no_invoice']);
        }
        $journal = $this->journalService->createJournal($this->journalService->generateJournalNumber('JU', $this->date($r['tanggal'])->toDateString()), $this->date($r['tanggal'])->toDateString(), 'Import penjualan '.$r['no_invoice'], $items, $r['no_invoice'], $user->id, 'Penjualan', 'Penjualan');
        $this->ar($r, $total, $journal->no_jurnal, 'IMPORT_PENJUALAN', $user, 'tanggal', 'no_invoice', 'kode_pelanggan');
    }

    private function purchase(array $r, User $user): void
    {
        $total = $this->number($r['total_hutang']);
        $tax = $this->number($r['nilai_pajak']);
        $items = [$this->item($r['kode_akun_beban_pembelian'], $this->number($r['nilai_dpp']), 0, 'Pembelian '.$r['no_faktur'])];
        if ($tax > 0) {
            $items[] = $this->item($r['kode_akun_pajak_masukan'], $tax, 0, 'Pajak masukan '.$r['no_faktur']);
        }
        $items[] = $this->item($r['kode_akun_hutang'], 0, $total, 'Hutang '.$r['no_faktur']);
        $journal = $this->journalService->createJournal($this->journalService->generateJournalNumber('JU', $this->date($r['tanggal'])->toDateString()), $this->date($r['tanggal'])->toDateString(), 'Import pembelian '.$r['no_faktur'], $items, $r['no_faktur'], $user->id, 'Pembelian', 'Pembelian');
        $this->ap($r, $total, $journal->no_jurnal, 'IMPORT_PEMBELIAN', $user, 'tanggal');
    }

    private function openingReceivable(array $r, User $user): void
    {
        $this->ar($r, $this->number($r['saldo_piutang']), null, 'SALDO_AWAL_IMPORT', $user, 'tanggal_invoice', 'no_invoice', 'kode_pelanggan');
    }

    private function openingPayable(array $r, User $user): void
    {
        $this->ap($r, $this->number($r['saldo_hutang']), null, 'SALDO_AWAL_IMPORT', $user, 'tanggal_faktur');
    }

    private function ar(array $r, float $amount, ?string $journal, string $source, User $user, string $date, string $number, string $party): void
    {
        $customerCode = $this->ensureCustomer($r, $user, $party);

        Arh::create(['NOFAKTUR' => $r[$number], 'TGLFAKTUR' => $this->date($r[$date]), 'TGLJT' => $this->nullableDate($r['jatuh_tempo']), 'CUSTOMER' => $customerCode, 'PIUTANG' => $amount, 'BAYAR' => 0, 'SALDO' => $amount, 'KETERANGAN' => $r['keterangan'], 'STATUS' => 'OPEN', 'SOURCE' => $source, 'NOJURNAL' => $journal, 'USER' => $user->name]);
    }

    private function ensureCustomer(array $row, User $user, string $partyField): string
    {
        $code = $this->customerCode($row[$partyField] ?? null);
        $name = trim((string) ($row['nama_pelanggan'] ?? ''));
        $customer = DB::table('mcustomer')
            ->where('CUSTOMER', $code)
            ->lockForUpdate()
            ->first();

        if (! $customer) {
            DB::table('mcustomer')->insert([
                'CUSTOMER' => $code,
                'kode_cus' => $code,
                'NAMACUST' => $name,
                'SETATUS' => 'A',
                'TGL_UPDATE' => now()->toDateString(),
                'USERID' => mb_substr((string) ($user->username ?: $user->name), 0, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (trim((string) $customer->NAMACUST) === '' && $name !== '') {
            DB::table('mcustomer')->where('id', $customer->id)->update([
                'NAMACUST' => $name,
                'TGL_UPDATE' => now()->toDateString(),
                'USERID' => mb_substr((string) ($user->username ?: $user->name), 0, 20),
                'updated_at' => now(),
            ]);
        }

        return $code;
    }

    private function ap(array $r, float $amount, ?string $journal, string $source, User $user, string $date): void
    {
        $supplierCode = $this->ensureSupplier($r);

        Aph::create(['NOFAKTUR' => $r['no_faktur'], 'TGLFAKTUR' => $this->date($r[$date]), 'TGLJT' => $this->nullableDate($r['jatuh_tempo']), 'SUPPLIER' => $supplierCode, 'HUTANG' => $amount, 'BAYAR' => 0, 'SALDO' => $amount, 'KETERANGAN' => $r['keterangan'], 'STATUS' => 'OPEN', 'SOURCE' => $source, 'NOJURNAL' => $journal, 'USER' => $user->name]);
    }

    private function ensureSupplier(array $row): string
    {
        $code = $this->supplierCode($row['kode_supplier'] ?? null);
        $name = trim((string) ($row['nama_supplier'] ?? ''));
        $supplier = DB::table('msupplier')
            ->where('SUPPLIER', $code)
            ->lockForUpdate()
            ->first();

        if (! $supplier) {
            DB::table('msupplier')->insert([
                'SUPPLIER' => $code,
                'NAMA' => $name,
                'ALAMAT1' => '',
                'ALAMAT2' => '',
                'KOTA' => '',
                'TELEPON' => '',
                'FAX' => '',
                'EMAIL' => '',
                'KONTAK' => '',
                'NOREK' => '',
                'BANK' => '',
                'ATASNAMA' => '',
                'SALDO' => 0,
                'RETURAN' => 0,
                'FTOP' => 0,
                'DISC_REG' => 0,
            ]);
        } elseif (trim((string) $supplier->NAMA) === '' && $name !== '') {
            DB::table('msupplier')->where('SUPPLIER', $code)->update([
                'NAMA' => $name,
            ]);
        }

        return $code;
    }

    private function accountFields(string $type, array $row): array
    {
        return match ($type) {
            'penjualan' => ['kode_akun_piutang' => true, 'kode_akun_penjualan' => true, 'kode_akun_pajak_keluaran' => $this->number($row['nilai_pajak']) > 0], 'hutang' => ['kode_akun_beban_pembelian' => true, 'kode_akun_hutang' => true, 'kode_akun_pajak_masukan' => $this->number($row['nilai_pajak']) > 0], 'saldo_piutang' => ['kode_akun_piutang' => true], default => ['kode_akun_hutang' => true]
        };
    }

    private function item(string $code, float $debit, float $credit, string $description): array
    {
        return ['coa_id' => Coa::where('kode_akun', $this->accountCode($code))->value('id'), 'deskripsi' => $description, 'debit' => $debit, 'kredit' => $credit];
    }

    private function accountCode(mixed $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return strlen($digits) === 5 ? $digits.'000' : str_pad($digits, 8, '0', STR_PAD_LEFT);
    }

    private function customerCode(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }

    private function supplierCode(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }

    private function number(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

return (float) str_replace(',', '.', str_replace('.', '', trim((string) $value)));
    }

    private function date(mixed $value): Carbon
    {
        return is_numeric($value) ? Carbon::instance(Date::excelToDateTimeObject($value))->startOfDay() : Carbon::parse($value)->startOfDay();
    }

    private function nullableDate(mixed $value): ?Carbon
    {
        return $value === null || $value === '' ? null : $this->date($value);
    }
}
