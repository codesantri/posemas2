<?php

namespace App\Traits\Filament\Action;

use App\Models\Product;
use App\Models\Customer;

use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;

trait SelectAction
{
    public static function getSelectProduct(string $type): array
    {
        return [
            Select::make('product_id')
                ->label('Nama Produk')
                ->searchable()
                ->required()
                ->preload()
                ->options(fn() => Product::with(['karat', 'category', 'type'])
                    ->latest()->get()->mapWithKeys(fn($product) => [
                        $product->id => "{$product->name} / {$product->karat->karat}-{$product->karat->rate}% / {$product->category->name} / {$product->type->name}",
                    ]))
                ->createOptionForm([
                    Placeholder::make('InfoProduk')
                        ->label('Daftar Produk')
                        ->content(new \Illuminate\Support\HtmlString(view('components.product-list')->render()))
                        ->columnSpanFull(),
                ])
                ->createOptionAction(
                    fn($action) => $action
                        ->label('Pilih Produk')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalHeading('')
                ),

        ];
    }

    public static function getSelectCustomer(string $type): array
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
}
