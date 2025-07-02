<?php

namespace App\Filament\Clusters\Shop\Pages;

use Filament\Pages\Page;
use App\Models\Transaction;
use App\Filament\Clusters\Shop;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\Grid;
use Filament\Pages\SubNavigationPosition;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Actions\Action;

class Invoice extends Page
{
    protected static ?string $cluster = Shop::class;
    protected static string $view = 'filament.pages.shop.invoice';
    protected static bool $shouldRegisterNavigation = false;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public ?string $customerName = null;
    public ?string $cashierName = null;
    public ?int $service = null;
    public ?int $discount = null;
    public ?int $cash = null;
    public ?int $change_return = null;
    public ?int $subtotal = null;
    public ?int $totalPayment = null;
    public $transaction;

    public ?string $typeName = "";
    public ?string $urlName = "";
    public ?string $urlPrint = "";

    public function mount()
    {
        $inv = request()->get('invoice');
        $this->loadPage($inv);
    }


    public function loadPage($inv)
    {
        if (blank($inv)) {
            return redirect()->to(url()->previous());
        }

        $this->transaction = Transaction::where('invoice', $inv)->first();

        if ($this->transaction) {
            $this->customerName = optional($this->transaction->customer)->name ?? 'Tidak Diketahui';
            $this->cashierName = optional($this->transaction->user)->name ?? 'Tidak Diketahui';
            $this->service = $this->transaction->service ?? 0;
            $this->discount = $this->transaction->discount ?? 0;
            $this->cash = $this->transaction->cash ?? 0;
            $this->change_return = $this->transaction->change ?? 0;
            $this->subtotal = $this->transaction->subtotal ?? 0;
            $this->totalPayment = $this->transaction->total ?? 0;
            $this->getCustomer();
            $this->getTotalPayment();
            $this->printUrl();
            $this->urlName();
        } else {
            // Jika invoice tidak ditemukan
            $this->transaction = (object)[
                'invoice' => $this->inv,
            ];
            $this->customerName = 'Invoice tidak ditemukan';
            $this->cashierName = '-';
            $this->service = 0;
            $this->discount = 0;
            $this->cash = 0;
            $this->change_return = 0;
            $this->subtotal = 0;
            $this->totalPayment = 0;
        }

        return null;
    }



    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make("Berhasil") // Changed title for better clarity
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                \Filament\Infolists\Components\ViewEntry::make('paymentDetails')
                                    ->view('filament.pages.shop.payment-details')
                                    ->viewData([
                                        'invoice' => $this->transaction->invoice ?? 'N/A',
                                        'customer' => $this->customerName,
                                        'cashier' => $this->cashierName,
                                        'change_return' => $this->change_return,
                                        'service' => $this->service,
                                        'discount' => $this->discount,
                                        'cash' => $this->cash,
                                        'subtotal' => $this->subtotal,
                                        'total' => $this->totalPayment,
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Actions::make([

                                    Action::make('back')
                                        ->label('Kembali')
                                        ->icon('heroicon-o-arrow-left')
                                        ->color('gray')
                                        ->url(url()->route($this->urlName)),

                                    Action::make('print')
                                        ->label('Cetak Nota')
                                        ->icon('heroicon-o-printer')
                                        ->color('primary')
                                        ->url(route($this->urlPrint, $this->transaction->invoice))
                                        ->openUrlInNewTab()

                                ])
                                    ->alignCenter()
                                    ->fullWidth(),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

    public function getCustomer()
    {
        if ($this->transaction->transaction_type === "change") {
            $this->customerName = $this->transaction->exchange->customer->name;
        } elseif ($this->transaction->transaction_type === "purchase") {
            $this->customerName = $this->transaction->purchase->customer->name;
        } elseif ($this->transaction->transaction_type === "sale") {
            $this->customerName = $this->transaction->sale->customer->name;
        }
    }

    public function getTotalPayment()
    {
        if ($this->transaction->transaction_type === "change") {
            $this->subtotal = $this->transaction->exchange->total_payment;
        } elseif ($this->transaction->transaction_type === "purchase") {
            $this->subtotal = $this->transaction->purchase->total_payment;
        } elseif ($this->transaction->transaction_type === "sale") {
            $this->subtotal = $this->transaction->sale->total_payment;
        }
    }


    public function urlName()
    {
        if ($this->transaction->transaction_type === "change") {
            if ($this->transaction->exchange->change_type === "add") {
                $this->urlName = "filament.admin.shop.resources.change-adds.index";
            } elseif ($this->transaction->exchange->change_type === "deduct") {
                $this->urlName = "filament.admin.shop.resources.change-deducts.index";
            } elseif ($this->transaction->exchange->change_type === "change_model") {
                $this->urlName = "filament.admin.shop.resources.change-models.index";
            }
        } elseif ($this->transaction->transaction_type === "purchase") {
            $this->urlName = "filament.admin.shop.resources.purchases.index";
        } elseif ($this->transaction->transaction_type === "sale") {
            $this->urlName = "filament.admin.shop.resources.sales.index";
        } elseif ($this->transaction->transaction_type === "entrust") {
            $this->urlName = "filament.admin.shop.resources.entrusts.index";
        }
    }

    public function getTitle(): string
    {
        $this->typeName = 'Tidak Diketahui';

        if ($this->transaction?->transaction_type === 'change') {
            $changeType = optional($this->transaction->exchange)->change_type;

            if ($changeType === 'add') {
                $this->typeName = 'Tukar Tambah';
            } elseif ($changeType === 'deduct') {
                $this->typeName = 'Tukar Kurang';
            } elseif ($changeType === 'change_model') {
                $this->typeName = 'Tukar Model';
            } else {
                $this->typeName = 'Tukar';
            }
        } elseif ($this->transaction?->transaction_type === 'purchase') {
            $this->typeName = 'Pembelian';
        } elseif ($this->transaction?->transaction_type === 'sale') {
            $this->typeName = 'Penjualan';
        }

        return 'Transaksi ' . $this->typeName;
    }

    public function printUrl()
    {
        if ($this->transaction->transaction_type === "change") {
            $this->urlPrint = "print.change";
        } elseif ($this->transaction->transaction_type === "purchase") {
            $this->urlPrint = "print.purchase";
        } elseif ($this->transaction->transaction_type === "sale") {
            $this->urlPrint = "print.sale";
        } elseif ($this->transaction->transaction_type === "entrust") {
            $this->urlPrint = "print.entrust";
        }
    }
}
