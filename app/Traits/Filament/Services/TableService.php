<?php

namespace App\Traits\Filament\Services;

use Filament\Tables\Columns\TextColumn;

trait TableService
{

    public static function getColumns()
    {
        return [
            TextColumn::make('transaction.invoice')
                ->label('INVOICE')
                ->searchable(),
            TextColumn::make('customer.name')
                ->label('PELANGGAN')
                ->searchable(),
            TextColumn::make('total_payment')
                ->label('HARGA')
                ->state(fn($record) => $record->total_payment)
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            TextColumn::make('status')
                ->label('STATUS')
                ->badge()
                ->color(fn($state) => [
                    'pending' => 'warning',
                    'success' => 'success',
                    'failed' => 'danger',
                ][$state] ?? 'gray'),
            TextColumn::make('created_at')
                ->label('TANGGAL')
                ->date('d M Y')
                ->searchable(),
        ];
    }
}
