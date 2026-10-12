<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Besar</title>
    <style>
        @page { margin: 22pt 24pt; }
        body { margin: 0; color: #111; font-family: DejaVu Sans, sans-serif; font-size: 8pt; }
        .header { margin-bottom: 9pt; text-align: center; line-height: 1.35; }
        .company { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .title { font-size: 11pt; font-weight: bold; }
        .account { margin-top: 3pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 0.6pt solid #222; padding: 3pt; vertical-align: top; }
        th { background: #e9ecef; font-weight: bold; }
        .date { width: 10%; }
        .number { width: 15%; }
        .description { width: 33%; }
        .amount { width: 14%; text-align: right; white-space: nowrap; }
        .account-row td { background: #e9ecef; font-weight: bold; }
        .summary { margin-top: 7pt; page-break-inside: avoid; }
        .summary td { border: 0; padding: 2pt 0 2pt 12pt; text-align: right; }
        .summary .label { font-weight: bold; }
        @if($dotMatrix ?? false)
        * { font-family: "DejaVu Sans Mono", monospace !important; color: #000 !important; background: transparent !important; }
        body { font-size: 9pt; }
        th, td { border-color: #000; padding: 3pt 4pt; }
        @endif
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ config('app.company_name') }}</div>
        <div class="title">LAPORAN BUKU BESAR</div>
        <div>Periode {{ date('d/m/Y', strtotime($tanggalAwal)) }} - {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</div>
        <div class="account">Akun: {{ $selectedCoa->kode_akun }} - {{ $selectedCoa->nama_akun }}</div>
    </div>

    <table>
        <thead><tr><th class="date">Tanggal</th><th class="number">No. Jurnal</th><th class="description">Deskripsi</th><th class="amount">Debit</th><th class="amount">Kredit</th><th class="amount">Saldo</th></tr></thead>
        <tbody>
            <tr class="account-row"><td colspan="6">{{ $selectedCoa->kode_akun }} - {{ $selectedCoa->nama_akun }}</td></tr>
            @php($runningBalance = 0)
            @forelse($items as $item)
                @php($runningBalance += in_array($selectedCoa->tipe, ['Aset', 'Beban'], true) ? $item->debit - $item->kredit : $item->kredit - $item->debit)
                <tr>
                    <td>{{ $item->jurnal->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->jurnal->no_jurnal }}</td>
                    <td>{{ $item->deskripsi ?? $item->jurnal->keterangan ?? '-' }}</td>
                    <td class="amount">{{ number_format($item->debit, 2, ',', '.') }}</td>
                    <td class="amount">{{ number_format($item->kredit, 2, ',', '.') }}</td>
                    <td class="amount">{{ number_format($runningBalance, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center">Tidak ada transaksi pada periode yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="label">Total Debit</td><td>{{ number_format($totalDebit, 2, ',', '.') }}</td></tr>
        <tr><td class="label">Total Kredit</td><td>{{ number_format($totalKredit, 2, ',', '.') }}</td></tr>
        <tr><td class="label">Saldo Akhir</td><td>{{ number_format($endingBalance, 2, ',', '.') }}</td></tr>
    </table>
</body>
</html>
