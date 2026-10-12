<?php

use App\Http\Controllers\Api\SinkronisasiController;
use Illuminate\Support\Facades\Route;

Route::post('/penjualan/sync', [SinkronisasiController::class, 'syncData'])
    ->name('api.penjualan.sync');

Route::post('/penjualan/{invoice}/void', [SinkronisasiController::class, 'void'])
    ->name('api.penjualan.void');

Route::post('/penjualan/{invoice}/revoid', [SinkronisasiController::class, 'revoid'])
    ->name('api.penjualan.revoid');

Route::get('/rekapan', [SinkronisasiController::class, 'rekapan'])
    ->name('api.rekapan');

Route::post('/rekapan', [SinkronisasiController::class, 'rekapan'])
    ->name('api.rekapan.post');
