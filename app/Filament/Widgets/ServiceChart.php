<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

// class ServiceChart extends ChartWidget
// {
//     protected static ?string $heading = 'Jasa Pembuatan';
//     protected static string $color = 'gray';

//     public ?string $filter = null;

//     public function mount(): void
//     {
//         $this->filter = (string) now()->year;
//     }

//     protected function getFilters(): ?array
//     {
//         $years = Transaction::selectRaw('YEAR(transaction_date) as year')
//             ->where('transaction_type', 'service')
//             ->distinct()
//             ->orderByDesc('year')
//             ->pluck('year')
//             ->toArray();

//         if (empty($years)) {
//             $years[] = now()->year;
//         }

//         return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
//     }

//     protected function getData(): array
//     {
//         $year = $this->filter ?? now()->year;

//         $monthlyServices = cache()->remember(
//             "monthly_services_{$year}", // 🔑 Unique cache key
//             now()->addMinutes(30),
//             fn() => Transaction::selectRaw('MONTH(transaction_date) as month, SUM(total_amount) as total')
//                 ->where('transaction_type', 'service')
//                 ->where('status', 'success')
//                 ->whereYear('transaction_date', $year)
//                 ->groupBy(DB::raw('MONTH(transaction_date)'))
//                 ->orderBy('month')
//                 ->pluck('total', 'month')
//                 ->toArray()
//         );

//         $data = collect(range(1, 12))
//             ->map(fn($month) => (float) ($monthlyServices[$month] ?? 0))
//             ->toArray();

//         $labels = collect(range(1, 12))
//             ->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))
//             ->toArray();

//         return [
//             'datasets' => [
//                 [
//                     'label' => "Total Jasa Tahun $year",
//                     'data' => $data,
//                     'backgroundColor' => '#6b7280', // Abu-abu
//                 ],
//             ],
//             'labels' => $labels,
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar';
//     }
// }
