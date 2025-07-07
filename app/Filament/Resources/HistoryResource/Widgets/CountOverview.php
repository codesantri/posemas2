<?php

namespace App\Filament\Resources\HistoryResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Traits\Filament\Services\GeneralService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\HistoryResource\Pages\ListHistories;

class CountOverview extends BaseWidget
{
    use InteractsWithPageTable;
    protected static ?string $pollingInterval = '3s';


    protected function getTablePage(): string
    {
        return ListHistories::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        $transactions = $query->with([
            'sale.saleDetails.product',
            'purchase.purchaseDetails.product',
            'exchange.changeItems.product',
            'entrust.entrustDetails.product',
        ])->get();

        $totalSaleGram = 0;
        $totalPurchaseGram = 0;
        $totalGramEntrust = 0;
        $totalChangeGram = 0;


        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'sale' && $transaction->sale) {
                foreach ($transaction->sale->saleDetails as $detail) {
                    $gram = $detail->total_weight;
                    $totalSaleGram += $gram ?? 0;
                }
            }

            if ($transaction->transaction_type === 'purchase' && $transaction->purchase) {
                foreach ($transaction->purchase->purchaseDetails as $detail) {
                    $gram = $detail->total_weight;
                    $totalPurchaseGram += $gram ?? 0;
                }
            }


            if ($transaction->transaction_type === 'change' && $transaction->exchange) {
                foreach ($transaction->exchange->changeItems as $detail) {
                    $gram = $detail->total_weight;
                    $totalChangeGram += $gram ?? 0;
                }
            }


            if ($transaction->transaction_type === 'entrust' && $transaction->entrust) {
                foreach ($transaction->entrust->entrustDetails as $detail) {
                    $gram = $detail->total_weight;
                    $totalGramEntrust += $gram ?? 0;
                }
            }
        }


        $totalWeight = $totalSaleGram + $totalPurchaseGram + $totalGramEntrust + $totalChangeGram;
        $totalMayam = GeneralService::getMayam($totalWeight);
        return [
            Stat::make('Total', 'Rp ' . number_format($this->getPageTableQuery()->sum('total'), 0, ',', '.'))->color('success'),
            Stat::make('Total Mayam', $totalMayam . ' my'), //getCoutMayam
            Stat::make('Total Gram', $totalWeight . ' gr'), //getCountGram,
        ];
    }
}
