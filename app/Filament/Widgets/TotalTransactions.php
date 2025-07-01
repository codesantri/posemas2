<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Change;
use App\Models\Purchase;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use App\Traits\Filament\Services\GeneralService;

class TotalTransactions extends Widget
{
    protected static string $view = 'filament.widgets.total-transactions';
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $sales = Sale::with('transaction')->with('saleDetails')->where('status', 'success')->get();
        $changeAdd = Change::where('change_type', 'add')->where('status', 'success')->with('transaction')->with('changeItems')->get();
        $changeAdd = Change::where('change_type', 'add')->where('status', 'success')->with('transaction')->with('changeItems')->get();
        $changeDeduct = Change::where('change_type', 'deduct')->where('status', 'success')->with('transaction')->with('changeItems')->get();
        $changeModel = Change::where('change_type', 'change_model')->where('status', 'success')->with('transaction')->with('changeItems')->get();
        $purchase = Purchase::with('transaction')->where('status', 'success')->get();


        // Totalkan semua transaction->total
        // Total In
        $totalSale = $sales->sum(function ($sale) {
            return $sale->transaction?->total ?? 0;
        });

        $totalChangeAdd = $changeAdd->sum(function ($changeAdd) {
            return $changeAdd->transaction?->total ?? 0;
        });

        $totalIncome = $totalSale + $totalChangeAdd;

        // Total Out
        $totalPurchase =  $purchase->sum(function ($purchase) {
            return  $purchase->transaction?->total ?? 0;
        });

        $totalChangeDeduct = $changeDeduct->sum(function ($changeDeduct) {
            return $changeDeduct->transaction?->total ?? 0;
        });


        $totalOutcome = $totalPurchase + $totalChangeDeduct;

        // Mayam dan Gram

        $getMayam = $sales->flatMap(function ($sale) {
            return $sale->saleDetails; // ini sudah collection of SaleDetail
        })->sum(function ($saleDetail) {
            return optional($saleDetail->product)->weight ?? 0;
        });

        $getMayamIn = GeneralService::getMayam($getMayam);
        $getMayamOut = 0;

        $getGramIn = $getMayam;
        $getGramOut = 0;

        $getMayamTotal = $getMayamIn + $getMayamOut ?? 0;
        $getGramTotal = $getGramIn + $getGramOut ?? 0;


        $totalTransaction = $totalIncome + $totalOutcome;


        $data = [
            [
                'title' => 'Total Income',
                'total' => $totalIncome,
                'mayam' => $getMayamIn,
                'gram' => $getGramIn,
                'icon' => 'icon/trend-up.png',
                'desc' => 'Total dari Penjualan, Tukar tambah, Tukar model, Titip emas, & Jasa Pembuatan.',
            ],
            [
                'title' => 'Total Out',
                'total' => $totalOutcome,
                'mayam' => 0,
                'gram' => 0,
                'icon' => 'icon/trend-down.png',
                'desc' => 'Total dari Pembelian, Tukar kurang, Tukar model',
            ],
            [
                'title' => 'Total Transaksi',
                'total' => $totalTransaction,
                'mayam' => $getMayamTotal,
                'gram' => $getGramTotal,
                'icon' => 'icon/total.png',
                'desc' => 'Total semua transaki.',
            ],
        ];



        return [
            'data' => $data,
        ];
    }
}
