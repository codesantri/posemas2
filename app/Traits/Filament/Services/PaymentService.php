<?php

namespace App\Traits\Filament\Services;

use App\Models\Transaction;
use Filament\Notifications\Notification;
use App\Filament\Clusters\Shop\Pages\Invoice;
use App\Filament\Clusters\Shop\Pages\Payment;

trait PaymentService
{
    public static function gotoPayment($invoice)
    {
        if ($invoice) {
            return redirect(Payment::getUrl(['invoice' => $invoice]));
        } else {
            return redirect()->back();
        }
    }

    public static function getLabel(string $type, ?string $changeType = null): string
    {
        return match ($type) {
            'change' => match ($changeType) {
                'add' => 'Transaksi Tukar Tambah',
                'deduct' => 'Transaksi Tukar Kurang',
                'change_model' => 'Transaksi Tukar Model',
                default => 'Tukar Tambah',
            },
            'sale' => 'Transaksi Penjualan',
            'purchase' => 'Transaksi Pembelian',
            'entrust' => 'Transaksi Titip Emas',
            default => 'Transaksi',
        };
    }




    public static function getPaymentLoad(array $data)
    {
        $transaction = Transaction::where('invoice', $data['invoice'])->firstOrFail();


        if ($transaction->status === 'success') {
            return redirect()
                ->back()
                ->with('error', 'Transaksi ini sudah selesai dan tidak bisa diproses lagi.');
        }

        if ($transaction->payment_method === 'cash') {
            if ($data['cash'] < $data['total'] || $data['cash'] <= 0) {
                Notification::make()
                    ->title('Uang Tunai kurang dari total pembayaran')
                    ->success()
                    ->send();
                return;
            }
        }

        $transaction->update([
            'cash' => $data['cash'],
            'discount' => $data['discount'],
            'change' => $data['change'],
            'service' => $data['service'],
            'total' => $data['total'],
            'status' => 'success',
            'transaction_date' => now(),
        ]);


        self::getNotification($transaction->transaction_type, $transaction->id);

        return redirect(Invoice::getUrl(['invoice' => $transaction->invoice]));
    }


    public static function getNotification(string $transactionType, int $id): void
    {
        if ($transactionType === "change") {
            $change = Transaction::where('id', $id)->first();
            $changeType = $change->exchange?->change_type ?? 'default';
            $changeLabel = match ($changeType) {
                'add' => 'Transaksi tukar tambah berhasil',
                'deduct' => 'Transaksi tukar kurang berhasil',
                'change_model' => 'Transaksi tukar model berhasil',
                default => 'Transaksi tukar berhasil',
            };

            Notification::make()
                ->title($changeLabel)
                ->success()
                ->send();
            return;
        }

        switch ($transactionType) {
            case 'purchase':
                Notification::make()
                    ->title('Transaksi pembelian emas berhasil')
                    ->success()
                    ->send();
                break;

            case 'sale':
                Notification::make()
                    ->title('Transaksi penjualan emas berhasil')
                    ->success()
                    ->send();
                break;

            case 'entrust':
                Notification::make()
                    ->title('Transaksi titip emas berhasil')
                    ->success()
                    ->send();
                break;

            default:
                Notification::make()
                    ->title('Transaksi berhasil')
                    ->success()
                    ->send();
                break;
        }
    }
}
