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
use App\Filament\Clusters\Shop\Resources\ChangeModelResource\Pages;

class ChangeModelResource extends Resource
{
    protected static ?string $model = Change::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Tukar Model';
    protected static ?string $label = "Tukar Model";
    protected static ?string $breadcrumb = 'Tukar Model';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form->schema(ExchangeService::getForm('change_model'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ExchangeService::getTable())
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
            ->where('change_type', 'change_model')->latest();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangeModels::route('/'),
            'create' => Pages\CreateChangeModel::route('/create'),
            'view' => Pages\ViewChangeModel::route('/{record}'),
            'edit' => Pages\EditChangeModel::route('/{record}/edit'),
        ];
    }
}
