<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeDeductResource\Pages;

use Filament\Actions;
use App\Models\Change;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeDeductResource;

class EditChangeDeduct extends EditRecord
{
    protected static string $resource = ChangeDeductResource::class;

    protected function fillForm(): void
    {
        /** @var Change $record */
        $record = $this->getRecord();
        $this->form->fill(ExchangeService::prepareFormData($record));
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return ExchangeService::getUpdateChange($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $validated = ExchangeService::getUpdateChange($record, $data); // data sudah tervalidasi
        return ExchangeService::getRecordUpdate($record, $validated);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getGoPayment($this->getRecord()->invoice),
            Actions\DeleteAction::make(),
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
