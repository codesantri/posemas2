<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Detail Penjualan';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Sale $record */
        $record = $this->getRecord();

        $data['items'] = $record->saleDetails->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $invoice = optional($record->transaction)->invoice ?? null;
        return [
            HeaderAction::getGoPayment($invoice),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
