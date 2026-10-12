<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PricesController;
use App\Http\Controllers\RuteController;
use App\Http\Controllers\PricesCustomerController;
use App\Http\Controllers\PricedinginController;
use App\Http\Controllers\PricedinginCustomerController;
use App\Http\Controllers\ExpedisiController;
use App\Http\Controllers\ExpedisiInvoiceController;
use App\Http\Controllers\ExpedisiGenerateInvoiceController;
use App\Http\Controllers\ExpedisiKwitansiController;
use App\Http\Controllers\RentPendinginController;
use App\Http\Controllers\RentPendinginInvoiceController;
use App\Http\Controllers\RentPendinginGenerateInvoiceController;
use App\Http\Controllers\RentPendinginKwitansiController;
use App\Http\Controllers\CoolroomController;
use App\Http\Controllers\MsupplierController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\CoolroomGenerateInvoiceController;
use App\Http\Controllers\CoolroomKwitansiController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\KwitansiHistoryController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('login')->group(function () {
    Route::get('/', [LoginController::class, 'login'])->name('login');
    Route::get('/home', [LoginController::class, 'index'])->name('index')->middleware('auth');
    Route::post('actionlogin', [LoginController::class, 'actionlogin'])->name('actionlogin')->middleware('web');
    Route::get('actionlogout', [LoginController::class, 'actionlogout'])->name('actionlogout')->middleware('auth');
});

Route::prefix('register')->middleware('auth')->group(function () {
    Route::get('/', [RegisterController::class, 'register'])->name('register');
    Route::post('actionregister', [RegisterController::class, 'actionregister'])->name('actionregister');
    Route::get('editregister', [RegisterController::class, 'editregister'])->name('editregister');
    Route::post('updateregister', [RegisterController::class, 'updateregister'])->name('updateregister');
    Route::get('listregister', [RegisterController::class, 'listregister'])->name('listregister');
    Route::get('filter_register', [RegisterController::class, 'filter_register'])->name('filter_register');
    Route::get('edit_list_register/{id}', [RegisterController::class, 'edit_list_register'])->name('edit_list_register');
    Route::get('select_list_register_staff/{id}', [RegisterController::class, 'select_list_register_staff'])->name('select_list_register_staff');
    Route::post('update_list_register', [RegisterController::class, 'update_list_register'])->name('update_list_register');
    Route::delete('delete_list_register/{id}', [RegisterController::class, 'delete_list_register'])->name('delete_list_register');
    Route::get('/generate-user-id', [RegisterController::class, 'generate_user_id'])->name('generate_user_id');
});

