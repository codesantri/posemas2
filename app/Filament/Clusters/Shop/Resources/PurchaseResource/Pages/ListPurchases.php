<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Pembelian')->icon('heroicon-m-plus'),
            HeaderAction::getMenu()
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->whereHas('transaction', function (Builder $query) {
                $query->where('transaction_type', 'purchase');
            });
    }
}
