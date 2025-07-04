<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Tables;
use App\Models\Entrust;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\TableActions;
use App\Traits\Filament\Services\EntrustService;
use App\Filament\Clusters\Shop\Resources\EntrustResource\Pages;

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
            ->schema(EntrustService::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.invoice')
                    ->label('INVOICE')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('PELANGGAN')
                    ->searchable(),
                TextColumn::make('total_payment')
                    ->label('HARGA')
                    ->state(fn($record) => $record->total_payment)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => [
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    ][$state] ?? 'gray')
                    ->label('STATUS'),

                TextColumn::make('status_entrust')
                    ->label('STATUS TITIP')
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
                TextColumn::make('created_at')
                    ->label('TANGGAL')
                    ->date('d M Y')
                    ->searchable(),
            ])
            ->filters(TableActions::getTableFilters())
            ->actions(TableActions::getGroup())
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
            'index' => Pages\ListEntrusts::route('/'),
            'create' => Pages\CreateEntrust::route('/create'),
            'view' => Pages\ViewEntrust::route('/{record}'),
            'edit' => Pages\EditEntrust::route('/{record}/edit'),
        ];
    }
}
