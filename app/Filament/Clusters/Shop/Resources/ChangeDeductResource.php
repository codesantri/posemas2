<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Tables;
use App\Models\Change;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\TableActions;
use App\Traits\Filament\Services\TableService;
use App\Traits\Filament\Services\ExchangeService;
use App\Traits\Filament\Services\ExchangeTableService;
use App\Filament\Clusters\Shop\Resources\ChangeDeductResource\Pages;

class ChangeDeductResource extends Resource
{
    protected static ?string $model = Change::class;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationLabel = 'Tukar Kurang';
    protected static ?string $label = "Tukar Kurang";
    protected static ?string $breadcrumb = 'Tukar Kurang';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form->schema(ExchangeService::getForm('deduct'));
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
            ->where('change_type', 'deduct')->latest();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangeDeducts::route('/'),
            'create' => Pages\CreateChangeDeduct::route('/create'),
            'view' => Pages\ViewChangeDeduct::route('/{record}'),
            'edit' => Pages\EditChangeDeduct::route('/{record}/edit'),
        ];
    }
}
