<?php

namespace App\Traits\Filament\Services;

use App\Models\Cart;
use App\Models\Product;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use App\Traits\Filament\Services\GeneralService;

trait FormService
{
    public static function getForm(string $type = ''): array
    {
        $hasCartItems = $type ? Cart::exists() : true;

        return [
            Section::make('Daftar Produk')
                ->schema([
                    ...self::selectCustomer(),
                    Repeater::make('items')
                        ->label('')
                        ->addActionLabel('Tambah Produk')
                        ->deletable((!$type || !$hasCartItems)) // false hanya jika $type ada DAN cart kosong
                        ->schema([
                            ...self::selectProduct(),
                            Grid::make(2)
                                ->schema([
                                    ...self::inputQuantity(),
                                    ...self::inputPrice(),
                                    self::getDeleteAction($type),
                                ]),
                        ])
                        ->columns(1)
                        ->itemLabel(fn(array $state): ?string => self::getItemLabel($state))
                ]),
        ];
    }

    protected static function getDeleteAction(string $type): Actions
    {
        $hasCartItems = $type ? Cart::exists() : true;

        return Actions::make([
            Action::make('delete')
                ->label('Hapus')
                ->size('sm')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(!$hasCartItems)
                ->action(function (array $arguments, array $state, Set $set) {
                    try {
                        if (!isset($state['cart_id'])) {
                            throw new \Exception('ID keranjang tidak valid');
                        }

                        $cart = Cart::find($state['cart_id']);
                        if (!$cart) {
                            throw new \Exception('Item tidak ditemukan');
                        }

                        $cart->delete();

                        Notification::make()
                            ->title('Item berhasil dihapus')
                            ->success()
                            ->duration(3000)
                            ->send();

                        redirect()->route('filament.admin.shop.resources.sales.create');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->duration(3000)
                            ->send();
                    }
                }),
        ])->visible((bool) $type && $hasCartItems);
    }

    protected static function getItemLabel(array $state): ?string
    {
        if (!isset($state['product_id'])) {
            return null;
        }

        $product = Product::with(['karat', 'category', 'type'])
            ->find($state['product_id']);

        if (!$product) {
            return 'Produk tidak ditemukan';
        }

        return sprintf(
            '%s / %s-%s%% / %s / %s',
            $product->name,
            $product->karat->karat,
            $product->karat->rate,
            $product->category->name,
            $product->type->name
        );
    }


    public static function getFormFill(Model $record): array
    {
        $items = [];

        foreach ($record->transaction->details as $item) {
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

    public static function getCartsData(): array
    {
        $carts = Cart::with('product')->get();

        return $carts->map(fn($cart) => [
            'cart_id'    => $cart->id,
            'product_id' => $cart->product_id,
            'quantity'   => $cart->quantity,
            'price'      => null,
        ])->toArray();
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

    public static function selectCustomer(): array
    {
        return [
            Select::make('customer_id')
                ->label('Nama Pelanggan (Opsional)')
                ->searchable()
                ->preload()
                ->options(fn() => Customer::orderByDesc('id')
                    ->get()
                    ->mapWithKeys(fn($customer) => [
                        $customer->id => "{$customer->name} - {$customer->phone} - {$customer->address}"
                    ]))
                ->columnSpanFull(),
        ];
    }

    public static function selectProduct(): array
    {
        return [
            Grid::make(12)
                ->schema([
                    Select::make('product_id')
                        ->label('Nama Produk')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->live()
                        ->options(function () {
                            return Product::with(['karat', 'category', 'type'])
                                ->latest()
                                ->get()
                                ->mapWithKeys(function ($product) {
                                    $weight = $product->weight;
                                    $weightText = number_format($weight, 2, ',', '.') . ' Gram';
                                    $mayamValue = GeneralService::getMayam($weight);
                                    $mayamText = number_format($mayamValue, 2, ',', '.') . ' Mayam';

                                    return [
                                        $product->id => "{$product->name} / {$product->karat->name} / {$product->type->name} / {$mayamText} / {$weightText}",
                                    ];
                                });
                        })
                        ->columnSpan(9),
                    View::make('components.product-image')
                        ->label('')
                        ->viewData(fn($get) => [
                            'product' => Product::find($get('product_id')),
                        ])
                        ->columnSpan(3),
                ])
        ];
    }

    public static function inputPrice(): array
    {
        return [
            TextInput::make('price')
                ->label('Harga')
                ->prefix('Rp')
                ->inputMode('decimal')
                ->extraAttributes([
                    'x-data' => '{}',
                    'x-init' => <<<JS
                        \$el.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/[^\\d]/g, '')
                            value = new Intl.NumberFormat('id-ID').format(value)
                            e.target.value = value
                        })
                    JS,
                ])
                ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^\d]/', '', $state))
                ->required()
                ->minValue(1),
        ];
    }

    public static function inputQuantity(): array
    {
        return [
            TextInput::make('quantity')
                ->label('Jumlah')
                ->numeric()
                ->default(1)
                ->required(),
        ];
    }
}
