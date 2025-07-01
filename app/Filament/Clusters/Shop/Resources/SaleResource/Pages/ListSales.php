<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Penjualan';
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('penjualan')
                ->label('Tambah Penjualan')
                ->icon('heroicon-m-plus-circle')
                ->url(route('filament.admin.shop.pages.products')),
            HeaderAction::getMenu()
        ];
    }

}
