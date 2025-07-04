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
use App\Filament\Clusters\Shop\Resources\ChangeAddResource\Pages;

class ChangeAddResource extends Resource
{
    protected static ?string $model = Change::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Tukar Tambah';
    protected static ?string $label = "Tukar Tambah";
    protected static ?string $breadcrumb = 'Tukar Tambah';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form->schema(ExchangeService::getForm('add'));
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
            ->where('change_type', 'add')->latest();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangeAdds::route('/'),
            'create' => Pages\CreateChangeAdd::route('/create'),
            'edit' => Pages\EditChangeAdd::route('/{record}/edit'),
            'view' => Pages\ViewChangeAdd::route('/{record}'),
        ];
    }
}
