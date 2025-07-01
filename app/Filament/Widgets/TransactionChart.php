<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

// class TransactionChart extends ChartWidget
// {
//     protected static ?string $heading = 'Total Transaksi';
//     protected static string $color = 'info';

//     public ?string $filterYear = null;

//     public function mount(): void
//     {
//         $this->filterYear = (string) now()->year;
//     }

//     /**
//      * Generate filter dropdown for years.
//      */
//     protected function getFilters(): ?array
//     {
//         $years = Transaction::selectRaw('YEAR(transaction_date) as year')
//             ->distinct()
//             ->orderByDesc('year')
//             ->pluck('year')
//             ->toArray();

//         return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
//     }

//     /**
//      * Update filter value when changed from UI.
//      */
//     public function updatedFilterYear($value): void
//     {
//         $this->filterYear = $value;
//         $this->resetChart();
//     }

//     /**
//      * Prepare chart data based on selected year and transaction types.
//      */
//     protected function getData(): array
//     {
//         $types = ['sale', 'purchase', 'pawning', 'change', 'service'];
//         $year = $this->filterYear ?? now()->year;

//         $datasets = collect($types)->map(function ($type) use ($year) {
//             $monthlyTotals = Transaction::selectRaw('MONTH(transaction_date) as month, SUM(total_amount) as total')
//                 ->where('transaction_type', $type)
//                 ->where('status', 'success')
//                 ->whereYear('transaction_date', $year)
//                 ->groupBy(DB::raw('MONTH(transaction_date)'))
//                 ->pluck('total', 'month');

//             $data = collect(range(1, 12))->map(fn($month) => round($monthlyTotals->get($month, 0), 2));

//             return [
//                 'label' => $this->getLabelForType($type),
//                 'data' => $data,
//                 'fill' => true,
//                 'borderColor' => $this->getColorForType($type),
//                 'tension' => 0.4,
//             ];
//         });

//         return [
//             'datasets' => $datasets->toArray(),
//             'labels' => collect(range(1, 12))
//                 ->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))
//                 ->toArray(),
//         ];
//     }

//     /**
//      * Chart type (line, bar, etc.).
//      */
//     protected function getType(): string
//     {
//         return 'line';
//     }

//     /**
//      * Return custom color per transaction type.
//      */
//     private function getColorForType(string $type): string
//     {
//         return match ($type) {
//             'sale'     => '#22c55e', // green
//             'purchase' => '#f59e0b', // yellow
//             'pawning'  => '#3b82f6', // blue
//             'change'   => '#ef4444', // red
//             'service'  => '#6b7280', // gray
//             default    => '#6b7280', // fallback
//         };
//     }

//     /**
//      * Return readable label per transaction type.
//      */
//     private function getLabelForType(string $type): string
//     {
//         return match ($type) {
//             'sale'     => 'Penjualan',
//             'purchase' => 'Pembelian',
//             'pawning'  => 'Gadai',
//             'change'   => 'Pertukaran',
//             'service'  => 'Jasa Pembuatan',
//             default    => ucfirst($type),
//         };
//     }
// }
