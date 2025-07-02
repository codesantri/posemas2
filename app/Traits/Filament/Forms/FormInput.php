<?php

namespace App\Traits\Filament\Forms;

use App\Models\Product;
use App\Models\Customer;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

trait FormInput
{


    public static function selectCustomer(string $type): array
    {
        return [
            Select::make('customer_id')
                ->label('Nama Pelanggan')
                ->searchable()
                ->required()
                ->preload()
                ->options(fn() => Customer::orderBy('id', 'desc')->get()->mapWithKeys(fn($customer) => [
                    $customer->id => "{$customer->name} -  {$customer->phone} - {$customer->address}"
                ]))
                ->columnSpanFull(),
        ];
    }


    public static function selectProduct(string $type): array
    {
        return [
            Grid::make(12)->schema([
                Select::make('product_id')
                    ->label('Nama Produk')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->live() // Add this to make it update on every change
                    ->options(fn() => Product::with(['karat', 'category', 'type'])
                        ->latest()
                        ->get()
                        ->mapWithKeys(fn($product) => [
                            $product->id => "{$product->name} / {$product->karat->karat}-{$product->karat->rate}% / {$product->category->name} / {$product->type->name}",
                        ]))
                    ->columnSpan(8),

                View::make('components.product-image')
                    ->label('')
                    ->viewData(fn($get) => [
                        'product' => Product::find($get('product_id')),
                    ])
                    ->columnSpan(4),
            ])
        ];
    }


    public static function inputPrice(string $type)
    {
        return [
            TextInput::make("price")
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

    public static function inputQuantity(string $type)
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
