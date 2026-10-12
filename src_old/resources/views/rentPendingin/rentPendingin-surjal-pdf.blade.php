<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Jalan Sewa Unit Pendingin</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }
        .company-sub {
            font-size: 13px;
            margin: 2px 0 8px 0;
        }
        .address {
            font-size: 10px;
            margin: 3px 0;
        }
        .form-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 15px 0;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        .bordered {
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .conditions-table td {
            height: 22px;
        }
        .vehicle-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .signature-table {
            margin-top: 25px;
        }
        .signature-table td {
            padding: 6px;
            vertical-align: top;
            border: 1px solid #000;
        }
        .note {
            font-size: 9px;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }
        .color-code {
            font-size: 9px;
            margin-top: 8px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .mb-8 { margin-bottom: 8px; }
        .mt-15 { margin-top: 15px; }
        .checkmark {
            font-family: "DejaVu Sans";
            font-size: 14px;
        }
        .no-border { border: none !important; }
        .border-top { border-top: 1px solid #000 !important; }
        .border-bottom { border-bottom: 1px solid #000 !important; }
        .w-25 { width: 25%; }
        .w-50 { width: 50%; }
        .w-75 { width: 75%; }
        .nowrap { white-space: nowrap; }

        /* Untuk checkbox yang dicentang */
        .checked:before {
            content: "✓";
            margin-right: 3px;
        }
        .unchecked:before {
            content: "□";
            margin-right: 3px;
        }
    </style>
</head>
<body>

    <div class="header">
        <p class="company-name">PT. LINTAS MITRA ANUGERAH SEJATI</p>
        <p class="company-sub">COLD CHAIN DISTRIBUTION & STORAGE</p>
        <p class="address">Cool Room : Jl. Bajataki IV, No. 9 Denpasar</p>
        <p class="address">Telp. / Fax. : (0361) 8947610</p>
    </div>

    <div class="form-title">SURAT JALAN SEWA UNIT</div>

    <table class="mb-8">
        @if(!empty($expedisi->NOMUAT))
        <tr>
            <td width="25%"><strong>TANGGAL SJ.</strong></td>
            <td width="25%">{{ date('d-m-Y', strtotime($expedisi->tglsj)) }}</td>
            <td width="25%"><strong>TANGGAL MUAT.</strong></td>
            <td width="25%">{{ date('d-m-Y', strtotime($expedisi->TGLMUAT)) }}</td>
        </tr>
        <tr>
            <td><strong>NOMOR SJ.</strong></td>
            <td width="25%">{{ $expedisi->NOSJ }}</td>
            <td><strong>NOMOR MUAT.</strong></td>
            <td>{{ $expedisi->NOMUAT }}</td>
        </tr>
        <tr>
            <td><strong>NAMA KONSUMEN</strong></td>
            <td colspan="3">
                {{ $expedisi->CUSTOMER ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><strong>ALAMAT</strong></td>
            <td colspan="3">{{ $expedisi->P_ALAMAT ?? '-' }}</td>
        </tr>
        @else
        <tr>
            <td width="25%"><strong>TANGGAL SJ.</strong></td>
            <td colspan="3">{{ date('d-m-Y', strtotime($expedisi->tglsj)) }}</td>
        </tr>
        <tr>
            <td><strong>NOMOR SJ.</strong></td>
            <td colspan="3">{{ $expedisi->NOSJ }}</td>
        </tr>
        <tr>
            <td><strong>NAMA KONSUMEN</strong></td>
            <td colspan="3">
                {{ $expedisi->CUSTOMER ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><strong>ALAMAT</strong></td>
            <td colspan="3">{{ $expedisi->P_ALAMAT ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">SYARAT, KETENTUAN DAN SPESIFIKASI UNIT KENDARAAN</div>
    <table class="conditions-table">
        <tr>
            <td width="20%"><strong>JANGKA WAKTU SEWA</strong></td>
            <td width="30%">
                @php
                    $jumlahHari = $expedisi->JUMLAH ?? 1;
                    $jenisSewa = 'Harian';

                    // Logika jangka waktu sewa
                    if ($jumlahHari == 1) {
                        $jenisSewa = 'Harian (24 Jam)';
                    } elseif ($jumlahHari >= 30) {
                        $jenisSewa = 'Bulanan';
                    } else {
                        $jenisSewa = 'Harian';
                    }
                @endphp
                <table>
                    @if($jumlahHari == 1)
                    <tr>
                        <td>Harian</td>
                        <td class="checked">24 Jam</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Bulanan</td>
                        <td colspan="2">-</td>
                    </tr>
                    @elseif($jumlahHari >= 30)
                    <tr>
                        <td>Harian</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Bulanan</td>
                        <td colspan="2" class="checked">{{ $jumlahHari }} Hari</td>
                    </tr>
                    @else
                    <tr>
                        <td>Harian</td>
                        <td class="checked">{{ $jumlahHari }} Hari</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Bulanan</td>
                        <td colspan="2">-</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td width="25%"><strong>Syarat DAN KETENTUAN</strong></td>
            <td width="25%">
                @php
                    // Asumsi: selalu dengan sopir dan BBM
                    $withDriver = true;
                    $withFuel = true;
                    $withAC = true; // Asumsi pendingin selalu dengan AC
                @endphp
                @if($withDriver)<span class="checked">Sopir</span><br>@else<span class="unchecked">Sopir</span><br>@endif
                @if(!$withDriver)<span class="checked">Tanpa Sopir</span><br>@else<span class="unchecked">Tanpa Sopir</span><br>@endif
                @if($withFuel)<span class="checked">BBM</span><br>@else<span class="checked">Tanpa BBM</span><br>@endif
                @if($withAC)<span class="checked">Ber AC</span><br>@else<span class="unchecked">Tanpa AC</span><br>@endif
            </td>
        </tr>
        <tr>
            <td><strong>PENGIRIMAN UNIT</strong></td>
            <td colspan="3">
                @php
                    // Asumsi: unit dikirim ke lokasi
                    $deliveryType = 'Dikirim';
                @endphp
                @if($deliveryType == 'Dikirim')
                <span class="checked">Dikirim</span>
                <span class="unchecked" style="margin-left: 20px;">Diambil</span>
                @else
                <span class="unchecked">Dikirim</span>
                <span class="checked" style="margin-left: 20px;">Diambil</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title mt-15">DETAIL UNIT KENDARAAN</div>
    <table class="vehicle-table">
        <thead>
            <tr>
                <th width="5%">NO.</th>
                <th width="25%">NAMA UNIT KENDARAAN</th>
                <th colspan="5">SPESIFIKASI TEKNIS DAN INFORMASI UNIT</th>
                <th colspan="2">KONDISI KENDARAAN</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th width="12%">NO. PELAT</th>
                <th width="10%">KAP. MUAT</th>
                <th width="10%">KM. AWAL</th>
                <th width="12%">NAMA SOPIR</th>
                <th width="10%">ISI BBM</th>
                <th width="8%">DISERAHKAN</th>
                <th width="8%">KEMBALI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $expedisi->NAMA_KENDARAAN ?? 'UNIT RENT PENDINGIN' }}</td>
                <td class="text-center">{{ $expedisi->PLAT ?? '-' }}</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center">{{ $expedisi->NAMA_DRIVER ?? '-' }}</td>
                <td class="text-center">-</td>
                <td class="text-center"></td>
                <td class="text-center"></td>
            </tr>
            <!-- Baris kosong untuk tambahan unit -->
            <tr><td colspan="9" style="height: 15px;"></td></tr>
            <tr><td colspan="9" style="height: 15px;"></td></tr>
        </tbody>
    </table>

    <div class="note">
        <strong>KONDISI KENDARAAN : BL (Baik dan lengkap)</strong><br>
        Surat Jalan ini dilengkapi dengan hasil pemeriksaan fisik unit<br>
        Isi kolom nama sopir dan isi BBM bila kolom sopir dan BBM dipilih
    </div>

    <div class="section-title mt-15">KOLOM PENGESAHAN</div>
    <h3>{{ $expedisi->catatan ?? '-' }}</h3>
    <table class="signature-table">
        <tr>
            <td width="33%" style="vertical-align: top; padding: 10px;">
                <div style="text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 12px;">
                    DIPERIKSA TEKNISI TRUCKING
                </div>

                <div style="margin-bottom: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">NAMA</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">JABATAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TGL. DIPERIKSA</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TANDA TANGAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td width="33%" style="vertical-align: top; padding: 10px;">
                <div style="text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 12px;">
                    DISERAHKAN
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">NAMA</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">JABATAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TGL. DIKIRIM</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TANDA TANGAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td width="34%" style="vertical-align: top; padding: 10px;">
                <div style="text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 12px;">
                    DITERIMA
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">NAMA</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">JABATAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TGL. DITERIMA</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; border: none; padding: 3px 0; font-size: 10px;">TANDA TANGAN</td>
                            <td style="width: 5%; border: none; padding: 3px 0; text-align: center; font-size: 10px;">:</td>
                            <td style="border: none; padding: 3px 0; border-bottom: 1px solid #000; font-size: 10px;">&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="note color-code">
        <p><strong>PT. Lintas Mitra Anugerah Sejati, telah menyerahkan unit kendaraan dalam kondisi baik dan lengkap, kerusakan dalam jumlah yang tidak wajar menjadi tanggungjawab penyewa</strong></p>

    </div>

    <!-- Informasi tambahan untuk internal -->
    <div style="margin-top: 5px; font-size: 9px; color: #666; border-top: 1px dashed #ccc; padding-top: 5px;">
        <strong>INFORMASI INTERNAL:</strong><br>
        No. SJ: {{ $expedisi->NOSJ }} | Customer: {{ $expedisi->CUSTOMER }} |
        Jumlah: {{ rtrim(rtrim(number_format($expedisi->JUMLAH, 3, '.', ''), '0'), '.') }} {{ $expedisi->UNIT }} |
        Harga: Rp {{ number_format($expedisi->HARGA, 0, ',', '.') }} |
        Subtotal: Rp {{ number_format(($expedisi->JUMLAH * $expedisi->HARGA), 0, ',', '.') }}<br>
        Disc: {{ $expedisi->DISC ?? 0 }}% (Rp {{ number_format($expedisi->NDISC ?? 0, 0, ',', '.') }}) |
        PPN: {{ $expedisi->PPN ?? 0 }}% |
        Grand Total: Rp {{ number_format($expedisi->GRAND, 0, ',', '.') }}
    </div>

</body>
</html>
