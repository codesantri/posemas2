<?php

namespace App\Traits\Filament\Action;

use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\Shop\Pages\Invoice;
use App\Traits\Filament\Services\PaymentService;

trait TableActions
{
    public static function getGroup(): ActionGroup
    {
        return ActionGroup::make([
            ViewAction::make(),

            EditAction::make()
                ->hidden(fn($record) => $record->transaction->status === 'success'),

            DeleteAction::make(),

            Action::make('payment_process')
                ->label('Pembayaran')
                ->icon('heroicon-m-credit-card')
                ->color('success')
                ->visible(fn($record) => $record->transaction->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Proses Pembayaran')
                ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk transaksi ini?')
                ->modalButton('Ya, Proses Pembayaran')
                ->action(fn($record) => PaymentService::gotoPayment($record->transaction->invoice)),

            Action::make('invoice')
                ->label('Invoice')
                ->icon('heroicon-m-document-text')
                ->color('info')
                ->visible(fn($record) => $record->transaction->status === 'success')
                ->url(fn($record) => Invoice::getUrl(['invoice' => $record->transaction->invoice])),
        ]);
    }

    public static function getTableFilters(): array
    {
        return [
            // RENTANG TANGGAL FILTER
            Filter::make('created_at_range')
                ->label('Rentang Tanggal Dibuat')
                ->form([
                    DatePicker::make('from')->label('Dari Tanggal'),
                    DatePicker::make('until')->label('Sampai Tanggal'),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!empty($data['from']) && !empty($data['until'])) {
                        return 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y') .
                            ' sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                    }
                    if (!empty($data['from'])) {
                        return 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                    }
                    if (!empty($data['until'])) {
                        return 'Sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                    }
                    return null;
                })
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                }),

            // BULAN FILTER
            SelectFilter::make('month')
                ->label('Bulan Dibuat')
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
                ->query(function ($query, array $data) {
                    return $query->when($data['value'], fn($q) => $q->whereMonth('created_at', $data['value']));
                }),

            // TAHUN FILTER
            SelectFilter::make('year')
                ->label('Tahun Dibuat')
                ->options(fn() => collect(range(now()->year, 2020))->mapWithKeys(fn($year) => [$year => $year]))
                ->query(function ($query, array $data) {
                    return $query->when($data['value'], fn($q) => $q->whereYear('created_at', $data['value']));
                }),
        ];
    }
}
