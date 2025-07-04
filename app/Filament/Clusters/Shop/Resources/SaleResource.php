<?php

namespace App\Filament\Clusters\Shop\Resources;

use App\Models\Sale;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use App\Traits\Filament\Action\TableActions;
use App\Traits\Filament\Services\SaleService;
use App\Traits\Filament\Services\TableService;
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
            ->schema(SaleService::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableService::getColumns())
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
