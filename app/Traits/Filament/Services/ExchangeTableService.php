<?php

namespace App\Traits\Filament\Services;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Filament\Action\TableActions;
use App\Traits\Filament\Services\PaymentService;

trait ExchangeTableService
{
    public static function getTableSchemaForResource(string $context = 'add'): array
    {
        return [
            Tables\Columns\TextColumn::make('invoice')
                ->label('Invoice')
                ->searchable(),

            Tables\Columns\TextColumn::make('total_old')
                ->label('Produk Lama')
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->state(
                    fn($record) => $record->changeItems
                        ->where('item_type', 'old')
                        ->sum(fn($item) => $item->price * $item->quantity)
                ),

            Tables\Columns\TextColumn::make('total_new')
                ->label('Produk Baru')
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->state(
                    fn($record) => $record->changeItems
                        ->where('item_type', 'new')
                        ->sum(fn($item) => $item->price * $item->quantity)
                ),

            Tables\Columns\TextColumn::make('total_payment')
                ->label('Harga')
                ->state(fn($record) => $record->total_payment)
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color('info'),

            Tables\Columns\TextColumn::make('transaction.total')
                ->label('Total')
                ->state(function ($record) {
                    return $record->total_payment; // atau logic sesuai kebutuhan
                })
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color(function ($record) {
                    return match ($record->change_type) {
                        'add' => 'success',
                        'deduct' => 'danger',
                        'change_model' => 'warning',
                        default => 'gray',
                    };
                })
                ->icon(function ($record) {
                    return match ($record->change_type) {
                        'add' => 'heroicon-m-arrow-up-circle',
                        'deduct' => 'heroicon-m-arrow-down-circle',
                        'change_model' => 'heroicon-m-arrow-path',
                        default => null,
                    };
                }),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn($state) => [
                    'pending' => 'warning',
                    'success' => 'success',
                    'failed' => 'danger',
                ][$state] ?? 'gray')
                ->label('Status'),

            Tables\Columns\TextColumn::make('transaction.transaction_date')
                ->label('Tanggal')
                ->date('d M Y')
                ->searchable()
                ->visible(fn($record) => $record?->status !== 'pending'),
        ];
    }

    public static function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('transaction_date_range')
                ->label('Rentang Tanggal')
                ->form([
                    Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if ($data['from'] && $data['until']) {
                        return 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y') .
                            ' sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                    }
                    if ($data['from']) {
                        return 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                    }
                    if ($data['until']) {
                        return 'Sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                    }
                    return null;
                })
                ->query(function (Builder $query, array $data) {
                    return $query->whereHas('transaction', function ($q) use ($data) {
                        $q->where('transaction_type', 'change')
                            ->when($data['from'], fn($qq) => $qq->whereDate('transaction_date', '>=', $data['from']))
                            ->when($data['until'], fn($qq) => $qq->whereDate('transaction_date', '<=', $data['until']));
                    });
                }),

            Tables\Filters\SelectFilter::make('month')
                ->label('Bulan')
                ->options([
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember',
                ])
                ->query(fn($query, array $data) => $query->whereHas('transaction', function ($q) use ($data) {
                    $q->where('transaction_type', 'change')
                        ->when($data['value'], fn($qq) => $qq->whereMonth('transaction_date', $data['value']));
                })),

            Tables\Filters\SelectFilter::make('year')
                ->label('Tahun')
                ->options(fn() => collect(range(now()->year, 2020))->mapWithKeys(fn($year) => [$year => $year]))
                ->query(fn($query, array $data) => $query->whereHas('transaction', function ($q) use ($data) {
                    $q->where('transaction_type', 'change')
                        ->when($data['value'], fn($qq) => $qq->whereYear('transaction_date', $data['value']));
                })),
        ];
    }



    public static function getTableActions(): array
    {
        return [
            TableActions::getGroup()
        ];
    }
}
