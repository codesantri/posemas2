<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Traits\Filament\Services\EntrustService;
use App\Filament\Clusters\Shop\Resources\EntrustResource;

class CreateEntrust extends CreateRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Data Titip Emas';



    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return EntrustService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return EntrustService::handleCreate($data);
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