Route::prefix('dashboard')->group(function () {
    Route::get('/index', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');
});

Route::prefix('customer')->group(function () {
    Route::get('/', [CustomerController::class, 'index_customer'])->name('index_customer');

    // DataTables
    Route::get('/customer_get_data', [CustomerController::class, 'customer_get_data'])->name('customer_get_data');
    Route::get('/customer-search', [CustomerController::class, 'search_select'])->name('customer_select');

    // CRUD
    Route::post('/store', [CustomerController::class, 'customer_store'])->name('customer_store');
    Route::get('/show/{id}', [CustomerController::class, 'customer_show'])->name('customer_show');
    Route::post('/update/{id}', [CustomerController::class, 'customer_update'])->name('customer_update');
    Route::post('/destroy/{id}', [CustomerController::class, 'customer_destroy'])->name('customer_destroy');

    // Callback
    Route::get('/customer_kode', [CustomerController::class, 'customer_kode'])->name('customer_kode');
});

Route::prefix('kendaraan')->group(function () {
    Route::get('/', [KendaraanController::class, 'index'])->name('kendaraan.index');
    Route::post('/store', [KendaraanController::class, 'store'])->name('kendaraan.store');
    Route::get('/data', [KendaraanController::class, 'data'])->name('kendaraan.data');
    Route::get('/edit/{id}', [KendaraanController::class, 'edit'])->name('kendaraan.edit');
    Route::post('/update/{id}', [KendaraanController::class, 'update'])->name('kendaraan.update');
    Route::post('/delete/{id}', [KendaraanController::class, 'destroy'])->name('kendaraan.destroy');
    // Callback
    Route::get('/kendaraan_kode', [KendaraanController::class, 'kendaraan_kode'])->name('kendaraan_kode');
    Route::get('/data-model', [KendaraanController::class, 'dataModel'])->name('kendaraan.datamodel');
});

Route::prefix('driver')->group(function () {
    Route::get('/', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/data', [DriverController::class, 'data'])->name('driver.data');
    Route::get('/edit/{id}', [DriverController::class, 'edit'])->name('driver.edit');
    Route::post('/store', [DriverController::class, 'store'])->name('driver.store');
    Route::post('/update/{id}', [DriverController::class, 'update'])->name('driver.update');
    Route::post('/destroy/{id}', [DriverController::class, 'destroy'])->name('driver.destroy');
    // Callback
    Route::get('/driver_kode', [DriverController::class, 'driver_kode'])->name('driver_kode');
    Route::get('/data/model', [DriverController::class, 'dataModal'])->name('driver-modal.data');
    Route::get('/data/driver/{user_id}', [DriverController::class, 'dataDriver'])->name('driver-det.data');
});

Route::prefix('price-expedition')->group(function() {
    Route::get('/', [PricesController::class, 'index'])->name('price-expedition.index');
    Route::get('/data', [PricesController::class, 'getData'])->name('price-expedition.data');
    Route::post('/store', [PricesController::class, 'store'])->name('price-expedition.store');
    Route::get('/show/{id}', [PricesController::class, 'show'])->name('price-expedition.show');
    Route::post('/update/{id}', [PricesController::class, 'update'])->name('price-expedition.update');
    Route::post('/destroy/{id}', [PricesController::class, 'destroy'])->name('price-expedition.destroy');
});

Route::prefix('rute')->group(function() {
    Route::get('/data', [RuteController::class, 'getData'])->name('rute.data');
    Route::post('/store', [RuteController::class, 'store'])->name('rute.store');
    Route::get('/show/{id}', [RuteController::class, 'show'])->name('rute.show');
    Route::post('/update/{id}', [RuteController::class, 'update'])->name('rute.update');
    Route::post('/destroy/{id}', [RuteController::class, 'destroy'])->name('rute.destroy');
});

Route::prefix('price-customer')->group(function() {
    Route::get('/', [PricesCustomerController::class, 'index'])->name('price-customer.index');
    Route::get('/data', [PricesCustomerController::class, 'getData'])->name('price-customer.data');
    Route::get('/price/{kodecus}', [PricesCustomerController::class, 'getPrice'])->name('price-customer.price');
    Route::post('/update-all', [PricesCustomerController::class, 'updateHargaRuteCustomer'])->name('price-customer.update-all');
    Route::post('/update-row', [PricesCustomerController::class, 'saveCustomerRow'])->name('price-customer.update-row');
    Route::post('/store', [PricesCustomerController::class, 'store'])->name('price-customer.store');
    Route::get('/price/modal/{kodecus}', [PricesCustomerController::class, 'getPriceModal'])->name('price-customer-modal.price');
});

Route::prefix('price-rent')->group(function() {
    Route::get('/', [PricedinginController::class, 'index'])->name('price-rent.index');
    Route::get('/data', [PricedinginController::class, 'getData'])->name('price-rent.data');
    Route::post('/store', [PricedinginController::class, 'store'])->name('price-rent.store');
    Route::get('/show/{id}', [PricedinginController::class, 'show'])->name('price-rent.show');
    Route::post('/update/{id}', [PricedinginController::class, 'update'])->name('price-rent.update');
    Route::post('/destroy/{id}', [PricedinginController::class, 'destroy'])->name('price-rent.destroy');
});

Route::prefix('price-dingin-customer')->group(function() {
    Route::get('/', [PricedinginCustomerController::class, 'index'])->name('price-rentcus.index');
    Route::get('/data', [PricedinginCustomerController::class, 'getData'])->name('price-rentcus.data');
    Route::get('/price/{kodecus}', [PricedinginCustomerController::class, 'getPrice'])->name('price-rentcus.price');
    Route::post('/update-all', [PricedinginCustomerController::class, 'updateHargaDinginCustomer'])->name('price-rentcus.update-all');
    Route::post('/update-row', [PricedinginCustomerController::class, 'saveCustomerRow'])->name('price-rentcus.update-row');
    Route::post('/store', [PricedinginCustomerController::class, 'store'])->name('price-rentcus.store');
    Route::get('/price/modal/{kodecus}', [PricedinginCustomerController::class, 'getPriceModal'])->name('price-rentcus-modal.price');
});

Route::prefix('expedisi')->group(function() {
    Route::get('/', [ExpedisiController::class, 'index'])->name('expedisi.index');
    Route::get('/data/cus', [ExpedisiController::class, 'getDataCustomer'])->name('expedisi-cus.data');
    Route::post('/store', [ExpedisiController::class, 'storeSurjal'])->name('expedisi.store');
    Route::get('/data/surjal', [ExpedisiController::class, 'getDataSurjal'])->name('expedisi-surjal.data');
    Route::get('/data', [ExpedisiController::class, 'getDataMuat'])->name('expedisi.data');
    Route::get('/show/surjal', [ExpedisiController::class, 'showSurjal'])->name('expedisi-surjal.show');
    Route::get('/show', [ExpedisiController::class, 'showMuat'])->name('expedisi.show');
    Route::post('/update/{nosj}', [ExpedisiController::class, 'updateSurjal'])->name('expedisi.update');
    Route::post('/update/muat/{nomuat}', [ExpedisiController::class, 'updateMuat'])->name('expedisi-muat.update');
    Route::post('/destroy/{id}', [ExpedisiController::class, 'destroySurjal'])->name('expedisi.destroy');
    Route::post('/destroy/muat/{nomuat}', [ExpedisiController::class, 'destroyMuat'])->name('expedisi-muat.destroy');
    Route::post('/store/muat', [ExpedisiController::class, 'storeMuat'])->name('expedisi-muat.store');
    Route::get('/data/rute', [ExpedisiController::class, 'getRuteMuat'])->name('rute-muat.data');
    Route::get('/expedisi/get-km-terakhir', [ExpedisiController::class, 'getKmTerakhir'])->name('expedisi.dataKm');
    // PDF
    Route::get('/expedisi/{id}/print-surat-jalan', [ExpedisiController::class, 'printSuratJalan'])->name('expedisi.printSuratJalan');
    Route::get('/expedisi/{nomuat}/print-nomuat', [ExpedisiController::class, 'pdfMuat'])->name('expedisi.pdfMuat');
});

Route::prefix('expedisi-invoice')->group(function() {
    Route::get('/', [ExpedisiInvoiceController::class, 'index'])->name('expedisiInvoice.index');
    Route::post('/store', [ExpedisiInvoiceController::class, 'storeGabungInvoice'])->name('expedisiInvoice.store');
    Route::post('/update', [ExpedisiInvoiceController::class, 'updateGabungInvoice'])->name('expedisiInvoice.update');
    Route::get('/data', [ExpedisiInvoiceController::class, 'getDataMuat'])->name('expedisiInvoice.data');
    Route::get('/data/gabung', [ExpedisiInvoiceController::class, 'dataGabung'])->name('expedisiInvoiceGabung.data');
    Route::get('data/invoice/existing', [ExpedisiInvoiceController::class,'getExistingGabung'])
    ->name('expedisiInvoiceGabungExisting.data');
});

Route::prefix('expedisi-generate-invoice')->group(function() {
    Route::get('/', [ExpedisiGenerateInvoiceController::class, 'index'])->name('expedisiInvoiceGenerate.index');
    Route::post('/store', [ExpedisiGenerateInvoiceController::class, 'prosesInvoiceStore'])->name('expedisiInvoiceGenerate.store');
    Route::post('/update', [ExpedisiGenerateInvoiceController::class, 'updateInvoice'])->name('expedisiInvoiceGenerate.update');
    Route::get('/data', [ExpedisiGenerateInvoiceController::class, 'getDataInvoiceGen'])->name('expedisiInvoiceGenerate.data');
    Route::get('/show/{surjalNo}', [ExpedisiGenerateInvoiceController::class, 'showInvoiceGabung'])->name('expedisiInvoiceGenerate.show');
    // generate excel
    Route::post('/laporan/excel/export', [ExpedisiGenerateInvoiceController::class, 'export'])->name('laporan.expedisiInvoiceGenerate.export');
    // ### Edit Invoice
    Route::get('/edit/table', [ExpedisiGenerateInvoiceController::class, 'indexEdit'])->name('expedisiInvoiceEdit.index');
    Route::get('/data/edit', [ExpedisiGenerateInvoiceController::class, 'tableEdit'])->name('expedisiInvoiceEdit.data');
    Route::get('/edit/show/{invoice}',[ExpedisiGenerateInvoiceController::class, 'showEditInvoice'])->name('expedisiInvoiceEdit.show');
    Route::post('/edit/update/{invoice}',[ExpedisiGenerateInvoiceController::class, 'updateEditInvoice'])->name('expedisiInvoiceEdit.update');
     // PDF
    Route::get('/invoice/pdf/{invoiceNo}', [ExpedisiGenerateInvoiceController::class, 'pdfGabungInvoice'])->name('expedisiInvoice.pdfInvoice');
    Route::get('/print-invoice-text/{invoiceNo}', [ExpedisiGenerateInvoiceController::class, 'printInvoiceText'])->name('expedisiInvoice.text');
});
// Test Print
Route::get('/print-test', [ExpedisiGenerateInvoiceController::class, 'printTest'])->name('print.test');

Route::prefix('expedisi-kwitansi')->group(function() {
    Route::get('/', [ExpedisiKwitansiController::class, 'index'])->name('expedisiKwitansi.index');
    Route::get('/data', [ExpedisiKwitansiController::class, 'getDataKwitansi'])->name('expedisiKwitansi.data');
    Route::post('/proses', [ExpedisiKwitansiController::class, 'prosesKwitansi'])->name('expedisiKwitansi.proses');
    Route::post('/destroy', [ExpedisiKwitansiController::class, 'deleteKwitansi'])->name('expedisiKwitansi.destroy');
    // // PDF
    Route::get('/invoice/pdf/{kwitansiNo}', [ExpedisiKwitansiController::class, 'pdfInvoiceKwitansi'])->name('expedisiKwitansi.pdfKwitansi');
});

Route::prefix('rent-pendingin')->group(function() {
    Route::get('/', [RentPendinginController::class, 'index'])->name('rentPendingin.index');
    Route::get('/data/cus', [RentPendinginController::class, 'getDataCustomer'])->name('rentPendingin-cus.data');
    Route::post('/store', [RentPendinginController::class, 'storeRentPendinginSurjal'])->name('rentPendingin-surjal.store');
    Route::get('/data', [RentPendinginController::class, 'getDataMuat'])->name('rentPendingin.data');
    Route::get('/data/surjal', [RentPendinginController::class, 'getDataSurjal'])->name('rentPendingin-surjal.data');
    Route::get('/show/{nosj}', [RentPendinginController::class, 'showSurjal'])->name('rentPendinginSurjal.show');
    Route::post('/update/{nosj}', [RentPendinginController::class, 'updateRentPendinginSurjal'])->name('rentPendinginSurjal.update');
    Route::post('/destroy/surjal/{id}', [RentPendinginController::class, 'destroySurjal'])->name('rentPendinginSurjal.destroy');
    Route::post('/destroy/{id}', [RentPendinginController::class, 'destroy'])->name('rentPendingin.destroy');
    Route::post('/update/muat/{nosj}', [RentPendinginController::class, 'updateRentPendinginMuat'])->name('rentPendinginMuat.update');
    Route::get('/show/muat/{nomuat}', [RentPendinginController::class, 'showMuat'])->name('rentPendinginMuat.show');
    Route::post('/destroy/muat/{id}', [RentPendinginController::class, 'destroyMuat'])->name('rentPendinginMuat.destroy');
    // // PDF
    Route::get('/rent/pendingin/{nosj}/print-surat-jalan', [RentPendinginController::class, 'printSurjalRent'])->name('rentPendingin.printSuratJalan');
});

// ### Expired
Route::prefix('rent-pendingin-invoice')->group(function() {
    Route::get('/', [RentPendinginInvoiceController::class, 'index'])->name('rentPendinginInv.index');
    Route::post('/store', [RentPendinginInvoiceController::class, 'storeRentDinginInvoice'])->name('rentPendinginInv.store');
    Route::get('/data', [RentPendinginInvoiceController::class, 'getDataMuat'])->name('rentPendinginInv.data');
    Route::get('/data/muat', [RentPendinginInvoiceController::class, 'getDetailByNomuat'])->name('rentPendinginInv.detail');
    Route::post('/update', [RentPendinginInvoiceController::class, 'updateRentDinginInvoice'])->name('rentPendinginInv.update');
});

Route::prefix('rent-pendingin-invoice-gen')->group(function() {
    Route::get('/', [RentPendinginGenerateInvoiceController::class, 'index'])->name('rentPendinginInvGen.index');
    Route::post('/store', [RentPendinginGenerateInvoiceController::class, 'prosesInvoicePembayaran'])->name('rentPendinginInvGen.store');
    Route::get('/data', [RentPendinginGenerateInvoiceController::class, 'getDataInvoiceGen'])->name('rentPendinginInvGen.data');
    Route::get('/show/{nosj}', [RentPendinginGenerateInvoiceController::class, 'showInvoiceDetail'])->name('rentPendinginInvGen.show');
    Route::post('/destroy', [RentPendinginGenerateInvoiceController::class, 'prosesKwitansiDelete'])->name('rentPendinginKwitansi.destroy');
    // // PDF
    Route::get('/invoice/pdf/{invoiceNo}', [RentPendinginGenerateInvoiceController::class, 'pdfInvoiceGenerate'])->name('rentPendinginGenerate.pdfGenerate');
    // generate excel
    Route::post('/laporan/excel/export', [RentPendinginGenerateInvoiceController::class, 'export'])->name('laporan.rentPendinginGenerate.export');
    // #### EDIT RENT PEDINGIN
    Route::get('/edit', [RentPendinginGenerateInvoiceController::class, 'indexEdit'])->name('rentPendinginInvGen.indexEdit');
    Route::get('/table/edit', [RentPendinginGenerateInvoiceController::class, 'tableEditRen'])->name('rentPendinginInvGen.tableEdit');
    Route::get('/show/edit/{invoice}',[RentPendinginGenerateInvoiceController::class,'showEditInvoiceRen'])->name('rentPendinginInvGen.showEdit');
    Route::get('/show/edit/{invoice}',[RentPendinginGenerateInvoiceController::class,'showEditInvoiceRen'])->name('rentPendinginInvGen.showEdit');
    Route::post('/update/edit',[RentPendinginGenerateInvoiceController::class,'updateEditInvoiceRen'])->name('rentPendinginInvGen.updateEdit');
});

Route::prefix('rent-pendingin-kwitansi')->group(function() {
    Route::get('/', [RentPendinginKwitansiController::class, 'index'])->name('pendinginKwitansi.index');
    Route::get('/data', [RentPendinginKwitansiController::class, 'getDataKwitansi'])->name('pendinginKwitansi.data');
    Route::post('/proses', [RentPendinginKwitansiController::class, 'prosesKwitansi'])->name('pendinginKwitansi.proses');
    Route::post('/destroy', [RentPendinginKwitansiController::class, 'deleteKwitansi'])->name('pendinginKwitansi.destroy');
    // // PDF
    Route::get('/invoice/pdf/{kwitansiNo}', [RentPendinginKwitansiController::class, 'pdfInvoiceKwitansi'])->name('pendinginKwitansi.pdfKwitansi');
});

Route::prefix('coolroom')->group(function() {
    Route::get('/', [CoolroomController::class, 'index'])->name('coolroom.index');
    Route::get('/get-data', [CoolroomController::class, 'getData'])->name('coolroom.getData');
    Route::get('/data/cus', [CoolroomController::class, 'getDataCustomer'])->name('coolroom-cus.data');
    Route::post('/store', [CoolroomController::class, 'store'])->name('coolroom.store');
    Route::get('/pdf/{nosj}', [CoolroomController::class,'pdf'])->name('coolroom.pdf');
    Route::get('/edit/{id}', [CoolroomController::class,'edit'])->name('coolroom.edit');
    Route::post('/update/{id}', [CoolroomController::class,'update'])->name('coolroom.update');
    Route::delete('/coolroom/delete/{id}', [CoolroomController::class,'destroy'])->name('coolroom.destroy');
});

Route::prefix('coolroom-invoice')->group(function() {
    Route::get('/', [CoolroomGenerateInvoiceController::class, 'index'])->name('coolroomInv.index');
    Route::get('/get-data', [CoolroomGenerateInvoiceController::class,'getDataInvoice'])->name('coolroomInv.getData');
    Route::get('/show-data/{nosj}', [CoolroomGenerateInvoiceController::class,'showInvoiceCoolroom'])->name('coolroomInv.show');
    Route::post('/proses', [CoolroomGenerateInvoiceController::class, 'prosesInvoice'])->name('coolroomInv.proses');
    Route::get('/pdf/{invoice}', [CoolroomGenerateInvoiceController::class,'pdfGenerate'])->name('coolroomInv.pdf');
    Route::post('/laporan/excel/export', [CoolroomGenerateInvoiceController::class, 'export'])->name('coolroomInv.export');
    // ####### EDIT COOLROOM
    Route::get('/edit', [CoolroomGenerateInvoiceController::class, 'indexEdit'])->name('coolroomInv.indexEdit');
    Route::get('/table/edit',[CoolroomGenerateInvoiceController::class,'tableEditCoolroom'])->name('coolroomInv.tableEdit');
    Route::get('/show/edit/{invoice}',[CoolroomGenerateInvoiceController::class,'showEditInvoiceCoolroom'])->name('coolroomInv.showEdit');
    Route::post('/update/edit',[CoolroomGenerateInvoiceController::class,'updateEditInvoiceCoolroom'])->name('coolroomInv.updateEdit');
    Route::get('/print-invoice-text/{invoiceNo}', [CoolroomGenerateInvoiceController::class, 'printInvoiceCoolroom'])->name('coolroomInv.text');
});

Route::prefix('coolroom-kwitansi')->group(function() {
    Route::get('/', [CoolroomKwitansiController::class, 'index'])->name('coolroomKwt.index');
    Route::get('/get-data', [CoolroomKwitansiController::class,'getDataKwitansi'])->name('coolroomKwt.getData');
    Route::post('/proses', [CoolroomKwitansiController::class, 'prosesKwitansi'])->name('coolroomKwt.proses');
    Route::post('/delete',[CoolroomKwitansiController::class, 'deleteKwitansi']
    )->name('coolroomKwt.delete');
    Route::get('/pdf/{kwitansi}',[CoolroomKwitansiController::class, 'pdfInvoiceKwitansi'])->name('coolroomKwt.pdf');
});

Route::prefix('kwitansi')->group(function () {
    Route::get('/', [KwitansiHistoryController::class, 'index'])->name('index.kwitansi');
    Route::get('/data', [KwitansiHistoryController::class, 'getDataKwitansi'])->name('kwitansi.data');
    // Di routes/web.php
    Route::post('/export', [KwitansiHistoryController::class, 'exportKwitansi'])->name('kwitansi.export');
});

Route::prefix('supplier')->middleware('auth')->group(function () {
    Route::get('/', [MsupplierController::class, 'index_supplier'])->name('msupplier.index');
    Route::get('/data', [MsupplierController::class, 'data'])->name('msupplier.data');
    Route::post('/store', [MsupplierController::class, 'store'])->name('msupplier.store');
    Route::get('/show/{id}', [MsupplierController::class, 'show'])->name('msupplier.show');
    Route::delete('/delete/{id}', [MsupplierController::class, 'destroy'])->name('msupplier.destroy');
});

Route::prefix('service')->middleware('auth')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/data', [ServiceController::class, 'data'])->name('service.data');
    Route::get('/perkiraan', [ServiceController::class, 'ajaxPerkiraan'])->name('service.perkiraan');
    Route::post('/store', [ServiceController::class, 'store'])->name('service.store');
    Route::get('/show/{id}', [ServiceController::class, 'show'])->name('service.show');
    Route::get('/data/supplier', [ServiceController::class, 'dataSupplierModal'])->name('service-supplier.data');
    Route::get('/data/kendaraan', [ServiceController::class, 'dataKendaraanModel'])->name('service-kendaraan.data');
    Route::delete('/service/{id}', [ServiceController::class,'destroy'])->name('service.delete');
});

