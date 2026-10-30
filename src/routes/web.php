<?php

use App\Http\Controllers\AccountMappingController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuditSelisihController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\CoaSaldoNormalAuditController;
use App\Http\Controllers\CoolroomController;
use App\Http\Controllers\CoolroomGenerateInvoiceController;
use App\Http\Controllers\CoolroomKwitansiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverOpeningReceivableController;
use App\Http\Controllers\DriverPaymentController;
use App\Http\Controllers\DriverReceivableController;
use App\Http\Controllers\DriverReceivableReportController;
use App\Http\Controllers\ExpedisiController;
use App\Http\Controllers\ExpedisiGenerateInvoiceController;
use App\Http\Controllers\ExpedisiInvoiceController;
use App\Http\Controllers\ExpedisiKwitansiController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KwitansiHistoryController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanHutangController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\LaporanPiutangController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\PelunasanHutangController;
use App\Http\Controllers\PelunasanPiutangController;
use App\Http\Controllers\PenjualanHostingController;
use App\Http\Controllers\PeriodLockController;
use App\Http\Controllers\PiutangBerjalanController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\PriceCusController;
use App\Http\Controllers\PricedinginController;
use App\Http\Controllers\PricedingincusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\RentPendinginController;
use App\Http\Controllers\RentPendinginGenerateInvoiceController;
use App\Http\Controllers\RentPendinginKwitansiController;
use App\Http\Controllers\SaldoAwalController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->defaultAuthorizedRoute());
    }

    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('customers/duplicates', [CustomerController::class, 'duplicates'])->name('customers.duplicates');
    Route::get('customers/duplicates/export', [CustomerController::class, 'exportDuplicates'])->name('customers.duplicates.export');
    Route::get('customers/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup');
    Route::post('customers/merge', [CustomerController::class, 'merge'])->name('customers.merge');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('drivers', DriverController::class)->except('show');
    Route::resource('driver-receivables', DriverReceivableController::class)->except('show');
    Route::get('driver-receivables/{driver_receivable}', [DriverReceivableController::class, 'show'])->name('driver-receivables.show');
    Route::get('driver-opening-receivables-export', [DriverOpeningReceivableController::class, 'export'])->name('driver-opening-receivables.export');
    Route::resource('driver-opening-receivables', DriverOpeningReceivableController::class)->except('show');
    Route::get('driver-payments', [DriverPaymentController::class, 'index'])->name('driver-payments.index');
    Route::get('driver-payments/create', [DriverPaymentController::class, 'create'])->name('driver-payments.create');
    Route::get('driver-payments/{driver_payment}/pdf', [DriverPaymentController::class, 'receiptPdf'])->name('driver-payments.pdf');
    Route::get('driver-payments/{driver_payment}/edit', [DriverPaymentController::class, 'edit'])->name('driver-payments.edit');
    Route::post('driver-payments', [DriverPaymentController::class, 'store'])->name('driver-payments.store');
    Route::put('driver-payments/{driver_payment}', [DriverPaymentController::class, 'update'])->name('driver-payments.update');
    Route::post('driver-payments/{driver_payment}/void', [DriverPaymentController::class, 'void'])->name('driver-payments.void');
    Route::get('driver-payments/{driver_payment}', [DriverPaymentController::class, 'show'])->name('driver-payments.show');
    Route::get('laporan/piutang-sopir/export', [DriverReceivableReportController::class, 'export'])->name('laporan.driver-receivables.export');
    Route::get('laporan/piutang-sopir/pdf', [DriverReceivableReportController::class, 'pdf'])->name('laporan.driver-receivables.pdf');
    Route::get('laporan/piutang-sopir', [DriverReceivableReportController::class, 'index'])->name('laporan.driver-receivables');
    Route::get('penjualan-hosting', [PenjualanHostingController::class, 'index'])->name('penjualan-hosting.index');
    Route::get('penjualan-hosting/export-bermasalah', [PenjualanHostingController::class, 'exportProblems'])->name('penjualan-hosting.export-problems');
    Route::get('penjualan-hosting/export-posted', [PenjualanHostingController::class, 'exportPosted'])->name('penjualan-hosting.export-posted');
    Route::post('penjualan-hosting/post', [PenjualanHostingController::class, 'post'])->name('penjualan-hosting.post');
    Route::post('penjualan-hosting/{invoice}/rejurnal', [PenjualanHostingController::class, 'repost'])->name('penjualan-hosting.repost');
    Route::post('penjualan-hosting/{invoice}/void', [PenjualanHostingController::class, 'void'])->name('penjualan-hosting.void');
    Route::post('penjualan-hosting/{invoice}/revoid', [PenjualanHostingController::class, 'revoid'])->name('penjualan-hosting.revoid');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/users', [SettingsController::class, 'store'])->name('settings.users.store');
    Route::put('/settings/users/{user}', [SettingsController::class, 'update'])->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'destroy'])->name('settings.users.destroy');

    Route::post('/settings/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
    Route::put('/settings/roles/{role}', [SettingsController::class, 'updateRole'])->name('settings.roles.update');
    Route::delete('/settings/roles/{role}', [SettingsController::class, 'destroyRole'])->name('settings.roles.destroy');

    Route::post('/settings/menus', [SettingsController::class, 'storeMenu'])->name('settings.menus.store');
    Route::put('/settings/menus/{menu}', [SettingsController::class, 'updateMenu'])->name('settings.menus.update');
    Route::delete('/settings/menus/{menu}', [SettingsController::class, 'destroyMenu'])->name('settings.menus.destroy');

    Route::post('/settings/roles/{role}/permissions', [SettingsController::class, 'updateRolePermissions'])->name('settings.roles.permissions.update');

    Route::get('period-locks', [PeriodLockController::class, 'index'])->name('period-locks.index');
    Route::post('period-locks', [PeriodLockController::class, 'store'])->name('period-locks.store');
    Route::delete('period-locks/{year}', [PeriodLockController::class, 'destroy'])->name('period-locks.destroy');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::get('validation-dashboard', [ValidationDashboardController::class, 'index'])->name('validation-dashboard');
    Route::get('audit-selisih', [AuditSelisihController::class, 'index'])->name('audit-selisih');
    Route::get('import-data', [ImportDataController::class, 'index'])->name('import-data.index');
    Route::get('import-data/template/{type}', [ImportDataController::class, 'template'])->name('import-data.template');
    Route::post('import-data/{type}', [ImportDataController::class, 'store'])->name('import-data.store');

    // User management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Backup
    Route::get('backup', [BackupController::class, 'index'])->name('backup');
    Route::post('backup/run', [BackupController::class, 'run'])->name('backup.run');
    Route::get('backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');

    /*
    |--------------------------------------------------------------------------
    | COA
    |--------------------------------------------------------------------------
    */
    Route::get('coa/export', [CoaController::class, 'export'])->name('coa.export');
    Route::resource('coa', CoaController::class);

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT MAPPING
    |--------------------------------------------------------------------------
    */
    Route::get('account-mappings', [AccountMappingController::class, 'index'])
        ->name('account-mappings.index');
    Route::post('account-mappings', [AccountMappingController::class, 'store'])
        ->name('account-mappings.store');

    Route::get('coa-saldo-normal', [CoaSaldoNormalAuditController::class, 'index'])
        ->name('coa-saldo-normal');
    Route::post('coa-saldo-normal/fix', [CoaSaldoNormalAuditController::class, 'fix'])
        ->name('coa-saldo-normal.fix');

    /*
    |--------------------------------------------------------------------------
    | JURNAL
    |--------------------------------------------------------------------------
    */
    Route::post('jurnal/draft/save', [JurnalController::class, 'saveDraft'])->name('jurnal.draft.save');

    Route::get('jurnal/{jurnal}/pdf', [JurnalController::class, 'receiptPdf'])->name('jurnal.pdf');

    Route::resource('jurnal', JurnalController::class);

    Route::post('jurnal/{jurnal}/post', [JurnalController::class, 'post'])->name('jurnal.post');
    Route::post('jurnal/{jurnal}/void', [JurnalController::class, 'void'])->name('jurnal.void');

    /*
    |--------------------------------------------------------------------------
    | SALDO AWAL AKUN
    |--------------------------------------------------------------------------
    */
    Route::post('saldo-awal/draft/save', [SaldoAwalController::class, 'saveDraft'])->name('saldo-awal.draft.save');
    Route::get('saldo-awal/export', [SaldoAwalController::class, 'export'])->name('saldo-awal.export');
    Route::get('saldo-awal/pdf', [SaldoAwalController::class, 'pdf'])->name('saldo-awal.pdf');
    Route::get('saldo-awal/{saldo_awal}/pdf', [SaldoAwalController::class, 'pdfDetail'])->name('saldo-awal.detail.pdf');
    Route::resource('saldo-awal', SaldoAwalController::class);
    Route::post('saldo-awal/{saldo_awal}/post', [SaldoAwalController::class, 'post'])->name('saldo-awal.post');
    Route::post('saldo-awal/{saldo_awal}/reopen', [SaldoAwalController::class, 'reopen'])->name('saldo-awal.reopen');

    /*
    |--------------------------------------------------------------------------
    | JURNAL PENYESUAIAN
    |--------------------------------------------------------------------------
    */
    Route::post('jurnal-penyesuaian/draft/save', [JurnalPenyesuaianController::class, 'saveDraft'])->name('jurnal-penyesuaian.draft.save');
    Route::get('jurnal-penyesuaian/export', [JurnalPenyesuaianController::class, 'export'])->name('jurnal-penyesuaian.export');
    Route::get('jurnal-penyesuaian/pdf', [JurnalPenyesuaianController::class, 'pdf'])->name('jurnal-penyesuaian.pdf');
    Route::resource('jurnal-penyesuaian', JurnalPenyesuaianController::class);
    Route::post('jurnal-penyesuaian/{jurnal_penyesuaian}/post', [JurnalPenyesuaianController::class, 'post'])->name('jurnal-penyesuaian.post');

    /*
    |--------------------------------------------------------------------------
    | PIUTANG (SALDO AWAL)
    |--------------------------------------------------------------------------
    */
    Route::get('piutang-berjalan', [PiutangBerjalanController::class, 'index'])
        ->name('piutang-berjalan.index');
    Route::get('piutang-berjalan/create', [PiutangBerjalanController::class, 'create'])
        ->name('piutang-berjalan.create');
    Route::post('piutang-berjalan', [PiutangBerjalanController::class, 'store'])
        ->name('piutang-berjalan.store');
    Route::get('piutang-berjalan/{piutang}/edit', [PiutangBerjalanController::class, 'edit'])->name('piutang-berjalan.edit');
    Route::put('piutang-berjalan/{piutang}', [PiutangBerjalanController::class, 'update'])->name('piutang-berjalan.update');
    Route::delete('piutang-berjalan/{piutang}', [PiutangBerjalanController::class, 'destroy'])->name('piutang-berjalan.destroy');

    Route::get('piutang/pdf', [PiutangController::class, 'pdf'])->name('piutang.pdf');
    Route::get(
        'piutang',
        [PiutangController::class, 'index']
    )->name('piutang.index');

    Route::get(
        'piutang/create',
        [PiutangController::class, 'create']
    )->name('piutang.create');

    Route::get('piutang-export', [PiutangController::class, 'export'])->name('piutang.export');

    Route::post(
        'piutang',
        [PiutangController::class, 'store']
    )->name('piutang.store');

    Route::get(
        'piutang/pelanggan/search',
        [PiutangController::class, 'searchPelanggan']
    )->name('piutang.search-pelanggan');

    Route::get('piutang/{piutang}/edit', [PiutangController::class, 'edit'])->name('piutang.edit');
    Route::put('piutang/{piutang}', [PiutangController::class, 'update'])->name('piutang.update');
    Route::delete('piutang/{piutang}', [PiutangController::class, 'destroy'])->name('piutang.destroy');
    Route::get('piutang/{piutang}', [PiutangController::class, 'show'])->name('piutang.show');

    /*
    |--------------------------------------------------------------------------
    | PELUNASAN PIUTANG
    |--------------------------------------------------------------------------
    */
    // Custom routes first (more specific)
    Route::get(
        'pelunasan-piutang/search-customer',
        [PelunasanPiutangController::class, 'searchCustomer']
    )->name('pelunasan-piutang.search-customer');

    Route::get(
        'pelunasan-piutang/customer-invoices',
        [PelunasanPiutangController::class, 'customerInvoices']
    )->name('pelunasan-piutang.customer-invoices');
    Route::post('pelunasan-piutang/{pelunasan_piutang}/void', [PelunasanPiutangController::class, 'void'])->name('pelunasan-piutang.void');
    Route::get('pelunasan-piutang/{pelunasan_piutang}/export', [PelunasanPiutangController::class, 'exportReceipt'])->name('pelunasan-piutang.export');
    Route::get('pelunasan-piutang/{pelunasan_piutang}/pdf', [PelunasanPiutangController::class, 'receiptPdf'])->name('pelunasan-piutang.pdf');

    // Resource routes (less specific)
    Route::resource(
        'pelunasan-piutang',
        PelunasanPiutangController::class
    )->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
    ]);

    /*
    |--------------------------------------------------------------------------
    | HUTANG
    |--------------------------------------------------------------------------
    */
    Route::get('saldo-awal-hutang', [HutangController::class, 'openingIndex'])
        ->name('saldo-awal-hutang.index');
    Route::get('saldo-awal-hutang/export/excel', [HutangController::class, 'exportSaldoAwal'])
        ->name('saldo-awal-hutang.export');
    Route::get('saldo-awal-hutang/pdf', [HutangController::class, 'pdfSaldoAwal'])->name('saldo-awal-hutang.pdf');
    Route::get('saldo-awal-hutang/create', [HutangController::class, 'createSaldoAwal'])
        ->name('saldo-awal-hutang.create');
    Route::post('saldo-awal-hutang', [HutangController::class, 'storeSaldoAwal'])
        ->name('saldo-awal-hutang.store');
    Route::get('saldo-awal-hutang/{hutang}/edit', [HutangController::class, 'editSaldoAwal'])->name('saldo-awal-hutang.edit');
    Route::put('saldo-awal-hutang/{hutang}', [HutangController::class, 'updateSaldoAwal'])->name('saldo-awal-hutang.update');
    Route::delete('saldo-awal-hutang/{hutang}', [HutangController::class, 'destroySaldoAwal'])->name('saldo-awal-hutang.destroy');
    Route::get('hutang/supplier/search', [HutangController::class, 'searchSupplier'])
        ->name('hutang.search-supplier');
    Route::resource('hutang', HutangController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | PELUNASAN HUTANG
    |--------------------------------------------------------------------------
    */
    Route::get('pelunasan-hutang/search-supplier', [PelunasanHutangController::class, 'searchSupplier'])
        ->name('pelunasan-hutang.search-supplier');
    Route::get('pelunasan-hutang/supplier-invoices', [PelunasanHutangController::class, 'supplierInvoices'])
        ->name('pelunasan-hutang.supplier-invoices');
    Route::post('pelunasan-hutang/{pelunasan_hutang}/void', [PelunasanHutangController::class, 'void'])->name('pelunasan-hutang.void');
    Route::resource('pelunasan-hutang', PelunasanHutangController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | LAPORAN
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/laporan',
        [LaporanController::class, 'index']
    )->name('laporan.index');

    Route::get('/laporan/piutang', [LaporanPiutangController::class, 'index'])
        ->name('laporan.piutang');
    Route::get('/laporan/piutang/export', [LaporanPiutangController::class, 'export'])
        ->name('laporan.piutang.export');
    Route::get('/laporan/piutang/pdf', [LaporanPiutangController::class, 'pdf'])
        ->name('laporan.piutang.pdf');
    Route::get('/laporan/penjualan', [LaporanPenjualanController::class, 'index'])
        ->name('laporan.penjualan');
    Route::get('/laporan/penjualan/export', [LaporanPenjualanController::class, 'export'])
        ->name('laporan.penjualan.export');
    Route::get('/laporan/penjualan/pdf', [LaporanPenjualanController::class, 'pdf'])
        ->name('laporan.penjualan.pdf');
    Route::get('/laporan/hutang', [LaporanHutangController::class, 'index'])->name('laporan.hutang');
    Route::get('/laporan/hutang/export', [LaporanHutangController::class, 'export'])->name('laporan.hutang.export');
    Route::get('/laporan/hutang/pdf', [LaporanHutangController::class, 'pdf'])->name('laporan.hutang.pdf');

    /*
    |--------------------------------------------------------------------------
    | PANDUAN (Markdown)
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/panduan/tutup-buku',
        [PanduanController::class, 'tutupBuku']
    )->name('panduan.tutup_buku');

    Route::get(
        '/panduan/jurnal-umum',
        [PanduanController::class, 'jurnalUmum']
    )->name('panduan.jurnal_umum');

    Route::get(
        '/panduan/jurnal-penyesuaian',
        [PanduanController::class, 'jurnalPenyesuaian']
    )->name('panduan.jurnal_penyesuaian');

    Route::get(
        '/panduan/pelunasan-piutang',
        [PanduanController::class, 'pelunasanPiutang']
    )->name('panduan.pelunasan_piutang');

    Route::get(
        '/panduan/piutang-sopir',
        [PanduanController::class, 'piutangSopir']
    )->name('panduan.piutang_sopir');

    Route::get('/panduan/transaksi/{slug}', [PanduanController::class, 'transaksi'])
        ->name('panduan.transaksi');

    Route::get(
        '/laporan/jurnal-umum',
        [LaporanController::class, 'jurnalUmum']
    )->name('laporan.jurnal_umum');

    Route::get(
        '/laporan/jurnal-umum/excel',
        [LaporanController::class, 'exportJurnalUmum']
    )->name('laporan.jurnal_umum.excel');

    Route::get(
        '/laporan/coa',
        [LaporanController::class, 'coa']
    )->name('laporan.coa');

    Route::get(
        '/laporan/coa/excel',
        [LaporanController::class, 'exportCoa']
    )->name('laporan.coa.excel');

    Route::get(
        '/laporan/buku-besar',
        [LaporanController::class, 'bukuBesar']
    )->name('laporan.buku_besar');

    Route::get(
        '/laporan/buku-besar/excel',
        [LaporanController::class, 'exportBukuBesar']
    )->name('laporan.buku_besar.excel');

    Route::get(
        '/laporan/buku-besar/pdf',
        [LaporanController::class, 'exportBukuBesarPdf']
    )->name('laporan.buku_besar.pdf');

    Route::get(
        '/laporan/biaya',
        [LaporanController::class, 'biaya']
    )->name('laporan.biaya');

    Route::get(
        '/laporan/biaya/excel',
        [LaporanController::class, 'exportBiaya']
    )->name('laporan.biaya.excel');

    Route::get(
        '/laporan/neraca',
        [LaporanController::class, 'neraca']
    )->name('laporan.neraca');

    Route::get(
        '/laporan/neraca/excel',
        [LaporanController::class, 'exportNeraca']
    )->name('laporan.neraca.excel');

    Route::get(
        '/laporan/neraca/pdf',
        [LaporanController::class, 'exportNeracaPdf']
    )->name('laporan.neraca.pdf');

    Route::get(
        '/laporan/neraca-saldo',
        [LaporanController::class, 'neracaSaldo']
    )->name('laporan.neraca_saldo');

    Route::get(
        '/laporan/neraca-saldo/excel',
        [LaporanController::class, 'exportNeracaSaldo']
    )->name('laporan.neraca_saldo.excel');

    Route::get(
        '/laporan/arus-kas',
        [LaporanController::class, 'arusKas']
    )->name('laporan.arus_kas');

    Route::get(
        '/laporan/arus-kas/excel',
        [LaporanController::class, 'exportArusKas']
    )->name('laporan.arus_kas.excel');

    Route::get(
        '/laporan/tutup-buku',
        [LaporanController::class, 'tutupBuku']
    )->name('laporan.tutup_buku');

    Route::get(
        '/laporan/tutup-buku/excel',
        [LaporanController::class, 'exportTutupBuku']
    )->name('laporan.tutup_buku.excel');

    Route::get(
        '/laporan/perubahan-ekuitas',
        [LaporanController::class, 'perubahanEkuitas']
    )->name('laporan.perubahan_ekuitas');

    Route::get(
        '/laporan/perubahan-ekuitas/excel',
        [LaporanController::class, 'exportPerubahanEkuitas']
    )->name('laporan.perubahan_ekuitas.excel');

    Route::post(
        '/laporan/tutup-buku/generate-jurnal',
        [LaporanController::class, 'generateClosingJournal']
    )->name('laporan.tutup_buku.generate_jurnal');

    Route::post(
        '/laporan/tutup-buku/mark-closed',
        [LaporanController::class, 'markYearClosed']
    )->name('laporan.tutup_buku.mark_closed');

    Route::post(
        '/laporan/tutup-buku/archive',
        [LaporanController::class, 'archiveJournal']
    )->name('laporan.tutup_buku.archive');

    Route::post(
        '/laporan/tutup-buku/close-year',
        [LaporanController::class, 'closeYear']
    )->name('laporan.tutup_buku.close_year');

    Route::post(
        '/laporan/tutup-buku/remove-closing-journal',
        [LaporanController::class, 'removeClosingJournal']
    )->name('laporan.tutup_buku.remove_closing_journal');

    Route::post(
        '/laporan/tutup-buku/reset-year-close',
        [LaporanController::class, 'resetYearClose']
    )->name('laporan.tutup_buku.reset_year_close');

    Route::get(
        '/laporan/laba-rugi',
        [LaporanController::class, 'labaRugi']
    )->name('laporan.laba_rugi');

    Route::get(
        '/laporan/laba-rugi/excel',
        [LaporanController::class, 'exportLabaRugi']
    )->name('laporan.laba_rugi.excel');

    Route::get('/laporan/{report}/pdf', [LaporanController::class, 'streamReportPdf'])
        ->whereIn('report', ['jurnal-umum', 'coa', 'neraca-saldo', 'arus-kas', 'tutup-buku', 'perubahan-ekuitas', 'laba-rugi'])
        ->name('laporan.stream_pdf');
});

Route::middleware('auth')->group(function () {

    Route::view('/ubah-password', 'profile.password')->name('profile.password');
    Route::patch('/profil/email', [ProfileController::class, 'updateEmail'])->name('profile.email.update');

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| TRANSKSI — Ekspedisi / Transaksi Operasional
|--------------------------------------------------------------------------
| Routes yang diadaptasi dari src_old ke style src (resource controllers)
*/
Route::middleware(['auth', 'verified', 'permission'])->group(function () {

    /*
    | Master Data
    */
    Route::resource('kendaraan', KendaraanController::class)
        ->except('show')
        ->parameters(['kendaraan' => 'kendaraan']);

    Route::resource('price', PriceController::class);
    Route::resource('price-customer', PriceCusController::class)
        ->except(['show'])
        ->parameters(['price-customer' => 'priceCus']);
    Route::get('price-customer/{kodecus}/prices', [PriceCusController::class, 'customerPrices'])->name('price-customer.customer-prices');
    Route::get('price-customer/{kodecus}/price', [PriceCusController::class, 'getPrice'])->name('price-customer.price');
    Route::post('price-customer/{kodecus}/update-all', [PriceCusController::class, 'updateAll'])->name('price-customer.update-all');
    Route::post('price-customer/update-row', [PriceCusController::class, 'saveRow'])->name('price-customer.update-row');
    Route::resource('price-rent', PricedinginController::class)
        ->except(['show'])
        ->parameters(['price-rent' => 'price']);
    Route::resource('price-rent-customer', PricedingincusController::class)
        ->parameters(['price-rent-customer' => 'pricedingincus']);
    Route::get('price-rent-customer/{kodecus}/prices', [PricedingincusController::class, 'customerPrices'])->name('price-rent-customer.customer-prices');
    Route::post('price-rent-customer/{kodecus}/update-all', [PricedingincusController::class, 'updateAll'])->name('price-rent-customer.update-all');
    Route::post('price-rent-customer/update-row', [PricedingincusController::class, 'saveRow'])->name('price-rent-customer.update-row');

    /*
    | Expedisi (EKS)
    | Route names sesuai src_old sidebar:
    |   sidebar_new_expedisi → expedisi.index
    |   sidebar_Invoice_edit_expedisi → expedisi-edit.index (handled via generate-invoice edit)
    */
    Route::get('expedisi/data/items', [ExpedisiController::class, 'dataItems'])
        ->name('expedisi.data.items');
    Route::get('expedisi/data/customers', [ExpedisiController::class, 'dataCustomers'])
        ->name('expedisi.data.customers');
    Route::get('expedisi/data/drivers', [ExpedisiController::class, 'dataDrivers'])
        ->name('expedisi.data.drivers');
    Route::get('expedisi/data/vehicles', [ExpedisiController::class, 'dataVehicles'])
        ->name('expedisi.data.vehicles');
    Route::get('expedisi/preview/no-sj', [ExpedisiController::class, 'previewNoSuratJalan'])
        ->name('expedisi.preview.no-sj');
    Route::get('/expedisi/tambah-muat', [ExpedisiController::class, 'createMuat'])->name('expedisi.create-muat');
    Route::get('/expedisi/data-sj-tersedia', [ExpedisiController::class, 'dataSJTersedia'])->name('expedisi.data-sj-tersedia');
    Route::get('/expedisi/surjal/show', [ExpedisiController::class, 'showSurjal'])->name('expedisi.surjal.show');

    // Tambahan rute muat agar persis dengan src_old
    Route::get('/expedisi/data-muat-list', [ExpedisiController::class, 'getDataMuat'])->name('expedisi.dataMuat');
    Route::get('/expedisi/show-muat', [ExpedisiController::class, 'showMuat'])->name('expedisi.showMuat');
    Route::post('/expedisi/store-muat', [ExpedisiController::class, 'storeMuat'])->name('expedisi.storeMuat');
    Route::post('/expedisi/update-muat/{nomuat}', [ExpedisiController::class, 'updateMuat'])->name('expedisi.updateMuat');
    Route::delete('/expedisi/muat/{nomuat}', [ExpedisiController::class, 'destroyMuat'])->name('expedisi.destroyMuat');
    Route::get('/expedisi/pdf-muat/{nomuat}', [ExpedisiController::class, 'pdfMuat'])->name('expedisi.pdfMuat');
    Route::get('/expedisi/rute-muat', [ExpedisiController::class, 'getRuteMuat'])->name('expedisi.rute-muat');

    Route::get('/expedisi/data-km', [ExpedisiController::class, 'getKmTerakhir'])->name('expedisi.dataKm');
    Route::resource('expedisi', ExpedisiController::class);
    Route::get('expedisi/print/surjal/{expedisi}', [ExpedisiController::class, 'printSuratJalan'])
        ->name('expedisi.print.surjal');
    Route::get('expedisi/print/muat', [ExpedisiController::class, 'pdfMuat'])
        ->name('expedisi.print.muat');

    /*
    | Expedisi Invoice (Gabung SJ → Invoice)
    |   sidebar_invoice_expedisi → expedisi-invoice.index
    |   sidebar_Invoice_edit_expedisi → expedisi-edit.index
    */
    Route::get('expedisi-invoice', [ExpedisiInvoiceController::class, 'index'])
        ->name('expedisi-invoice.index');
    Route::post('expedisi-invoice', [ExpedisiInvoiceController::class, 'storeGabungInvoice'])
        ->name('expedisi-invoice.store');
     Route::get('expedisi-invoice/{noInvoice}/edit', [ExpedisiInvoiceController::class, 'editGabung'])
         ->name('expedisi-invoice.edit');
    Route::put('expedisi-invoice/{noInvoice}', [ExpedisiInvoiceController::class, 'updateGabungInvoice'])
        ->name('expedisi-invoice.update');
     Route::get('expedisi-invoice/data/muat', [ExpedisiInvoiceController::class, 'getDataMuat'])
         ->name('expedisi-invoice.data.muat');
    Route::get('expedisi-invoice/data/gabung', [ExpedisiInvoiceController::class, 'dataGabung'])
        ->name('expedisi-invoice.data.gabung');
     Route::get('expedisi-invoice/data/existing', [ExpedisiInvoiceController::class, 'getExistingGabung'])
         ->name('expedisi-invoice.data.existing');
     Route::get('expedisi-invoice/{noInvoice}/pdf', [ExpedisiGenerateInvoiceController::class, 'pdfGabungInvoice'])
          ->name('expedisi-invoice.pdfGabungInvoice');
     Route::get('expedisi-invoice/{noInvoice}/text', [ExpedisiGenerateInvoiceController::class, 'printInvoiceText'])
         ->name('expedisi-invoice.text');
     Route::get('expedisi-invoice/{noInvoice}', [ExpedisiInvoiceController::class, 'show'])
         ->name('expedisi-invoice.show');

    /*
    |--------------------------------------------------------------------------
    | Expedisi Generate Invoice (Generate Invoice + AR + Excel)
    |   sidebar_Invoice_gen_expedisi → expedisi-generate-invoice.index
     */
     Route::get('expedisi-generate-invoice/data', [ExpedisiGenerateInvoiceController::class, 'getDataInvoiceGen'])->name('expedisi-generate-invoice.data');
     Route::get('expedisi-generate-invoice/customers', [ExpedisiGenerateInvoiceController::class, 'customerLookup'])->name('expedisi-generate-invoice.customers');
     Route::get('expedisi-generate-invoice/export', [ExpedisiGenerateInvoiceController::class, 'export'])->name('expedisi-generate-invoice.export');
     Route::resource('expedisi-generate-invoice', ExpedisiGenerateInvoiceController::class)->only(['index', 'create']);
     Route::post('expedisi-generate-invoice', [ExpedisiGenerateInvoiceController::class, 'prosesInvoiceStore'])->name('expedisi-generate-invoice.store');

    /*
    | Expedisi Kwitansi (Pembayaran)
    |   sidebar_Invoice_kwt_expedisi → expedisi-kwitansi.index
    */
    Route::get('expedisi-kwitansi', [ExpedisiKwitansiController::class, 'index'])
        ->name('expedisi-kwitansi.index');
    Route::post('expedisi-kwitansi/proses', [ExpedisiKwitansiController::class, 'prosesKwitansi'])
        ->name('expedisi-kwitansi.proses');
    Route::post('expedisi-kwitansi/destroy', [ExpedisiKwitansiController::class, 'deleteKwitansi'])
        ->name('expedisi-kwitansi.destroy');
    Route::get('expedisi-kwitansi/{noKwt}/text', [ExpedisiKwitansiController::class, 'printKwitansiText'])
        ->name('expedisi-kwitansi.text');
    Route::get('expedisi-kwitansi/{noKwt}/print', [ExpedisiKwitansiController::class, 'printKwitansi'])
        ->name('expedisi-kwitansi.printKwitansi');

    Route::get('expedisi-invoice-gen', [ExpedisiGenerateInvoiceController::class, 'indexEdit'])
        ->name('expedisi-invoice-gen.index');
    Route::get('expedisi-invoice-gen/{noInvoice}/edit', [ExpedisiGenerateInvoiceController::class, 'showEditInvoice'])
        ->name('expedisi-invoice-gen.edit');
    Route::get('expedisi-invoice-gen/{noInvoice}/show', [ExpedisiGenerateInvoiceController::class, 'showEditInvoiceJson'])
        ->name('expedisi-invoice-gen.show');
    Route::put('expedisi-invoice-gen/{noInvoice}', [ExpedisiGenerateInvoiceController::class, 'updateEditInvoice'])
        ->name('expedisi-invoice-gen.update');

    /*
    | Rent Pendingin (REN)
    |   sidebar_new_rent_dingin → rent-pendingin.index
    |   sidebar_generate_rent_dingin → rent-pendingin-generate-invoice.index
    |   sidebar_generate_kwt_dingin → rent-pendingin-kwitansi.index
    |   sidebar_Invoice_edit_rent → rent-pendingin-edit.index (use generate-invoice edit)
    */
    Route::resource('rent-pendingin', RentPendinginController::class);
    Route::get('rent-pendingin/data/surjal', [RentPendinginController::class, 'dataSurjal'])
        ->name('rent-pendingin.data.surjal');
    Route::get('rent-pendingin/data/muat', [RentPendinginController::class, 'dataMuat'])
        ->name('rent-pendingin.data.muat');
    Route::post('rent-pendingin/muat/{id}/reset', [RentPendinginController::class, 'resetMuat'])
        ->name('rent-pendingin.muat.reset');
    Route::delete('rent-pendingin/surjal/{id}', [RentPendinginController::class, 'destroySurjal'])
        ->name('rent-pendingin.surjal.destroy');
    Route::get('rent-pendingin/show/surjal/{nosj}', [RentPendinginController::class, 'showSurjal'])
        ->name('rent-pendingin.show.surjal');
    Route::get('rent-pendingin/show/muat/{nomuat}', [RentPendinginController::class, 'showMuat'])
        ->name('rent-pendingin.show.muat');
    Route::get('rent-pendingin/print/surjal/{nosj}', [RentPendinginController::class, 'printSurjal'])
        ->name('rent-pendingin.print.surjal');
    Route::get('rent-pendingin/price-items/{kodecus}', [RentPendinginController::class, 'priceItems'])
        ->name('rent-pendingin.price-items');
    Route::post('rent-pendingin/store-sj', [RentPendinginController::class, 'storeSj'])
        ->name('rent-pendingin.store-sj');
    Route::post('rent-pendingin/update-sj/{nosj}', [RentPendinginController::class, 'updateSj'])
        ->name('rent-pendingin.update-sj');
    Route::post('rent-pendingin/proses-muat/{nosj}', [RentPendinginController::class, 'prosesMuat'])
        ->name('rent-pendingin.proses-muat');

    /*
    | Rent Pendingin Generate Invoice
    |   sidebar_generate_rent_dingin → rent-pendingin-generate-invoice.index
    |   sidebar_Invoice_edit_rent → rent-pendingin-invoice-gen.index
    */
    Route::get('rent-pendingin-invoice', [RentPendinginGenerateInvoiceController::class, 'index'])
        ->name('rent-pendingin-invoice.index');
    Route::get('rent-pendingin-invoice/export', [RentPendinginGenerateInvoiceController::class, 'export'])
        ->name('rent-pendingin-invoice.export');
    Route::get('rent-pendingin-invoice/data', [RentPendinginGenerateInvoiceController::class, 'getDataInvoiceGen'])
        ->name('rent-pendingin-invoice.data');
    Route::get('rent-pendingin-invoice/surat-jalan/{nosj}', [RentPendinginGenerateInvoiceController::class, 'showInvoiceDetail'])
        ->name('rent-pendingin-invoice.surat-jalan');
    Route::post('rent-pendingin-invoice', [RentPendinginGenerateInvoiceController::class, 'prosesInvoicePembayaran'])
        ->name('rent-pendingin-invoice.store');
    Route::get('rent-pendingin-invoice/{noInvoice}/pdf', [RentPendinginGenerateInvoiceController::class, 'pdfInvoiceGenerate'])
        ->name('rent-pendingin-invoice.pdf');
    Route::get('rent-pendingin-invoice/{noInvoice}/text', [RentPendinginGenerateInvoiceController::class, 'printInvoiceText'])
        ->name('rent-pendingin-invoice.text');
    Route::get('rent-pendingin-invoice/{noInvoice}', [RentPendinginGenerateInvoiceController::class, 'show'])
        ->name('rent-pendingin-invoice.show');
    Route::get('rent-pendingin-invoice-gen', [RentPendinginGenerateInvoiceController::class, 'indexEdit'])
        ->name('rent-pendingin-invoice-gen.index');
    Route::get('rent-pendingin-invoice-gen/{noInvoice}/show', [RentPendinginGenerateInvoiceController::class, 'showEditInvoiceJson'])
        ->name('rent-pendingin-invoice-gen.show');
    Route::get('rent-pendingin-invoice-gen/prices/{kodecus}', [RentPendinginGenerateInvoiceController::class, 'customerRentPrices'])
        ->name('rent-pendingin-invoice-gen.prices');
    Route::get('rent-pendingin-invoice-gen/{noInvoice}/edit', [RentPendinginGenerateInvoiceController::class, 'showEditInvoiceRen'])
        ->name('rent-pendingin-invoice-gen.edit');
    Route::put('rent-pendingin-invoice-gen/{noInvoice}', [RentPendinginGenerateInvoiceController::class, 'updateEditInvoiceRen'])
        ->name('rent-pendingin-invoice-gen.update');

    /*
    | Rent Pendingin Kwitansi
    |   sidebar_generate_kwt_dingin → rent-pendingin-kwitansi.index
    */
    Route::get('rent-pendingin-kwitansi', [RentPendinginKwitansiController::class, 'index'])
        ->name('rent-pendingin-kwitansi.index');
    Route::post('rent-pendingin-kwitansi', [RentPendinginKwitansiController::class, 'prosesKwitansi'])
        ->name('rent-pendingin-kwitansi.store');
    Route::post('rent-pendingin-kwitansi/destroy', [RentPendinginKwitansiController::class, 'deleteKwitansi'])
        ->name('rent-pendingin-kwitansi.destroy');
    Route::get('rent-pendingin-kwitansi/kwitansi/{noKwt}/text', [RentPendinginKwitansiController::class, 'printKwitansiText'])
        ->name('rent-pendingin-kwitansi.text');
    Route::get('rent-pendingin-kwitansi/kwitansi/{noKwt}/pdf', [RentPendinginKwitansiController::class, 'printKwitansi'])
        ->name('rent-pendingin-kwitansi.pdf');

    /*
    | Coolroom
    |   sidebar_coolroom → coolroom.index
    |   sidebar_coolroom_inv → coolroom-invoice.index
    |   sidebar_coolroom_kwt → coolroom-kwitansi.index
    |   sidebar_coolroom_edit → coolroom-invoice-gen.index
    */
    Route::resource('coolroom', CoolroomController::class);
    Route::get('coolroom/{nosj}/pdf', [CoolroomController::class, 'pdf'])
        ->name('coolroom.pdf');
    Route::post('coolroom/customer/search', [CoolroomController::class, 'getDataCustomer'])
        ->name('coolroom.customer.search');

    Route::resource('coolroom-invoice', CoolroomGenerateInvoiceController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::get('coolroom-invoice/surat-jalan/{nosj}', [CoolroomGenerateInvoiceController::class, 'showInvoiceDetail'])
        ->name('coolroom-invoice.detail');
    Route::get('coolroom-invoice/{noInvoice}/pdf', [CoolroomGenerateInvoiceController::class, 'printInvoiceCoolroom'])
        ->name('coolroom-invoice.pdf');
    Route::get('coolroom-invoice/{noInvoice}/print-text', [CoolroomGenerateInvoiceController::class, 'printInvoiceText'])
        ->name('coolroom-invoice.text');
    Route::post('coolroom-invoice/export', [CoolroomGenerateInvoiceController::class, 'export'])
        ->name('coolroom-invoice.export');
    Route::get('coolroom-invoice-gen', [CoolroomGenerateInvoiceController::class, 'indexEdit'])
        ->name('coolroom-invoice-gen.index');
    Route::get('coolroom-invoice-gen/{noInvoice}/edit', [CoolroomGenerateInvoiceController::class, 'showEditInvoiceCoolroom'])
        ->name('coolroom-invoice-gen.edit');
    Route::put('coolroom-invoice-gen/{noInvoice}', [CoolroomGenerateInvoiceController::class, 'updateEditInvoiceCoolroom'])
        ->name('coolroom-invoice-gen.update');

    Route::resource('coolroom-kwitansi', CoolroomKwitansiController::class);

    /*
    | Kwitansi History
    |   sidebar_new_kwitansi → kwitansi-history.index
    */
    Route::resource('kwitansi-history', KwitansiHistoryController::class);
    Route::get('kwitansi-history/export', [KwitansiHistoryController::class, 'exportKwitansi'])
        ->name('kwitansi-history.export');

    /*
    | Service
    |   sidebar_new_service → service.index
    */
    Route::resource('service', ServiceController::class);
    Route::post('service/supplier', [ServiceController::class, 'dataSupplier'])
        ->name('service.data.supplier');
    Route::post('service/kendaraan', [ServiceController::class, 'dataKendaraan'])
        ->name('service.data.kendaraan');

    /*
    | Extra (Pajak, Rekening, Signature, Area)
    |   sidebar_extra_pajak, sidebar_extra_rekening, sidebar_extra_signature, sidebar_extra_area
    */
    Route::get('pajak', [PajakController::class, 'get'])
        ->name('pajak.get');
    Route::put('pajak', [PajakController::class, 'update'])
        ->name('pajak.update');

    Route::resource('rekening', RekeningController::class);
    Route::get('rekening/pilih/{id}', [RekeningController::class, 'pilih'])
        ->name('rekening.pilih');

    Route::get('signature', [SignatureController::class, 'get'])
        ->name('signature.get');
    Route::put('signature', [SignatureController::class, 'update'])
        ->name('signature.update');

    Route::get('area', [AreaController::class, 'get'])
        ->name('area.get');
    Route::post('area', [AreaController::class, 'store'])
        ->name('area.store');
    Route::put('area/{area}', [AreaController::class, 'update'])
        ->name('area.update');
    Route::delete('area/{area}', [AreaController::class, 'destroy'])
        ->name('area.destroy');
});
