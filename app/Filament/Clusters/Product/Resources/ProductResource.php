<?php

namespace App\Filament\Clusters\Product\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Product;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use App\Models\Product as ProductModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Services\GeneralService;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Clusters\Product\Resources\ProductResource\Pages;

class ProductResource extends Resource
{
    protected static ?string $model = ProductModel::class;
    protected static ?string $cluster = Product::class;
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk'; // Singular
    protected static ?string $pluralModelLabel = 'Produk'; // Plural
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),


                Grid::make(4)->schema([
                    Select::make('category_id')
                        ->label('Kategori')
                        ->relationship('category', 'name')
                        ->required()
                        ->rules(['exists:categories,id']),
                    Select::make('type_id')
                        ->label('Jenis')
                        ->relationship('type', 'name')
                        ->required()
                        ->rules(['exists:types,id']),

                    Select::make('karat_id')
                        ->label('Karat-Kadar')
                        ->options(function () {
                            return \App\Models\Karat::all()->mapWithKeys(function ($karat) {
                                return [$karat->id => $karat->karat . ' - ' . $karat->rate . '%'];
                            })->toArray();
                        })
                        ->required()
                        ->rules(['exists:karats,id']),

                    TextInput::make('weight')
                        ->label('Berat (gram)')
                        ->type('number')
                        ->step(0.01)
                        ->default(0.01)
                        ->minValue(0.01)
                        ->suffix('Gram')
                        ->required()
                        ->rules(['numeric', 'min:0.01']),
                ]),

                FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('products')
                    ->maxSize(2048)
                    ->imagePreviewHeight('200')
                    ->columnSpanFull()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                ProductModel::query()->with(['karat', 'category', 'type']) // <== ini WAJIB!
            )
            ->columns([
                TextColumn::make('image')
                    ->label('Nama Produk')
                    ->formatStateUsing(function ($state, $record) {
                        $imageUrl = asset('storage/' . $record->image);

                        return <<<HTML
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{$imageUrl}" alt="Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <span>{$record->name}</span>
                                </div>
                        HTML;
                    })
                    ->html(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('karat.karat')
                    ->label('Karat-Kadar')
                    ->formatStateUsing(function ($state, $record) {
                        return optional($record->karat)->karat . ' - ' . optional($record->karat)->rate . '%';
                    })
                    ->searchable(),
                TextColumn::make('weight_mayam')
                    ->label('Berat Mayam')
                    ->getStateUsing(fn($record) => $record->weight)
                    ->formatStateUsing(function ($state) {
                        $mayam = GeneralService::getMayam($state);
                        return "{$mayam} m";
                    })
                    ->color('success'), // hijau

                TextColumn::make('weight_gram')
                    ->label('Berat Gram')
                    ->getStateUsing(fn($record) => $record->weight)
                    ->formatStateUsing(function ($state) {
                        return "{$state} g";
                    })
                    ->color('primary'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
        ];
    }
}