Route::prefix('pajak')->middleware('auth')->group(function () {
    Route::get('/get-pajak', [PajakController::class, 'get_pajak'])->name('get_pajak');
    Route::post('/edit/show', [PajakController::class, 'update_pajak'])->name('update_pajak');
});

Route::prefix('rekening')->middleware('auth')->group(function () {
    Route::get('/', [RekeningController::class, 'index'])->name('rekening.index');
    Route::get('/data', [RekeningController::class, 'data'])->name('rekening.data');
    Route::post('/store', [RekeningController::class, 'store'])->name('rekening.store');
    Route::post('/pilih/{id}', [RekeningController::class, 'pilih'])->name('rekening.pilih');
});

Route::prefix('signature')->middleware('auth')->group(function () {
    Route::get('/get-sign', [SignatureController::class, 'get_signature'])->name('get_signature');
    Route::post('/edit/show', [SignatureController::class, 'update_signature'])->name('update_signature');
});

Route::prefix('printer')->middleware('auth')->group(function () {
    Route::get('/list', [PrinterController::class, 'list'])->name('printer.list');
    Route::post('/save', [PrinterController::class, 'save'])->name('printer.save');
    Route::get('/current', [PrinterController::class, 'current'])->name('printer.current');
});

Route::prefix('permissions')->middleware('auth')->group(function () {
    Route::get('/index', [UserPermissionController::class, 'index'])->name('index.permissions');
    Route::get('/user-permissions/{id}', [UserPermissionController::class, 'getPermissions'])->name('user.permissions');
    Route::post('/save-permissions', [UserPermissionController::class, 'update'])->name('update.permissions');
});

Route::prefix('area')->middleware('auth')->group(function () {
    Route::get('/get-area', [AreaController::class, 'getArea'])->name('get_area');
    Route::post('/store-area', [AreaController::class, 'store'])->name('store_area');
});



// Route::prefix('register')->group(function () {
//     Route::get('/users', UsersPage::class)->name('users.page');
// });

