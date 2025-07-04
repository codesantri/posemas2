<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeAddResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeAddResource;

class EditChangeAdd extends EditRecord
{
    protected static string $resource = ChangeAddResource::class;

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            ExchangeService::getEditing($record)
        );
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return ExchangeService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return ExchangeService::getUpdating($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getBack(),
            HeaderAction::getDelete(),
            HeaderAction::getGoPayment($this->getRecord()->transaction->invoice),
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
    }
}
