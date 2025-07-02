<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use App\Traits\Filament\Services\Purchase\PurchaseFormService;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected static ?string $title = 'Data Pembelian';
    protected array $processedProducts = [];

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddProductAction(),
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PurchaseFormService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return PurchaseFormService::handleCreate($data);
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): Action
    {
        return SubmitAction::create();
    }
}
