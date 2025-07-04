<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\SaleService;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'Ubah Penjualan';
    protected static ?string $breadcrumb = '';

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $this->form->fill(
            SaleService::getEditing($record)
        );
    }

    /**
     * Transform data sebelum mengisi form
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SaleService::getUpdate($this->record, $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return SaleService::getUpdating($record, $data);
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
