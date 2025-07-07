<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\FormService;
use App\Filament\Clusters\Shop\Resources\EntrustResource;

class ViewEntrust extends ViewRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Detail Titip Emas';

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
            HeaderAction::getGoPayment($this->getRecord()->transaction->invoice),
            HeaderAction::getDelete(),
        ];
    }
}
