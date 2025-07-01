<?php

namespace App\Filament\Clusters\Shop\Resources;

use App\Models\Sale;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use App\Traits\Filament\Action\SelectAction;
use App\Traits\Filament\Action\TableActions;
use App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $cluster = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $breadcrumb = 'Penjualan';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Daftar Belanja')
                    ->schema([
                        ...SelectAction::getSelectCustomer('customer_id'),
                        Repeater::make('items')
                            ->label('Item Belanja')
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->schema([
                                Grid::make(12)->schema([
                                    \Filament\Forms\Components\View::make('components.product-image')
                                        ->label('') // Kosongkan label biar clean
                                        ->columnSpan(2)
                                        ->viewData(fn($get) => [
                                            'product' => \App\Models\Product::find(
                                                $get('product_id')
                                            ),
                                        ])->columnSpan(4),

                                    Hidden::make('product_id')
                                        ->label('Jumlah')
                                        ->required()
                                        ->columnSpan(1),


                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->default(1)
                                        ->columnSpan(1),

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
                                        ->minValue(1)
                                        ->columnSpan(5),
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('delete')
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

                                                        // Redirect ke halaman sebelumnya atau refresh
                                                        redirect()->back();
                                                        // Atau jika mau tetap di halaman, bisa return tanpa redirect
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.invoice')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_payment')
                    ->label('Harga')
                    ->state(fn($record) => $record->total_payment)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('transaction.total')
                    ->label('Total Pemabayaran')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => [
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    ][$state] ?? 'gray')
                    ->label('Status'),
                TextColumn::make('transaction.payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'online' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Online',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('transaction.transaction_date')
                    ->label('Waktu Transaksi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                TableActions::getGroup()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->latest(); 
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
