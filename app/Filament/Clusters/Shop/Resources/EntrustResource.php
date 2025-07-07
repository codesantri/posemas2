<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Tables;
use App\Models\Entrust;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\TableActions;
use App\Traits\Filament\Services\FormService;
use App\Traits\Filament\Services\TableService;
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
            ->schema(FormService::getForm());
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
            'index' => Pages\ListEntrusts::route('/'),
            'create' => Pages\CreateEntrust::route('/create'),
            'view' => Pages\ViewEntrust::route('/{record}'),
            'edit' => Pages\EditEntrust::route('/{record}/edit'),
        ];
    }
}
