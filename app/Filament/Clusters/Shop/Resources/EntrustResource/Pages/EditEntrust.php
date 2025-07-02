<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\EntrustResource;

class EditEntrust extends EditRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Ubah Data Titip Emas';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Entrust $record */
        $record = $this->getRecord();

        $data['items'] = $record->entrustDetails->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $invoice = optional($record->transaction)->invoice ?? null;

        $actions = [
            HeaderAction::getActivate($record->id),
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
            Actions\DeleteAction::make(),
        ];

        if ($record->status_entrust === 'active') {
            array_unshift($actions, HeaderAction::getGoPayment($invoice));
        }

        return $actions;
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
