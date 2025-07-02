<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use App\Traits\Filament\Services\Purchase\PurchaseFormService;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected static ?string $title = 'Detail Pembelian';




    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            PurchaseFormService::prepareFormData($record)
        );
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
