<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Tables;
use App\Models\Entrust;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use App\Traits\Filament\Forms\FormInput;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\SelectAction;
use App\Traits\Filament\Action\TableActions;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;
use App\Filament\Clusters\Shop\Resources\EntrustResource\RelationManagers;

class EntrustResource extends Resource
{
    protected static ?string $model = Entrust::class;
    protected static ?string $cluster = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = "Titip Emas";
    protected static ?string $breadcrumb = 'Titip Emas';
    protected static ?string $label = 'Titip Emas';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.invoice')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                TextColumn::make('total_payment')
                    ->label('Harga')
                    ->state(fn($record) => $record->total_payment)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('transaction.total')
                    ->label('Total Pemabayaran')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => [
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    ][$state] ?? 'gray')
                    ->label('Status'),

                TextColumn::make('status_entrust')
                    ->label('Status Titip')
                    ->badge()
                    ->color(fn($state) => [
                        'unactive' => 'info',
                        'active' => 'warning',
                        'end' => 'success',
                    ][$state] ?? 'gray')
                    ->icon(fn($state) => [
                        'unactive' => 'heroicon-o-clock',
                        'active' => 'heroicon-o-bolt',
                        'end' => 'heroicon-o-check-circle',
                    ][$state] ?? 'heroicon-o-question-mark-circle')
                    ->formatStateUsing(fn($state) => [
                        'unactive' => 'Menunggu Konfirmasi',
                        'active' => 'Aktif',
                        'end' => 'Berhasil',
                    ][$state] ?? 'Tidak Diketahui'),

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
            'index' => Pages\ListEntrusts::route('/'),
            'create' => Pages\CreateEntrust::route('/create'),
            'view' => Pages\ViewEntrust::route('/{record}'),
            'edit' => Pages\EditEntrust::route('/{record}/edit'),
        ];
    }
}
