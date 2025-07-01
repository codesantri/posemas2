<?php

namespace App\Traits\Filament\Action;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use App\Filament\Clusters\Shop\Pages\Invoice;
use App\Traits\Filament\Services\PaymentService;
use App\Filament\Clusters\Shop\Pages\Payment; // pastikan use ini kalau mau direct Payment

trait TableActions
{
    public static function getGroup(): ActionGroup
    {
        return ActionGroup::make([
            ViewAction::make(),

            EditAction::make()
                ->hidden(fn($record) => $record->status === 'success'),

            DeleteAction::make(),

            Action::make('payment_process')
                ->label('Pembayaran')
                ->icon('heroicon-m-credit-card')
                ->color('success')
                ->visible(fn($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Proses Pembayaran')
                ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk transaksi ini?')
                ->modalButton('Ya, Proses Pembayaran')
                ->action(fn($record) => PaymentService::gotoPayment($record->transaction->invoice)),

            Action::make('invoice')
                ->label('Invoice')
                ->icon('heroicon-m-document-text')
                ->color('info')
                ->visible(fn($record) => $record->status === 'success')
                ->url(fn($record) => Invoice::getUrl(['invoice' => $record->transaction->invoice])),
        ]);
    }
}
