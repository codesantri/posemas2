<?php

namespace App\Traits\Filament\Services\Sale;

use App\Models\Cart;
use App\Models\Product;
use Filament\Notifications\Notification;

trait SaleService
{

    public static function addToCart(int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            Notification::make()
                ->title("Produk tidak ditemukan!")
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        $existingCart = Cart::where('product_id', $product->id)->first();

        if ($existingCart) {
            $existingCart->update([
                'quantity' => $existingCart->quantity + 1,
            ]);
        } else {
            Cart::create([
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        Notification::make()
            ->title("{$product->name} berhasil masuk ke keranjang.")
            ->success()
            ->duration(3000)
            ->send();
    }

    public static function updateCart(string $action, int $id): void
    {
        $cart = Cart::findOrFail($id);

        if ($action === 'increment') {
            $cart->quantity += 1;
        } elseif ($action === 'decrement') {
            $cart->quantity -= 1;
        }
        $cart->save();
        Notification::make()
            ->title("Kuantitas diperbarui")
            ->success()
            ->duration(3000)
            ->send();
    }
}
