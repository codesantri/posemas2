<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrinterController;


Route::get('/cetak/{inv}', [PrinterController::class, 'print'])->name('print')->middleware('auth');
