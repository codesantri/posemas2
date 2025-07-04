<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeModelResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeModelResource;

class CreateChangeModel extends CreateRecord
{
    protected static string $resource = ChangeModelResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ExchangeService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return ExchangeService::handleCreate($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->label('Simpan & Proses Pertukaran')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Perhitungan?')
            ->modalSubheading('Untuk menghindari kesalahan, mohon cek ulang data Anda.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function () {
                $this->closeActionModal();
                $this->create();
            });
    }
}
