-- Mengelompokkan menu saldo awal ke sidebar "Saldo Awal".
-- Aman untuk hosting: hanya update tabel menus, tidak menghapus atau mengubah data transaksi.

START TRANSACTION;

UPDATE menus
SET name = 'Saldo Awal Akun',
    section = 'Saldo Awal',
    sort_order = 1,
    description = 'Input saldo awal akun pembukuan.'
WHERE route_name = 'saldo-awal';

UPDATE menus
SET name = 'Saldo Awal Piutang',
    section = 'Saldo Awal',
    sort_order = 2,
    description = 'Input rincian saldo awal piutang tanpa jurnal.'
WHERE route_name = 'piutang';

UPDATE menus
SET name = 'Saldo Awal Hutang',
    section = 'Saldo Awal',
    sort_order = 3,
    description = 'Input rincian saldo awal hutang tanpa jurnal.'
WHERE route_name = 'saldo-awal-hutang';

UPDATE menus
SET name = 'Saldo Awal Piutang Sopir',
    section = 'Saldo Awal',
    sort_order = 4,
    description = 'Input rincian saldo awal piutang sopir tanpa jurnal.'
WHERE route_name = 'driver-opening-receivables';

COMMIT;
