<?php

namespace App\Filament\Clusters\Product\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Karat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Product;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Product\Resources\KaratResource\Pages;
use App\Filament\Clusters\Product\Resources\KaratResource\Pages\ListKarats;
use App\Filament\Clusters\Product\Resources\KaratResource\RelationManagers;

class KaratResource extends Resource
{
    protected static ?string $model = Karat::class;
    protected static ?string $cluster = Product::class;


    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Kadar Emas';

    protected static ?string $modelLabel = 'Kadar Emas'; // Singular
    protected static ?string $pluralModelLabel = 'Kadar Emas'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Karat')
                    ->maxLength(10)
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListKarats::route('/'),
        ];
    }
}
