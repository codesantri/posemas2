<?php

namespace App\Filament\Clusters\Product\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Karat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Product\Resources\KaratResource\Pages;
use App\Filament\Clusters\Product\Resources\KaratResource\RelationManagers;

class KaratResource extends Resource
{
    protected static ?string $model = Karat::class;
    protected static ?string $cluster = Product::class;


    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Karat';

    protected static ?string $modelLabel = 'Karat'; // Singular
    protected static ?string $pluralModelLabel = 'Karat'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('karat')
                    ->label('Karat')
                    ->maxLength(10)
                    ->required(),

                TextInput::make('rate')
                    ->label('Kadar (0.00% - 100.00%)')
                    ->type('number') // ini kunci utama!
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0.01)
                    ->suffix('%')
                    ->required(),



                TextInput::make('buy_price')
                    ->label('Harga Beli')
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-init' => <<<JS
                        \$el.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/[^\\d]/g, '')
                            value = new Intl.NumberFormat('id-ID').format(value)
                            e.target.value = value
                        })
                    JS,
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^\d]/', '', $state))
                    ->required(),

                TextInput::make('sell_price')
                    ->label('Harga Jual')
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-init' => <<<JS
                        \$el.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/[^\\d]/g, '')
                            value = new Intl.NumberFormat('id-ID').format(value)
                            e.target.value = value
                        })
                    JS,
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^\d]/', '', $state))
                    ->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('karat')
                    ->label('Karat')
                    ->searchable(),
                TextColumn::make('rate')
                    ->label('Kadar')
                    ->suffix('%'),
                TextColumn::make('buy_price')
                    ->prefix('Rp ')
                    ->label('Harga Beli')
                    ->numeric(),
                TextColumn::make('sell_price')
                    ->prefix('Rp ')
                    ->label('Harga Jual')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKarats::route('/'),
        ];
    }
}
