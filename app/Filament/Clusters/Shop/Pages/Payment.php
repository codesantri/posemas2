<?php

namespace App\Filament\Clusters\Shop\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Transaction;
use App\Filament\Clusters\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\Placeholder;
use App\Traits\Filament\Services\PaymentService;
use Filament\Forms\Components\Actions\Action as FormAction;

class Payment extends Page
{
    protected static ?string $cluster = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.shop.payment';
    protected static bool $shouldRegisterNavigation = false;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public Transaction $transaction;
    public array $data = [];
    public string $transactionType;

    // Input values
    public float|int $iservice = 0;
    public float|int $idiscount = 0;
    public float|int $icash = 0;

    // Processed values
    public float|int $service = 0;
    public float|int $discount = 0;
    public float|int $cash = 0;

    public float|int $subtotal = 0;
    public float|int $change_return = 0;

    public float|int $totalPayment = 0;

    public string $customerName = '';
    public string $cashierName = '';

    public string $typeChange = '';

    public function mount(): void
    {
        $invoice = request()->query('invoice');
        $this->loadPage($invoice);
    }

    public function loadPage($invoice)
    {
        $this->transaction = Transaction::where('invoice', $invoice)->firstOrFail();
        $alreadyProcessed = false;
        switch ($this->transaction->transaction_type) {
            case 'purchase':
                if ($this->transaction->purchase && $this->transaction->purchase->status === 'success') {
                    $alreadyProcessed = true;
                }
                break;

            case 'sale':
                if ($this->transaction->sale && $this->transaction->sale->status === 'success') {
                    $alreadyProcessed = true;
                }
                break;
            case 'entrust':
                if ($this->transaction->entrust && $this->transaction->entrust->status === 'success') {
                    $alreadyProcessed = true;
                }
                break;

            case 'change':
                if ($this->transaction->exchange && $this->transaction->exchange->status === 'success') {
                    $alreadyProcessed = true;
                }
                break;
        }

        if ($alreadyProcessed) {
            Notification::make()
                ->title('Transaksi sudah selesai')
                ->body('Transaksi ini sudah diproses sebelumnya dan tidak dapat dibayar lagi.')
                ->danger()
                ->send();

            redirect()->back()->send();
            exit; // Pastikan berhenti
        }

        // Kalau belum success, lanjut load form
        $this->data['payment_method'] = $this->transaction->payment_method ?? 'cash';
        $this->transactionType = $this->transaction->transaction_type;
        $this->getTypeChange();
        $this->getTotalPayment();
        $this->getData();
        $this->form->fill();
    }


    public function getTypeChange(): void
    {
        if ($this->transactionType === 'change') {
            $this->typeChange = $this->transaction->exchange->change_type;
        }
    }

    public function getTotalPayment(): void
    {
        if ($this->transactionType === 'change') {
            $this->subtotal = $this->transaction->exchange->total_payment ?? 0;
        } elseif ($this->transactionType === 'sale') {
            $this->subtotal = $this->transaction->sale->total_payment ?? 0;
        } elseif ($this->transactionType === 'purchase') {
            $this->subtotal = $this->transaction->purchase->total_payment ?? 0;
        } elseif ($this->transactionType === 'entrust') {
            $this->subtotal = $this->transaction->entrust->total_payment ?? 0;
        }
        $this->recalculateAll();
    }

    protected function parseInput($value): float|int
    {
        return (float) preg_replace('/[^\d]/', '', $value);
    }

    protected function recalculateAll(): void
    {
        $this->totalPayment = $this->subtotal + $this->service - $this->discount;
        $this->change_return = max(0, $this->cash - $this->totalPayment);
    }

    public function updatedIservice($value): void
    {
        $this->service = $this->parseInput($value);
        $this->recalculateAll();
    }

    public function updatedIdiscount($value): void
    {
        $this->discount = $this->parseInput($value);
        $this->recalculateAll();
    }

