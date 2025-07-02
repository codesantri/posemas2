<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Filament\Clusters\Shop\Resources\SaleResource;
use App\Traits\Filament\Services\Sale\SaleFormService;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'Ubah Penjualan';
    protected static ?string $breadcrumb = '';

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            SaleFormService::getEditing($record)
        );
    }

    /**
     * Transform data sebelum mengisi form
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SaleFormService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return SaleFormService::getUpdating($record, $data);
    }


    protected function getSaveFormAction(): Action
    {
        return SubmitAction::update();
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
}
