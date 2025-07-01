<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use App\Traits\Filament\Services\Purchase\PurchaseFormService;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            PurchaseFormService::prepareFormData($record)
        );
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        // Hanya validasi dan siapkan data
        // Jangan update model di sini
        return PurchaseFormService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Panggil method yang lengkap melakukan update data utama dan detail
        return PurchaseFormService::getRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $invoice = optional($record->transaction)->invoice ?? null;
        return [
            HeaderAction::getGoPayment($invoice),
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
                $this->save();
            });
    }
}
