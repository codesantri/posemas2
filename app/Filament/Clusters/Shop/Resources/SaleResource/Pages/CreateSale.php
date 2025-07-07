<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Actions\Action;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Action\SubmitAction;
use App\Filament\Clusters\Shop\Pages\Payment;
use App\Traits\Filament\Services\FormService;
use App\Traits\Filament\Services\CreateService;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Daftar Penjualan';
    protected static ?string $breadcrumb = '';

    public ?array $data = [];

    public function mount(): void
    {
        $items = FormService::getCartsData();

        if (empty($items)) {
            $this->redirect(route('filament.admin.shop.pages.products'));
        }

        $this->form->fill([
            'items' => $items,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CreateService::getCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return CreateService::handleCreate($data, Sale::class, 'sale');
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
