<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\PurchaseService;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            PurchaseService::getEditing($record)
        );
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return PurchaseService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return PurchaseService::getUpdating($record, $data);
    }

    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
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
}
