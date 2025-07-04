<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
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
            EntrustService::getEditing($record)
        );
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return EntrustService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return EntrustService::getUpdating($record, $data);
    }

    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getBack(),
            HeaderAction::getActivate($this->getRecord()->id),
            HeaderAction::getGoPayment($this->getRecord()->transaction->invoice),
            HeaderAction::getDelete(),
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
        ];
    }
}
