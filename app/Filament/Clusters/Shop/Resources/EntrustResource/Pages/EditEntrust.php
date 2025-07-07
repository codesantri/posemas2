<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\FormService;
use App\Traits\Filament\Services\UpdateService;
use App\Traits\Filament\Services\EntrustService;
use App\Filament\Clusters\Shop\Resources\EntrustResource;


class EditEntrust extends EditRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Ubah Data Titip Emas';

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            FormService::getFormFill($record)
        );
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        return UpdateService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return UpdateService::getUpdating($record, $data);
    }

    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getBack(),
            HeaderAction::getGoPayment($this->getRecord()->transaction->invoice),
            HeaderAction::getDelete(),
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
        ];
    }
}
