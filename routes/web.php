<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PrinterController;
use App\Filament\Clusters\Shop\Resources\SaleResource;
use App\Http\Controllers\PaymentNotificationController;

// Route::get('/', function () {
//     return redirect()->route('filament-panels::pages.dashboard');
// });


Route::get('/pembelian/{inv}', [PrinterController::class, 'printPurchase'])->name('print.purchase');
Route::get('/penjualan/{inv}', [PrinterController::class, 'printSale'])->name('print.sale');