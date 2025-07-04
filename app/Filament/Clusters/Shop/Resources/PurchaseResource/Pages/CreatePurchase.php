<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\PurchaseService;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

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
        return PurchaseService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return PurchaseService::handleCreate($data);
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
