<?php

namespace App\Traits\Filament\Services\Purchase;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait PurchaseFormService
{
    /**
     * Validasi & siapkan data pembelian.
     */
    /**
     * Validasi & siapkan data pembelian.
     */
    public static function getCreate(array $data): array
    {
        Log::info('Data mentah dari form:', $data);

        $totalPayment = 0;
        $products = [];
        $errors = [];

        if (!isset($data['products']) || !is_array($data['products'])) {
            $errors[] = "Minimal satu produk harus diisi.";
        } else {
            foreach ($data['products'] as $item) {
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

                $products[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (empty($products)) {
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
            'products' => $products,
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
            $purchase->status = $data['status'] ?? 'pending';
            $purchase->save();

            // Simpan detail produk
            foreach ($data['products'] ?? [] as $item) {
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

    public static function prepareFormData(Model $record): array
    {
        $products = [];

        foreach ($record->purchaseDetails as $item) {
            $itemData = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];

            $products[] = $itemData;
        }

        return [
            ...$record->attributesToArray(),
            'products' => $products,
        ];
    }

    /**
     * Memproses data form untuk update dan melakukan validasi/perhitungan.
     * Mirip dengan getCreateChange, tetapi menerima objek Change yang ada.
     *
     * @param Purchase $purchase
     * @param array $data
     * @return array
     */
    public static function getUpdate(Purchase $purchase, array $data): array
    {
        Log::info('Data mentah dari form (UPDATE):', $data);

        $totalOld = 0;
        $errors = [];
        $products = [];

        if (!isset($data['products']) || !is_array($data['products'])) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        } else {
            foreach ($data['products'] as $item) {
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

                $products[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (empty($products)) {
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
            'products' => $products,
        ];

        Log::info('Data setelah validasi dan perhitungan (UPDATE):', $result);
        return $result;
    }


    /**
     * Memperbarui record Change dan ChangeItems terkait.
     *
     * @param Model $record
     * @param array $data
     * @return Model
     */
    public static function getRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            $purchase = $record;

            $purchase->customer_id = $data['customer_id'];
            $purchase->total_payment = $data['total_payment'];
            $purchase->status = $data['status'] ?? 'pending';
            $purchase->save();

            $purchase->purchaseDetails()->delete();

            foreach ($data['products'] ?? [] as $item) {
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
                ->title("Gagal memperbarui data.")
                ->body("Terjadi kesalahan: " . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Error saat memperbarui transaksi perubahan:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
