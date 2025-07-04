<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\PurchaseService;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected static ?string $title = 'Detail Pembelian';

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            PurchaseService::getEditing($record)
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getBack(),
            HeaderAction::getDelete(),
            HeaderAction::getGoPayment($this->getRecord()->transaction->invoice),
        ];
    }
}
