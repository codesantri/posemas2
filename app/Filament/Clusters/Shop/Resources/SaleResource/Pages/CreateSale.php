<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Filament\Clusters\Shop\Pages\Payment;
use App\Traits\Filament\Services\SaleService;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Daftar Penjualan';
    protected static ?string $breadcrumb = '';

    public ?array $data = [];

    public function mount(): void
    {
        $this->mounting();
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SaleService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return SaleService::handleCreate($data);
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): Action
    {
        return SubmitAction::create();
    }

    protected function getRedirectUrl(): string
    {
        return Payment::getUrl(['invoice' => $this->record->transaction->invoice]);
    }
}
