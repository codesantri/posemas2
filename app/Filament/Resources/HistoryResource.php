<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\Shop\Pages\Invoice;
use App\Filament\Resources\HistoryResource\Pages;

class HistoryResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = "Transaksi";
    protected static ?string $title = "Riwayat transaksi";
    protected static ?string $breadcrumb = 'Riwayat transaksi';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('TANGGAL/JAM')
                    ->dateTime('d M Y H:i')
                    ->searchable(),

                TextColumn::make('transaction_type')
                    ->label('TRANSAKSI')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === 'change') {
                            return match ($record->exchange->change_type ?? null) {
                                'add' => 'Tukar Tambah',
                                'deduct' => 'Tukar Kurang',
                                'change_model' => 'Tukar Model',
                                default => 'Tukar',
                            };
                        }

                        return match ($state) {
                            'sale' => 'Penjualan',
                            'purchase' => 'Pembelian',
                            'entrust' => 'Titip Emas',
                            default => ucfirst($state),
                        };
                    })
                    ->color(function ($state, $record) {
                        if ($state === 'change') {
                            return match ($record->exchange->change_type ?? null) {
                                'add' => 'success',       // hijau
                                'deduct' => 'danger',     // merah
                                'change_model' => 'warning', // kuning
                                default => 'info',
                            };
                        }

                        return match ($state) {
                            'sale' => 'success',
                            'purchase' => 'danger',
                            'entrust' => 'info',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('price')
                    ->label('HARGA')
                    ->state(function ($record) {
                        if ($record->transaction_type === 'sale') {
                            return $record->sale->total_payment ?? 0;
                        } elseif ($record->transaction_type === 'purchase') {
                            return $record->purchase->total_payment ?? 0;
                        } elseif ($record->transaction_type === 'change') {
                            return $record->exchange->total_payment ?? 0;
                        } elseif ($record->transaction_type === 'entrust') {
                            return $record->entrust->total_payment ?? 0;
                        }
                        return $record->price ?? 0;
                    })->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('service')
                    ->label('JASA')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))->color('primary'),
                TextColumn::make('discount')
                    ->label('DISKON')
                    ->formatStateUsing(fn($state) => '-Rp ' . number_format($state, 0, ',', '.'))->color('danger'),
                TextColumn::make('cash')
                    ->label('TUNAI')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))->color('info'),

                TextColumn::make('total')
                    ->label('TOTAL')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))->color('success'),

                TextColumn::make('payment_method')
                    ->label('PEMBAYARAN')
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
            ])
            ->actions([
                ActionGroup::make([
                    DeleteAction::make()->modalHeading('Hapus Transaksi'),
                    Action::make('invoice')
                        ->label('Invoice')
                        ->icon('heroicon-m-document-text')
                        ->color('info')
                        ->url(fn($record) => Invoice::getUrl(['invoice' => $record->invoice])),
                ])
            ])->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ]),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                SelectFilter::make('transaction_date')
                    ->label('Tanggal')
                    ->options(function () {
                        return Transaction::query()
                            ->selectRaw('DATE(transaction_date) as date')
                            ->whereNotNull('transaction_date') // Hanya untuk dropdown (null gak bisa jadi option)
                            ->distinct()
                            ->orderBy('date', 'desc')
                            ->pluck('date', 'date')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereDate('transaction_date', $data['value']);
                        }
                    }),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereMonth('transaction_date', $data['value']);
                        }
                    }),
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $startYear = 2022;
                        $currentYear = Carbon::now()->year;
                        return array_combine(range($startYear, $currentYear), range($startYear, $currentYear));
                    })
                    ->default(null)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('transaction_date', $data['value']);
                        }
                    }),



            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['sale', 'purchase', 'exchange', 'entrust'])
            ->where(function (Builder $query) {
                $query
                    ->where(function (Builder $query) {
                        $query->where('transaction_type', 'change')
                            ->whereHas('exchange', function (Builder $q) {
                                $q->where('status', 'success');
                            });
                    })
                    ->orWhere(function (Builder $query) {
                        $query->where('transaction_type', 'sale')
                            ->whereHas('sale', function (Builder $q) {
                                $q->where('status', 'success');
                            });
                    })
                    ->orWhere(function (Builder $query) {
                        $query->where('transaction_type', 'purchase')
                            ->whereHas('purchase', function (Builder $q) {
                                $q->where('status', 'success');
                            });
                    })
                    ->orWhere(function (Builder $query) {
                        $query->where('transaction_type', 'entrust')
                            ->whereHas('entrust', function (Builder $q) {
                                $q->where('status', 'success');
                            });
                    });
            })
            ->latest();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistories::route('/'),
        ];
    }
}
