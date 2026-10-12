<?php

if (! function_exists('terbilang')) {
    function terbilang($angka)
    {
        $angka = (float) $angka;
        if ($angka == 0) {
            return 'Nol';
        }
        $bil = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) {
            return $bil[$angka];
        } elseif ($angka < 20) {
            return terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            return terbilang(floor($angka / 10)) . ' Puluh' . ($angka % 10 ? ' ' . terbilang($angka % 10) : '');
        } elseif ($angka < 200) {
            return 'Seratus' . ($angka > 100 ? ' ' . terbilang($angka - 100) : '');
        } elseif ($angka < 1000) {
            return terbilang(floor($angka / 100)) . ' Ratus' . ($angka % 100 ? ' ' . terbilang($angka % 100) : '');
        } elseif ($angka < 2000) {
            return 'Seribu' . ($angka > 1000 ? ' ' . terbilang($angka - 1000) : '');
        } elseif ($angka < 1000000) {
            return terbilang(floor($angka / 1000)) . ' Ribu' . ($angka % 1000 ? ' ' . terbilang($angka % 1000) : '');
        } elseif ($angka < 2000000) {
            return 'Satu Juta' . ($angka > 1000000 ? ' ' . terbilang($angka - 1000000) : '');
        } elseif ($angka < 1000000000) {
            return terbilang(floor($angka / 1000000)) . ' Juta' . ($angka % 1000000 ? ' ' . terbilang($angka % 1000000) : '');
        } elseif ($angka < 2000000000) {
            return 'Satu Miliar' . ($angka > 1000000000 ? ' ' . terbilang($angka - 1000000000) : '');
        } else {
            return terbilang(floor($angka / 1000000000)) . ' Miliar' . ($angka % 1000000000 ? ' ' . terbilang($angka % 1000000000) : '');
        }
    }
}

if (! function_exists('terbilang_angka')) {
    function terbilang_angka($angka)
    {
        $angka = (float) $angka;
        if ($angka < 1) {
            return 'Kosong';
        }
        return terbilang(floor($angka)) . ' Rupiah';
    }
}
