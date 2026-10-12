<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LedgerController;

Route::get('/ledger/expedisi', [LedgerController::class, 'expedisi']);
Route::get('/ledger/coolrooms', [LedgerController::class, 'coolrooms']);
Route::get('/ledger/arh', [LedgerController::class, 'arh']);
