<?php

namespace App\Filament\Resources\HistoryResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\HistoryResource;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\HistoryResource\Widgets\CountOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListHistories extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = HistoryResource::class;
    protected static ?string $title = 'Riwayat transaksi';

    protected function getHeaderActions(): array
    {
        return [
            // Uncomment kalau mau tombol tambah data:
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CountOverview::class,
        ];
    }


    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Transaksi'),

            'sale' => Tab::make('Penjualan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'sale')
                ),

            'purchase' => Tab::make('Pembelian')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'purchase')
                ),

            'tukar_tambah' => Tab::make('Tukar Tambah')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'change')
                        ->whereHas(
                            'exchange',
                            fn($q) =>
                            $q->where('change_type', 'add')
                        )
                ),

            'tukar_kurang' => Tab::make('Tukar Kurang')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'change')
                        ->whereHas(
                            'exchange',
                            fn($q) =>
                            $q->where('change_type', 'deduct')
                        )
                ),

            'tukar_model' => Tab::make('Tukar Model')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'change')
                        ->whereHas(
                            'exchange',
                            fn($q) =>
                            $q->where('change_type', 'change_model')
                        )
                ),

            'entrust' => Tab::make('Titip Emas')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'entrust')
                ),
        ];
    }
}
