<?php

namespace App\Traits\Filament\Action;

use App\Models\Type;
use App\Models\Karat;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Traits\Filament\Services\PaymentService;

trait HeaderAction
{
    public static function getAddProductAction(): Action
    {
        return Action::make('addproduct')
            ->label('Produk')
            ->icon('heroicon-o-plus-circle')
            ->modalHeading('Tambah Produk Baru')
            ->modalSubmitActionLabel('Simpan')
            ->form([
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Grid::make(4)->schema([
                    Select::make('category_id')
                        ->label('Kategori')
                        ->options(Category::pluck('name', 'id'))
                        ->required(),
                    Select::make('type_id')
                        ->label('Jenis')
                        ->options(Type::pluck('name', 'id'))
                        ->required(),

                    Select::make('karat_id')
                        ->label('Karat-Kadar')
                        ->options(Karat::all()->mapWithKeys(fn($karat) => [
                            $karat->id => "{$karat->karat} - {$karat->rate}%",
                        ])->toArray())
                        ->required(),

                    TextInput::make('weight')
                        ->label('Berat (gram)')
                        ->type('number')
                        ->step(0.01)
                        ->default(0.01)
                        ->minValue(0.01)
                        ->suffix('Gram')
                        ->required(),
                ]),

                FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('products')
                    ->maxSize(2048)
                    ->imagePreviewHeight('200')
                    ->columnSpanFull()
                    ->required(),
            ])
            ->action(function (array $data) {
                $existing = Product::where([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'type_id' => $data['type_id'],
                    'karat_id' => $data['karat_id'],
                    'weight' => $data['weight'],
                ])->first();

                if ($existing) {
                    Notification::make()
                        ->title('Produk Sudah Ada')
                        ->body("Produk {$existing->name} telah terdaftar.")
                        ->warning()
                        ->send();
                    return;
                }

                $product = Product::create($data);

                Notification::make()
                    ->title('Produk Baru Ditambahkan')
                    ->body("{$product->name} berhasil disimpan.")
                    ->success()
                    ->send();
            });
    }

    public static function getAddCustomerAction(): Action
    {
        return Action::make('addcustomer')
            ->label('Pelanggan')
            ->color('info')
            ->icon('heroicon-o-user-plus')
            ->modalHeading('Tambah Pelanggan Baru')
            ->modalSubmitActionLabel('Simpan')
            ->form([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->prefixIcon('heroicon-m-user')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->rule('regex:/^[a-zA-Z\s\.\']+$/'),

                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->prefixIcon('heroicon-m-phone')
                    ->tel()
                    ->required()
                    ->minLength(10)
                    ->maxLength(15)
                    ->unique('customers', 'phone'),

                TextInput::make('address')
                    ->label('Alamat')
                    ->prefixIcon('heroicon-m-map-pin')
                    ->required()
                    ->minLength(5)
                    ->maxLength(255)
                    ->rule('regex:/^[a-zA-Z0-9\s,.\-\/]+$/'),
            ])
            ->action(function (array $data) {
                $existing = Customer::where('phone', $data['phone'])->first();

                if ($existing) {
                    Notification::make()
                        ->title('Pelanggan Sudah Terdaftar')
                        ->body("Nomor telepon {$existing->phone} sudah digunakan oleh {$existing->name}.")
                        ->warning()
                        ->send();
                    return;
                }

                $customer = Customer::create($data);

                Notification::make()
                    ->title('Pelanggan Baru Ditambahkan')
                    ->body("{$customer->name} berhasil disimpan.")
                    ->success()
                    ->send();
            });
    }

    public static function getGoPayment($invoice): Action
    {
        $transaction = Transaction::where('invoice', $invoice)->first();

        // Cek jika transaksi tidak ada
        if (!$transaction) {
            return Action::make('gopayment')
                ->disabled()
                ->label('Transaksi tidak ditemukan');
        }

        return Action::make('gopayment')
            ->color('success')
            ->icon('heroicon-o-credit-card')
            ->label('Proses Transaksi')
            ->visible(function () use ($transaction) {
                if ($transaction->transaction_type === "sale") {
                    return optional($transaction->sale)->status === 'pending';
                }
                if ($transaction->transaction_type === "purchase") {
                    return optional($transaction->purchase)->status === 'pending';
                }
                if ($transaction->transaction_type === "change") {
                    return optional($transaction->change)->status === 'pending';
                }
                if ($transaction->transaction_type === "entrust") {
                    return optional($transaction->entrust)->status === 'pending'
                        && optional($transaction->entrust)->status_entrust === 'active';
                }
                return false;
            })
            ->action(fn() => PaymentService::gotoPayment($invoice));
    }


    public static function getActivate($id): Action
    {
        return Action::make('activate')
            ->label('Konfirmasi Titip Emas')
            ->icon('heroicon-m-paper-airplane')
            ->color('success')
            ->visible(fn($record) => $record->status_entrust === 'unactive')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi')
            ->modalDescription('Apakah kamu yakin mengkonfirmasi penitipan emas?')
            ->modalButton('Ya, Lanjutkan')
            ->action(function ($record) {
                $record->update([
                    'status_entrust' => 'active',
                ]);
                Notification::make()
                    ->title('Konfirmasi titip emas berhasil')
                    ->success()
                    ->send();
            });
    }

    public static function getMenu(): Action
    {
        return Action::make('gomenu')
            ->color('gray')
            ->icon('heroicon-o-squares-2x2')
            ->label('Menu')
            ->url(route('filament.admin.pages.dashboard'));
    }
}
