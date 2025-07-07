<?php

namespace App\Traits\Filament\Services;

use Filament\Tables\Columns\TextColumn;

trait TableService
{

    public static function getColumns()
    {
        return [
            TextColumn::make('customer.name')
                ->label('PELANGGAN')
                ->searchable()
                ->formatStateUsing(fn($state) => $state ?: 'Umum'),
            TextColumn::make('weight')
                ->label('BERAT')
                ->state(function ($record) {
                    // langsung sum dari details
                    return $record->transaction?->details->sum('total_weight') ?? 0;
                })
                ->formatStateUsing(function ($state) {
                    if ($state <= 0) {
                        return '0,00 my (0,00 gr)';
                    }

                    $weightInMayam = GeneralService::getMayam($state);
                    return number_format($weightInMayam) . ' my (' .
                        number_format($state) . ' gr)';
                }),
            TextColumn::make('total_payment')
                ->label('HARGA')
                ->state(fn($record) => $record->total_payment)
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            TextColumn::make('transaction.status')
                ->label('STATUS')
                ->badge()
                ->formatStateUsing(fn($state) => match ($state) {
                    'pending' => 'Menunggu',
                    'success' => 'Sukses',
                    'failed' => 'Gagal',
                    default => 'Tidak Diketahui',
                })
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
