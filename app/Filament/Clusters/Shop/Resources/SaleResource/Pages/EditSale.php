<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\Shop\Resources\SaleResource;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'Ubah Penjualan';
    protected static ?string $breadcrumb = '';

    /**
     * Transform data sebelum mengisi form
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Sale $record */
        $record = $this->getRecord();

        $data['items'] = $record->saleDetails->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        return $data;
    }

    /**
     * Handle update logic saat form disimpan
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($record, $data) {
            // 1. Hitung total pembayaran baru
            $totalPayment = collect($data['items'])
                ->sum(fn($item) => $item['quantity'] * $item['price']);

            // 2. Update data sale
            $record->update([
                'customer_id' => $data['customer_id'] ?? null,
                'total_payment' => $totalPayment,
            ]);

            // 3. Hapus semua detail lama
            $record->saleDetails()->delete();

            // 4. Insert ulang sale details baru
            foreach ($data['items'] as $item) {
                $record->saleDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            // 5. Nofikasi sukses
            Notification::make()
                ->title('Penjualan berhasil diperbarui')
                ->success()
                ->send();

            return $record;
        });
    }
}
