<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\UserResource\Pages;
use Filament\Tables\Actions\ActionGroup;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $breadcrumb = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->prefixIcon('heroicon-m-user')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->columnSpanFull(),

                TextInput::make('username')
                    ->label('Username')
                    ->prefixIcon('heroicon-m-at-symbol')
                    ->required()
                    ->minLength(4)
                    ->maxLength(15)
                    ->unique(table: 'users', column: 'username')
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->prefixIcon('heroicon-m-key')
                    ->password()
                    ->required()
                    ->revealable()
                    ->minLength(4)
                    ->maxLength(64)
                    ->dehydrated(true)
                    ->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
