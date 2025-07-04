<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeDeductResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeDeductResource;

class ViewChangeDeduct extends ViewRecord
{
    protected static string $resource = ChangeDeductResource::class;

    protected static ?string $title = 'Detail Tukar Kurang';

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            ExchangeService::getEditing($record)
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
