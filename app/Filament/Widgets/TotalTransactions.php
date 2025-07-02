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
        $sales = Sale::with(['transaction', 'saleDetails'])
            ->where('status', 'success')
            ->get();

        $changeAdds = Change::with(['transaction', 'changeItems'])
            ->where('change_type', 'add')
            ->where('status', 'success')
            ->get();

        $changeDeducts = Change::with(['transaction', 'changeItems'])
            ->where('change_type', 'deduct')
            ->where('status', 'success')
            ->get();

        $changeModels = Change::with(['transaction', 'changeItems'])
            ->where('change_type', 'change_model')
            ->where('status', 'success')
            ->get();

        $purchases = Purchase::with('transaction')
            ->where('status', 'success')
            ->get();

        $totalSale = $sales->sum(function ($sale) {
            return $sale->transaction?->total ?? 0;
        });

        $totalChangeAdd = $changeAdds->sum(function ($changeAdd) {
            return $changeAdd->transaction?->total ?? 0;
        });


        $totalIn = $totalSale + $totalChangeAdd;
        $totalMayam = 0;
        $totalGram = 0;


        $totalChangeAdd = $changeAdds->sum(function ($changeAdd) {
            return $changeAdd->transaction?->total ?? 0;
        });

        $totalIncome = $totalSale + $totalChangeAdd;

        // Total Out
        $totalPurchase =  $purchases->sum(function ($purchase) {
            return  $purchase->transaction?->total ?? 0;
        });

        $totalChangeDeduct = $changeDeducts->sum(function ($changeDeduct) {
            return $changeDeduct->transaction?->total ?? 0;
        });


        $totalOutcome = $totalPurchase + $totalChangeDeduct;

        // Mayam dan Gram

        $getGramSale = $sales->flatMap(function ($sale) {
            return $sale->saleDetails;
        })->sum(function ($saleDetail) {
            return optional($saleDetail->product)->weight ?? 0;
        });

        $getGramChangeAdd = $changeAdds->flatMap(function ($changeAdd) {
            return $changeAdd->changeItems;
        })->sum(function ($changeItem) {
            return optional($changeItem->product)->weight ?? 0;
        });



        $totalMayam = $getGramSale + $getGramChangeAdd;

        $getMayamIn = GeneralService::getMayam($totalMayam);
        $getMayamOut = 0;

        $getGramIn = $getGramSale;
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
