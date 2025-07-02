<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use App\Traits\Filament\Services\Purchase\PurchaseFormService;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            PurchaseFormService::getEditing($record)
        );
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return PurchaseFormService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return PurchaseFormService::getUpdating($record, $data);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $invoice = optional($record->transaction)->invoice ?? null;
        return [
            HeaderAction::getGoPayment($invoice),
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
            DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
    }
}
