<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeAddResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\ExchangeService;
use App\Filament\Clusters\Shop\Resources\ChangeAddResource;

class CreateChangeAdd extends CreateRecord
{
    protected static string $resource = ChangeAddResource::class;


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

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): Action
    {
        return SubmitAction::create();
    }
}
