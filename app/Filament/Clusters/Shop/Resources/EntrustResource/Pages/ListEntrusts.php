<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\EntrustResource;

class ListEntrusts extends ListRecords
{
    protected static string $resource = EntrustResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Titip Emas')->icon('heroicon-m-plus'),
            HeaderAction::getMenu()
        ];
    }
}
