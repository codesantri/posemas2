<?php

namespace App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

use Filament\Actions;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Resources\EntrustResource;
use App\Models\Entrust;

class CreateEntrust extends CreateRecord
{
    protected static string $resource = EntrustResource::class;
    protected static ?string $title = 'Data Titip Emas';


    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $totalPayment = collect($data['items'])
                ->sum(fn($item) => $item['quantity'] * $item['price']);
            $transaction = Transaction::create([
                'transaction_type' => 'entrust',
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);

            $entrust = Entrust::create([
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_id' => $transaction->id,
                'total_payment' => $totalPayment,
            ]);
            foreach ($data['items'] as $item) {
                $entrust->entrustDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }
            return $entrust;
        });
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

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->label('Simpan & Proses Titip Emas')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi?')
            ->modalSubheading('Untuk menghindari kesalahan, mohon cek ulang data Anda.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function () {
                $this->create();
                $this->closeActionModal();
            });
    }
}
