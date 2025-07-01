<?php

namespace App\Filament\Clusters\Product\Resources;

use App\Models\Type;
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
use App\Filament\Clusters\Product\Resources\TypeResource\Pages;
use App\Filament\Clusters\Product\Resources\TypeResource\Pages\ListTypes;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;
    protected static ?string $cluster = Product::class;


    protected static ?string $navigationLabel = 'Jenis';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $modelLabel = 'Jenis'; // Singular
    protected static ?string $pluralModelLabel = 'Jenis'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Jenis')
                    ->required()->unique()->columnSpanFull(),
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
            'index' => ListTypes::route('/'),
        ];
    }
}