    public function updatedIcash($value): void
    {
        $this->cash = $this->parseInput($value);
        $this->recalculateAll();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Split::make([
                Section::make([
                    $this->getCounterPlaceholder(),
                    $this->getPaymentMethodRadio(),
                    $this->getSubmitAction(),
                ]),
                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(fn() => new HtmlString(
                            view('filament.pages.shop.payment-details', [
                                'invoice' => $this->transaction->invoice,
                                'customer' => $this->customerName,
                                'cashier' => $this->cashierName,
                                'change_return' => $this->change_return,
                                'service' => $this->service,
                                'discount' => $this->discount,
                                'cash' => $this->cash,
                                'subtotal' => $this->subtotal,
                                'total' => $this->totalPayment,
                            ])->render()
                        )),
                ]),
            ]),
        ];
    }

    protected function getCounterPlaceholder(): Placeholder
    {
        return Placeholder::make('counter')
            ->label('')
            ->content(fn() => new HtmlString(
                view('filament.pages.shop.counter', [
                    'type' => $this->typeChange,
                    'method' => $this->data['payment_method'] ?? 'cash',
                ])->render()
            ));
    }

    protected function getPaymentMethodRadio(): Radio
    {
        return Radio::make('payment_method')
            ->label('Metode Pembayaran')
            ->options([
                'cash' => 'Tunai',
                'online' => 'Transfer',
            ])
            ->default($this->data['payment_method'] ?? 'cash')
            ->reactive()
            ->afterStateUpdated(fn($state) => $this->updatePaymentMethod($state))
            ->inline();
    }

    public function updatePaymentMethod(string $method): void
    {
        try {
            $this->transaction->update(['payment_method' => $method]);
            $this->data['payment_method'] = $method;
            $this->icash = 0;
            $this->updatedIcash(0);

            Notification::make()
                ->title('Metode Pembayaran Berhasil Diubah')
                ->success()
                ->duration(3000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengubah metode pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getSubmitAction(): Actions
    {
        return Actions::make([
            FormAction::make('submit')
                ->label(fn() => 'Bayar Rp. ' . number_format($this->totalPayment, 0, ',', '.'))
                ->icon('heroicon-m-credit-card')
                ->disabled(
                    fn() =>
                    $this->typeChange === 'add'
                        && ($this->data['payment_method'] ?? 'cash') === 'cash'
                        && $this->cash < $this->totalPayment
                )
                ->button()
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalSubheading('Apakah Anda yakin ingin melanjutkan pembayaran?')
                ->modalButton('Konfirmasi')
                ->action(fn() => $this->processPayment())
        ])->fullWidth();
    }

    protected function getData(): void
    {
        if ($this->transactionType === 'change') {
            $this->customerName = $this->transaction->exchange->customer->name ?? '';
            $this->cashierName = $this->transaction->user->name ?? '';
        } elseif ($this->transactionType === 'sale') {
            $this->customerName = $this->transaction->sale->customer->name ?? '';
            $this->cashierName = $this->transaction->sale->user->name ?? '';
        } elseif ($this->transactionType === 'purchase') {
            $this->customerName = $this->transaction->purchase->customer->name ?? '';
            $this->cashierName = $this->transaction->purchase->user->name ?? '';
        }
    }

    public function getTitle(): string
    {
        return PaymentService::getLabel($this->transactionType, $this->typeChange);
    }

    public function processPayment(): void
    {
        DB::beginTransaction();

        try {
            $cashValue = 0;
            if ($this->data['payment_method'] === 'cash') {
                $cashValue = $this->cash > 0 ? $this->cash : $this->totalPayment;
            } else {
                $cashValue = 0;
            }

            if ($this->data['payment_method'] === 'cash') {
                $cashValue = $this->cash > 0 ? $this->cash : $this->totalPayment;
            } else {
                $cashValue = 0;
            }


            PaymentService::getPaymentLoad([
                'invoice' => $this->transaction->invoice,
                'type' => $this->transactionType,
                'cash' => $cashValue,
                'discount' => $this->discount ?? 0,
                'change' => $this->change_return ?? 0,
                'service' => $this->service ?? 0,
                'total' => $this->totalPayment ?? 0,
            ]);

            DB::commit();

            $this->redirectBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->handlePaymentError($e);
        }
    }

    protected function handlePaymentError(\Throwable $e): void
    {
        Log::error('Gagal memproses Pembelian', [
            'invoice' => $this->transaction->invoice,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Notification::make()
            ->title('Terjadi kesalahan saat memproses Pembayaran')
            ->body($e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }


    public function redirectBack()
    {
        return redirect()->back();
    }
}
