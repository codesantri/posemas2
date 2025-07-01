<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeModelResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\ChangeModelResource;

class ListChangeModels extends ListRecords
{
    protected static string $resource = ChangeModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tukar Model')->icon('heroicon-m-plus-circle'),
            HeaderAction::getMenu()
        ];
    }
}
