<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeModelResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeModelResource;

class EditChangeModel extends EditRecord
{
    protected static string $resource = ChangeModelResource::class;

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

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->submit(null)
            ->label('Simpan Perubahan')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembaruan?')
            ->modalSubheading('Pastikan perubahan data sudah benar sebelum melanjutkan.')
            ->modalButton('Ya, Simpan')
            ->action(function () {
                $this->closeActionModal();
                $this->save(); // panggil fungsi simpan bawaan
            });
    }
}
