<?php

namespace App\Traits\Filament\Services;

use App\Models\Detail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait UpdateService
{

    public static function getUpdate(Model $model, array $data): array
    {
        $totalPayment = 0;
        $errors = [];
        $items = [];

        if (!isset($data['items']) || !is_array($data['items'])) {
            $errors[] = "Minimal satu produk harus diisi.";
        } else {
            foreach ($data['items'] as $item) {
                if (!isset($item['product_id']) || empty($item['product_id'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $karatName = $item['karat_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name;
                $karatName = $product->karat->name;

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
                $totalWeight = $qty * $product->weight;

                $totalPayment += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'karat_name' => $karatName,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'total_weight' => $totalWeight,
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

            throw ValidationException::withMessages(['general' => $errors]);
        }

        return [
            'customer_id'   => $data['customer_id'],
            'total_payment' => $totalPayment,
            'status'        => 'pending',
            'items'         => $items,
        ];
    }


    public static function getUpdating(Model $record, array $data): Model
    {
        DB::beginTransaction();

        try {
            // Update Sale
            $record->update([
                'customer_id'   => $data['customer_id'],
                'total_payment' => $data['total_payment'],
            ]);

            // Hapus detail lama
            $record->transaction->details()->delete();
            // Create ulang detail
            foreach ($data['items'] ?? [] as $item) {
                Detail::create([
                    'transaction_id' => $record->transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'karat_name' => $item['karat_name'],
                    'total_weight' => $item['total_weight'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            return $record->fresh(); // supaya relasi ter-refresh
        } catch (\Throwable $e) {
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
