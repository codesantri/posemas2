<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeAddResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\ChangeAddResource;

class ListChangeAdds extends ListRecords
{
    protected static string $resource = ChangeAddResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tukar Tambah')->icon('heroicon-m-plus-circle'),
            HeaderAction::getMenu()
        ];
    }
}
