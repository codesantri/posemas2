<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class TransactionHistories extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
            )
            ->columns([
                TextColumn::make('invoice')->label('Invoice')->searchable(),

                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y H:i'),

                TextColumn::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->badge()
                // ->color(fn($record) => self::getTypeColor($record))
                // ->formatStateUsing(fn($state, $record) => self::getTypeLabel($record))
                ,

                TextColumn::make('total')
                    ->label('Total Transaksi')
                    ->money('IDR', true),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'online' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Online',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'success' => 'Berhasil',
                        'expired' => 'Kadaluarsa',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),
            ]);
    }
}
