<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeDeductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeDeductResource;

class ViewChangeDeduct extends ViewRecord
{
    protected static string $resource = ChangeDeductResource::class;

    protected static ?string $title = 'Detail Tukar Kurang';

    protected function fillForm(): void
    {
        /** @var Change $record */
        $record = $this->getRecord();
        $this->form->fill(ExchangeService::prepareFormData($record));
    }
}
