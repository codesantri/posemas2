<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrinterController;

// Route::get('/', function () {
//     return redirect()->route('filament-panels::pages.dashboard');
// });


Route::prefix('cetak-nota')->group(function () {
    // routes/web.php

    Route::get('/pembelian/{inv}', [PrinterController::class, 'print'])->name('print.purchase');




    Route::get('/penjualan/{inv}', [PrinterController::class, 'printSale'])->name('print.sale');
    Route::get('/pertukaran/{inv}', [PrinterController::class, 'exchange'])->name('print.change');
    Route::get('/titip/emas/{inv}', [PrinterController::class, 'printSale'])->name('print.entrust');
});
