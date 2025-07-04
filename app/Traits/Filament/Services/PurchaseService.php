<?php

namespace App\Traits\Filament\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filament\Forms\FormInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait PurchaseService
{

    public static function getForm()
    {
        return [
            Card::make([
                ...FormInput::selectCustomer('customer_id'),
                Repeater::make('items')
                    ->label('Data Produk')
                    ->schema([
                        ...FormInput::selectProduct('produk_id'),
                        Grid::make(2)
                            ->schema([
                                ...FormInput::inputQuantity('quantity'),
                                ...FormInput::inputPrice('price'),
                            ])

                    ])->addActionLabel('Tambah'),
            ]),
        ];
    }

    public static function getCreate(array $data): array
    {
        $totalPayment = 0;
        $items = [];
        $errors = [];

        if (!isset($data['items']) || !is_array($data['items'])) {
            $errors[] = "Minimal satu produk harus diisi.";
        } else {
            foreach ($data['items'] as $item) {
                if (!isset($item['product_id']) || is_null($item['product_id'])) {
                    continue; // Baris kosong
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name ?? "Tidak diketahui";

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

                if ($qty <= 0) {
                    $errors[] = "Jumlah untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalPayment += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (empty($items)) {
            $errors[] = "Minimal satu produk harus diisi.";
        }

        if (!empty($errors)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->danger()
                ->body(implode("\n", $errors))
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'general' => $errors,
            ]);
        }

        return [
            'customer_id' => $data['customer_id'],
            'total_payment' => $totalPayment,
            'status' => 'pending',
            'items' => $items,
        ];
    }
    /**
     * Simpan transaksi pembelian.
     */
    public static function handleCreate(array $data): Purchase
    {
        DB::beginTransaction();

        try {

            $transaction = Transaction::create([
                'transaction_type' => 'purchase',
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);

            $purchase = new Purchase();
            $purchase->transaction_id = $transaction->id;
            $purchase->customer_id = $data['customer_id'];
            $purchase->total_payment = $data['total_payment'];
            $purchase->status = 'pending';
            $purchase->save();

            // Simpan detail produk
            foreach ($data['items'] ?? [] as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            DB::commit();
            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Gagal menyimpan data')
                ->danger()
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->persistent()
                ->send();

            Log::error('Error saat menyimpan transaksi pembelian:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public static function getEditing(Model $record): array
    {
        $items = [];

        foreach ($record->purchaseDetails as $item) {
            $itemData = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];

            $items[] = $itemData;
        }

        return [
            ...$record->attributesToArray(),
            'items' => $items,
        ];
    }

    public static function getUpdate(Purchase $purchase, array $data): array
    {

        $totalOld = 0;
        $errors = [];
        $items = [];

        if (!isset($data['items']) || !is_array($data['items'])) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        } else {
            foreach ($data['items'] as $item) {
                if (empty($item['product_id'])) continue;

                $product = Product::find($item['product_id']);
                $productName = $product->name ?? ($item['product_name'] ?? "Tidak diketahui");

                if (!$product) {
                    $errors[] = "Produk lama '$productName' tidak ditemukan.";
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

                if ($qty <= 0) {
                    $errors[] = "Quantity untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalOld += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (empty($items)) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        }

        if (!empty($errors)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->danger()
                ->body(implode("\n", $errors))
                ->persistent()
                ->send();

            throw ValidationException::withMessages(['general' => $errors]);
        }

        $result = [
            'customer_id' => $data['customer_id'],
            'total_payment' => $totalOld,
            'status' => $data['status'] ?? 'pending',
            'items' => $items,
        ];
        return $result;
    }
    public static function getUpdating(Model $record, array $data): Model
    {
        DB::beginTransaction();

        try {
            // Update main sale record
            $record->update([
                'customer_id'   => $data['customer_id'],
                'total_payment' => $data['total_payment'],
            ]);

            // Delete old sale details safely
            $record->purchaseDetails()->delete();

            // Recreate sale details
            foreach ($data['items'] ?? [] as $item) {
                PurchaseDetail::create([
                    'purchase_id'    => $record->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);
            }

            DB::commit();
            return $record->fresh(); // Return the updated model instance
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title("Gagal memperbarui data.")
                ->body("Terjadi kesalahan: " . $e->getMessage())
                ->danger()
                ->send();
            throw $e;
        }
    }
}
