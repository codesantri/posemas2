<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\Widget;
use App\Traits\Filament\Services\GeneralService;

class TotalTransactions extends Widget
{
    protected static string $view = 'filament.widgets.total-transactions';
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // Get sum of all successful transaction totals
        $total = Transaction::where('status', 'success')->sum('total');

        // Get all successful transactions with relationships
        $transactions = Transaction::with([
            'sale.saleDetails',
            'purchase.purchaseDetails',
            'entrust.entrustDetails',
            'exchange.changeItems'
        ])->where('status', 'success')->get();

        // Calculate total weight from all transaction types
        $totalWeight = $this->calculateTotalWeight($transactions);

        return [
            'data' => [
                'total' => $total ?? 0,
                'total_weight' => $totalWeight ?? 0,
                'total_mayam' => GeneralService::getMayam($totalWeight) ?? 0
            ]
        ];
    }

    private function calculateTotalWeight($transactions): float
    {
        $weight = 0;

        foreach ($transactions as $transaction) {
            switch ($transaction->transaction_type) {
                case 'sale':
                    if ($transaction->sale && $transaction->sale->saleDetails) {
                        $weight += $transaction->sale->saleDetails->sum('total_weight');
                    }
                    break;

                case 'purchase':
                    if ($transaction->purchase && $transaction->purchase->purchaseDetails) {
                        $weight += $transaction->purchase->purchaseDetails->sum('total_weight');
                    }
                    break;

                case 'entrust':
                    if ($transaction->entrust && $transaction->entrust->entrustDetails) {
                        $weight += $transaction->entrust->entrustDetails->sum('total_weight');
                    }
                    break;

                case 'change':
                    if ($transaction->exchange && $transaction->exchange->changeItems) {
                        $weight += $transaction->exchange->changeItems->sum('total_weight');
                    }
                    break;
            }
        }

        return $weight;
    }
}
