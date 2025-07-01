<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\TransactionWidget;
use App\Filament\Resources\TransactionResource;
use Illuminate\Contracts\Support\Htmlable;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;
    protected static ?string $title = 'Riwayat transaksi';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
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
                        ->whereHas('exchange', function ($q) {
                            $q->where('change_type', 'add');
                        })
                ),

            'tukar_kurang' => Tab::make('Tukar Kurang')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'change')
                        ->whereHas('exchange', function ($q) {
                            $q->where('change_type', 'deduct');
                        })
                ),

            'tukar_model' => Tab::make('Tukar Model')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'change')
                        ->whereHas('exchange', function ($q) {
                            $q->where('change_type', 'change_model');
                        })
                ),
        ];
    }


    protected function getHeaderWidgets(): array
    {
        return [
            // TransactionWidget::class,
        ];
    }
}
