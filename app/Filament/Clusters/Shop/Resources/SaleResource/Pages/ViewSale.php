<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\FormService;
use App\Traits\Filament\Services\SaleService;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Detail Penjualan';


    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            FormService::getFormFill($record)
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
