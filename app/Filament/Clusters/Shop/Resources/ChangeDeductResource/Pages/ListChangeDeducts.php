<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeDeductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\ChangeDeductResource;

class ListChangeDeducts extends ListRecords
{
    protected static string $resource = ChangeDeductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tukar Kurang')->icon('heroicon-m-plus-circle'),
            HeaderAction::getMenu()
        ];
    }
}
