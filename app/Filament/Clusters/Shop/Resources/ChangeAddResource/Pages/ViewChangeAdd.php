<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeAddResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeAddResource;

class ViewChangeAdd extends ViewRecord
{
    protected static string $resource = ChangeAddResource::class;

    protected static ?string $title = 'Detail Tukar Tambah';

    protected function fillForm(): void
    {
        /** @var Change $record */
        $record = $this->getRecord();
        $this->form->fill(ExchangeService::prepareFormData($record));
    }
}
