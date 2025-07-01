<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\Filament\Action\HeaderAction;
use App\Filament\Clusters\Shop\Pages\Payment;
use App\Traits\Filament\Services\PaymentService;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Daftar Penjualan';
    protected static ?string $breadcrumb = '';

    public ?array $data = [];

    public function mount(): void
    {
        parent::mount();

        $carts = Cart::with('product')->get();

        if ($carts->isEmpty()) {
            $this->redirect(url()->previous() ?? SaleResource::getUrl());
        }

        $this->form->fill([
            'items' => $carts->map(fn($cart) => [
                'cart_id' => $cart->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'price' => null,
            ])->toArray(),
        ]);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // Hitung total payment
            $totalPayment = collect($data['items'])
                ->sum(fn($item) => $item['quantity'] * $item['price']);

            // Buat transaksi
            $transaction = Transaction::create([
                'transaction_type' => 'sale',
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);

            // Buat sales
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_id' => $transaction->id,
                'total_payment' => $totalPayment,
            ]);

            // Detail
            foreach ($data['items'] as $item) {
                $sale->saleDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                if (isset($item['cart_id'])) {
                    Cart::where('id', $item['cart_id'])->delete();
                }
            }
            return $sale;
        });
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddCustomerAction(),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->label('Proses Penjualan')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi?')
            ->modalSubheading('Untuk menghindari kesalahan, mohon cek ulang data Anda.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function () {
                $this->create();
                $this->closeActionModal();
            });
    }

    protected function getRedirectUrl(): string
    {
        return Payment::getUrl(['invoice' => $this->record->transaction->invoice]);
    }
}
