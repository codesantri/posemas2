<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\EntrustResource;

class ViewEntrust extends ViewRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Detail Titip Emas';

    protected function getHeaderActions(): array
    {

        $record = $this->getRecord();
        $invoice = optional($record->transaction)->invoice ?? null;

        $actions = [
            HeaderAction::getActivate($record->id),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];

        if ($record->status_entrust === 'active') {
            array_unshift($actions, HeaderAction::getGoPayment($invoice));
        }

        return $actions;
    }

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
}
