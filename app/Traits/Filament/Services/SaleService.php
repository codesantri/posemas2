<?php

namespace App\Traits\Filament\Services;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filament\Forms\FormInput;
use Filament\Notifications\Notification;
use App\Traits\Filament\Action\SelectAction;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Actions\Action;

trait SaleService
{

    public static function getForm()
    {
        return [
            Section::make('Daftar Belanja')
                ->schema([
                    ...SelectAction::getSelectCustomer('customer_id'),
                    Repeater::make('items')
                        ->label('Daftar belanja')
                        ->disableItemCreation()
                        ->disableItemDeletion()
                        ->schema([
                            ...FormInput::selectProduct('produk_id'),
                            Grid::make(2)->schema([
                                ...FormInput::inputQuantity('quantity'),
                                ...FormInput::inputPrice('price'),

                                Actions::make([
                                    Action::make('delete')
                                        ->label('Hapus')
                                        ->size('sm')
                                        ->color('danger')
                                        ->requiresConfirmation()
                                        ->action(function (array $arguments, array $state, \Filament\Forms\Set $set) {
                                            if (isset($state['cart_id'])) {
                                                $cart = \App\Models\Cart::find($state['cart_id']);

                                                if ($cart) {
                                                    $cart->delete();

                                                    Notification::make()
                                                        ->title('Item berhasil dihapus')
                                                        ->success()
                                                        ->duration(3000)
                                                        ->send();
                                                    redirect()->route('filament.admin.shop.resources.sales.create');
                                                } else {
                                                    Notification::make()
                                                        ->title('Item tidak ditemukan')
                                                        ->danger()
                                                        ->duration(3000)
                                                        ->send();
                                                }
                                            } else {
                                                Notification::make()
                                                    ->title('ID keranjang tidak valid')
                                                    ->danger()
                                                    ->duration(3000)
                                                    ->send();
                                            }
                                        }),
                                ]),
                            ]),
                        ])
                        ->columns(1)
                        ->itemLabel(function (array $state): ?string {
                            if (isset($state['product_id'])) {
                                $product = Product::with(['karat', 'category', 'type'])->find($state['product_id']);
                                return $product
                                    ? "{$product->name} / {$product->karat->karat}-{$product->karat->rate}% / {$product->category->name} / {$product->type->name}"
                                    : null;
                            }
                            return null;
                        })
                ]),
        ];
    }


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

    public function mounting(): void
    {
        // Call parent mount if exists
        if (method_exists(parent::class, 'mount')) {
            parent::mount();
        }

        $carts = Cart::with('product')->get();

        if ($carts->isEmpty()) {
            $this->redirect(route('filament.admin.shop.pages.products'));
            return;
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

    public static function handleCreate(array $data): Sale
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'transaction_type' => 'sale',
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);

            $sale = new Sale();
            $sale->transaction_id = $transaction->id;
            $sale->customer_id = $data['customer_id'];
            $sale->total_payment = $data['total_payment'];
            $sale->status = 'pending';
            $sale->save();

            foreach ($data['items'] ?? [] as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            DB::table('carts')->delete();
            DB::commit();
            return $sale;
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Gagal menyimpan data')
                ->danger()
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->persistent()
                ->send();

            throw $e;
        }
    }

    public static function getEditing(Model $record): array
    {
        $items = [];

        foreach ($record->saleDetails as $item) {
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

    public static function getUpdate(Sale $sale, array $data): array
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
            $record->saleDetails()->delete();

            // Recreate sale details
            foreach ($data['items'] ?? [] as $item) {
                SaleDetail::create([
                    'sale_id'    => $record->id,
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
