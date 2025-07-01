<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Tables;
use App\Models\Customer;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\SubNavigationPosition;
use App\Traits\Filament\Action\SelectAction;
use App\Traits\Filament\Action\TableActions;
use App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = "Pembelian";
    protected static ?string $breadcrumb = 'Pembelian';
    protected static ?string $label = 'Pembelian';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('customer_id')
                        ->label('Nama Pelanggan')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->options(fn() => Customer::orderBy('id', 'desc')->get()->mapWithKeys(fn($customer) => [
                            $customer->id => "{$customer->name} -  {$customer->phone} - {$customer->address}"
                        ]))
                        ->columnSpanFull(),
                    Repeater::make('products')
                        ->label('Data Produk')
                        ->schema([
                            ...SelectAction::getSelectProduct('produk_id'),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->required(),
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
                                ])

                        ])->addActionLabel('Tambah'),
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
                    ->label('Pembayaran')
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
            ])
            ->filters([
                //
            ])
            ->actions([
                TableActions::getGroup()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                self::deletePurchase($record);
                            }
                        }),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }


    private static function deletePurchase($record)
    {
        // Hapus transaksi jika ada
        if ($record->transaction) {
            $record->transaction->delete();
        }
    }
}
