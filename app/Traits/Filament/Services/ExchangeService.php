<?php

namespace App\Traits\Filament\Services;

use App\Models\Change;
use App\Models\Product;
use App\Models\ChangeItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filament\Forms\FormInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait ExchangeService
{
    public static function getForm(string $type): array
    {
        return [
            Section::make('Proses Pertukaran Emas')
                ->description('Silahkan isi data dibawah ini untuk melakukan pertukaran emas.')
                ->schema([
                    ...FormService::selectCustomer(),
                    Hidden::make('change_type')->default($type)->required()->dehydrated(true),
                    Grid::make(2)->schema([
                        Repeater::make('olds')
                            ->label('Produk Lama')
                            ->schema([
                                ...FormService::selectProduct(),
                                Grid::make(2)->schema([
                                    ...FormService::inputQuantity(),
                                    ...FormService::inputPrice(),
                                ])
                            ])
                            ->addActionLabel('Tambah Produk Lama') // Lebih deskriptif
                            ->minItems(1)
                            ->required(),

                        Repeater::make('news')
                            ->label('Produk Baru')
                            ->schema([
                                ...FormService::selectProduct(),
                                Grid::make(2)->schema([
                                    ...FormService::inputQuantity(),
                                    ...FormService::inputPrice(),
                                ])
                            ])
                            ->addActionLabel('Tambah Produk Baru') // Lebih deskriptif
                            ->minItems(1) // Minimal satu produk baru harus diisi
                            ->required(),
                    ]),
                ])
        ];
    }

    public static function getTable()
    {
        return [
            TextColumn::make('customer.name')
                ->label('PELANGGAN')
                ->searchable(),
            TextColumn::make('weight_old')
                ->label('BERAT LAMA')
                ->state(function ($record) {
                    return $record->changeItems
                        ->where('item_type', 'old')
                        ->map(fn($item) => $item->total_weight)
                        ->sum();
                })

                ->formatStateUsing(
                    fn($state) =>
                    number_format(GeneralService::getMayam($state), 2, ',', '.') . ' my (' . number_format($state, 2, ',', '.') . ' gr)'
                ),

            TextColumn::make('weight_new')
                ->label('BERAT BARU')
                ->state(function ($record) {
                    return $record->changeItems
                        ->where('item_type', 'new')
                        ->map(fn($item) => $item->total_weight)
                        ->sum();
                })
                ->formatStateUsing(
                    fn($state) =>
                    number_format(GeneralService::getMayam($state), 2, ',', '.') . ' my (' . number_format($state, 2, ',', '.') . ' gr)'
                ),

            TextColumn::make('total_old')
                ->label('LAMA')
                ->icon('heroicon-o-arrow-down')
                ->state(function ($record) {
                    return $record->changeItems
                        ->where('item_type', 'old')
                        ->sum('subtotal');
                })
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color('danger'), // Merah

            TextColumn::make('total_new')
                ->label('BARU')
                ->icon('heroicon-o-arrow-up')
                ->state(function ($record) {
                    return $record->changeItems
                        ->where('item_type', 'new')
                        ->sum('subtotal');
                })
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color('success'), // Hijau
            TextColumn::make('total_payment')
                ->label('HARGA')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar')
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

    public static function getCreate(array $data): array
    {
        $totalOld = 0;
        $totalNew = 0;
        $olds = [];
        $news = [];
        $errors = [];

        if (!isset($data['olds']) || !is_array($data['olds'])) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        } else {
            foreach ($data['olds'] as $item) {
                if (!isset($item['product_id']) || is_null($item['product_id'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk lama '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name ?? "Tidak diketahui";
                $karatName = $product->karat->name ?? "Tidak diketahui";
                $weight = $product->weight ?? 0;
                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;
                $totalWeight = $weight * $qty;

                if ($qty <= 0) {
                    $errors[] = "Quantity untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalOld += $subtotal;

                $olds[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'karat_name' => $karatName,
                    'total_weight' => $totalWeight,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (!isset($data['news']) || !is_array($data['news'])) {
            $errors[] = "Minimal satu produk baru harus diisi.";
        } else {
            foreach ($data['news'] as $item) {
                if (!isset($item['product_id']) || is_null($item['product_id'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name ?? "Tidak diketahui";
                $karatName = $product->karat->name ?? "Tidak diketahui";
                $weight = $product->weight ?? 0;
                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;
                $totalWeight = $weight * $qty;

                if ($qty <= 0) {
                    $errors[] = "Jumlah untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalNew += $subtotal;

                $news[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'karat_name' => $karatName,
                    'total_weight' => $totalWeight,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        if (empty($olds)) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        }

        if (empty($news)) {
            $errors[] = "Minimal satu produk baru harus diisi.";
        }

        $changeType = $data['change_type'];
        $totalPayment = 0;

        if ($totalOld > $totalNew) {
            $totalPayment = $totalOld - $totalNew;
        } elseif ($totalNew > $totalOld) {
            $totalPayment = $totalNew - $totalOld;
        } else {
            $totalPayment = 0;
        }

        if ($changeType === 'add' && $totalOld >= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih kecil dari total nilai produk baru.";
        }

        if ($changeType === 'deduct' && $totalOld <= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih besar dari total nilai produk baru.";
        }

        if ($changeType === 'change_model' && $totalOld != $totalNew) {
            $errors[] = "Total nilai produk lama harus sama dengan total nilai produk baru.";
        }

        if (!empty($errors)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->danger()
                ->body(implode("\n", $errors))
                ->persistent()
                ->send();
            throw ValidationException::withMessages([
                'general' => $errors
            ]);
        }

        return [
            'customer_id' => $data['customer_id'],
            'change_type' => $changeType,
            'total_payment' => $totalPayment,
            'status' => 'pending',
            'olds' => $olds,
            'news' => $news,
        ];
    }

    public static function handleCreate(array $data): Change
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'transaction_type' => 'change',
            ]);

            $change = new Change();
            $change->transaction_id = $transaction->id;
            $change->customer_id = $data['customer_id'];
            $change->change_type = $data['change_type'];
            $change->total_payment = $data['total_payment'];
            $change->save();

            foreach ($data['olds'] ?? [] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'karat_name' => $item['karat_name'],
                    'total_weight' => $item['total_weight'],
                    'item_type' => 'old',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            foreach ($data['news'] ?? [] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'karat_name' => $item['karat_name'],
                    'total_weight' => $item['total_weight'],
                    'item_type' => 'new',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            return $change;
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title("Gagal menyimpan data.")
                ->body("Terjadi kesalahan: " . $e->getMessage())
                ->danger()
                ->send();
            Log::error('Error saat menyimpan transaksi perubahan:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    // Update
    public static function getEditing(Model $record): array
    {
        $olds = [];
        $news = [];

        foreach ($record->changeItems as $item) {
            $itemData = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];

            if ($item->item_type === 'old') {
                $olds[] = $itemData;
            } else {
                $news[] = $itemData;
            }
        }

        return [
            ...$record->attributesToArray(),
            'olds' => $olds,
            'news' => $news,
        ];
    }

    public static function getUpdate(Change $change, array $data): array
    {
        $totalOld = 0;
        $totalNew = 0;
        $olds = [];
        $news = [];
        $errors = [];

        // Validasi & proses produk lama
        if (!isset($data['olds']) || !is_array($data['olds'])) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        } else {
            foreach ($data['olds'] as $item) {
                if (empty($item['product_id'])) continue;

                $product = Product::find($item['product_id']);
                $productName = $product->name ?? "Tidak diketahui";
                $karatName = $product->karat->name ?? "Tidak diketahui";
                $weight = $product->weight ?? 0;

                if (!$product) {
                    $errors[] = "Produk lama '$productName' tidak ditemukan.";
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;
                $totalWeight = $weight * $qty;

                if ($qty <= 0) {
                    $errors[] = "Quantity untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk lama '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalOld += $subtotal;

                $olds[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'karat_name' => $karatName,
                    'total_weight' => $totalWeight,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Validasi & proses produk baru
        if (!isset($data['news']) || !is_array($data['news'])) {
            $errors[] = "Minimal satu produk baru harus diisi.";
        } else {
            foreach ($data['news'] as $item) {
                if (empty($item['product_id'])) continue;

                $product = Product::find($item['product_id']);
                $productName = $product->name ?? "Tidak diketahui";
                $karatName = $product->karat->name ?? "Tidak diketahui";
                $weight = $product->weight ?? 0;

                if (!$product) {
                    $errors[] = "Produk baru '$productName' tidak ditemukan.";
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;
                $totalWeight = $weight * $qty;

                if ($qty <= 0) {
                    $errors[] = "Jumlah untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                if ($price <= 0) {
                    $errors[] = "Harga untuk produk '$productName' harus lebih dari 0.";
                    continue;
                }

                $subtotal = $qty * $price;
                $totalNew += $subtotal;

                $news[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'karat_name' => $karatName,
                    'total_weight' => $totalWeight,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Cek repeater kosong
        if (empty($olds)) $errors[] = "Minimal satu produk lama harus diisi.";
        if (empty($news)) $errors[] = "Minimal satu produk baru harus diisi.";

        // Validasi berdasarkan change_type
        $changeType = $data['change_type'] ?? 'change_model';
        $totalPayment = 0;

        if ($totalOld > $totalNew) {
            $totalPayment = $totalOld - $totalNew;
        } elseif ($totalNew > $totalOld) {
            $totalPayment = $totalNew - $totalOld;
        }

        if ($changeType === 'add' && $totalOld >= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih kecil dari total nilai produk baru.";
        }

        if ($changeType === 'deduct' && $totalOld <= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih besar dari total nilai produk baru.";
        }

        if ($changeType === 'change_model' && $totalOld !== $totalNew) {
            $errors[] = "Total nilai produk lama harus sama dengan total nilai produk baru.";
        }

        // Tangani error
        if (!empty($errors)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->danger()
                ->body(implode("\n", $errors))
                ->persistent()
                ->send();

            throw ValidationException::withMessages(['general' => $errors]);
        }

        $result = [
            'customer_id' => $data['customer_id'],
            'change_type' => $changeType,
            'total_payment' => $totalPayment,
            'olds' => $olds,
            'news' => $news,
        ];

        Log::info('Data setelah validasi dan perhitungan (UPDATE):', $result);
        return $result;
    }

    public static function getUpdating(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            $change = $record;

            $change->customer_id = $data['customer_id'];
            $change->change_type = $data['change_type'];
            $change->total_payment = $data['total_payment'];
            $change->save();

            $change->changeItems()->delete();

            foreach ($data['olds'] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'karat_name' => $item['karat_name'],
                    'total_weight' => $item['total_weight'],
                    'item_type' => 'old',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            foreach ($data['news'] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'karat_name' => $item['karat_name'],
                    'total_weight' => $item['total_weight'],
                    'item_type' => 'new',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            return $change;
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title("Gagal memperbarui data.")
                ->body("Terjadi kesalahan: " . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Error saat memperbarui transaksi perubahan:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
