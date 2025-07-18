<?php

namespace App\Filament\Clusters\Product\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use App\Models\Product as ProductModel;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Traits\Filament\Services\GeneralService;
use App\Filament\Clusters\Product\Resources\ProductResource\Pages\ListProducts;

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
                        ->label('Kadar Emas')
                        ->relationship('karat', 'name')
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
                    ->resize(50)
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
                    ->label('PRODUK')
                    ->formatStateUsing(function ($state, $record) {
                        // Fallback ke logo.png jika tidak ada image
                        $imagePath = $record->image
                            ? 'storage/' . $record->image
                            : 'images/logo.png'; // pastikan path benar, misal public/images/logo.png

                        $imageUrl = asset($imagePath);

                                        return <<<HTML
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{$imageUrl}" alt="Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <span>{$record->name}</span>
                            </div>
                        HTML;
                    })
                    ->html(),


                TextColumn::make('category.name')
                    ->label('KATEGORI')
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label('JENIS')
                    ->searchable(),
                TextColumn::make('karat.name')
                    ->label('KADAR EMAS')
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            'index' => ListProducts::route('/'),
        ];
    }
}
