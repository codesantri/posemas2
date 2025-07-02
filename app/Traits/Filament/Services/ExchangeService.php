<?php

namespace App\Traits\Filament\Services;

use App\Models\Change;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ChangeItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filament\Forms\FormInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait ExchangeService
{
    public static function getFormSchemaForResource(string $type): array
    {
        return [
            Section::make('Proses Pertukaran Emas')
                ->description('Silahkan isi data dibawah ini untuk melakukan pertukaran emas.')
                ->schema([
                    ...FormInput::selectCustomer('customer_id'),
                    Hidden::make('change_type')->default($type)->required()->dehydrated(true),
                    Grid::make(2)->schema([
                        Repeater::make('olds')
                            ->label('Produk Lama')
                            ->schema(self::productRepeaterSchema())
                            ->addActionLabel('Tambah Produk Lama') // Lebih deskriptif
                            ->minItems(1)
                            ->required(),

                        Repeater::make('news')
                            ->label('Produk Baru')
                            ->schema(self::productRepeaterSchema())
                            ->addActionLabel('Tambah Produk Baru') // Lebih deskriptif
                            ->minItems(1) // Minimal satu produk baru harus diisi
                            ->required(),
                    ]),
                ])
        ];
    }

    private static function productRepeaterSchema(): array
    {
        return [
            ...FormInput::selectProduct('produk_id'),
            Grid::make(2)->schema([
                ...FormInput::inputQuantity('quantity'),
                ...FormInput::inputPrice('price'),
            ])
        ];
    }


    public static function getCreateChange(array $data): array
    {
        Log::info('Data mentah dari form:', $data);
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
                    continue; // Lewati baris ini
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk lama '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name ?? "Tidak diketahui";

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

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
                // Jika product_id tidak ada atau null, anggap sebagai baris kosong
                if (!isset($item['product_id']) || is_null($item['product_id'])) {
                    continue; // Lewati baris ini
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    $productName = $item['product_name'] ?? "Tidak diketahui";
                    $errors[] = "Produk '$productName' tidak ditemukan.";
                    continue;
                }

                $productName = $product->name ?? "Tidak diketahui";

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

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
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Cek jika repeater kosong setelah filtering item yang tidak valid
        if (empty($olds)) {
            $errors[] = "Minimal satu produk lama harus diisi.";
        }

        if (empty($news)) {
            $errors[] = "Minimal satu produk baru harus diisi.";
        }


        // Validasi khusus berdasarkan change_type
        $changeType = $data['change_type'] ?? 'change_model'; // Ambil dari data form

        $totalPayment = 0;

        if ($totalOld > $totalNew) {
            $totalPayment = $totalOld - $totalNew;
        } elseif ($totalNew > $totalOld) {
            $totalPayment = $totalNew - $totalOld;
        } else {
            $totalPayment = 0;
        }

        // Validasi lebih lanjut berdasarkan change_type
        if ($changeType === 'add' && $totalOld >= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih kecil dari total nilai produk baru.";
        }

        if ($changeType === 'deduct' && $totalOld <= $totalNew) {
            $errors[] = "Total nilai produk lama harus lebih besar dari total nilai produk baru.";
        }

        if ($changeType === 'change_model' && $totalOld != $totalNew) {
            $errors[] = "Total nilai produk lama harus sama dengan total nilai produk baru.";
        }

        // Jika ada error, kirim notifikasi dan lempar ValidationException
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

        $result = [
            'customer_id' => $data['customer_id'], // Pastikan customer_id diteruskan
            'change_type' => $changeType,
            'total_payment' => $totalPayment,
            'status' => 'pending',
            'olds' => $olds,
            'news' => $news,
        ];
        return $result;
    }

    public static function getRecordCreation(array $data): Change
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'transaction_type' => 'change',
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);
            // Buat Change dulu supaya invoice terisi
            $change = new Change();
            $change->transaction_id = $transaction->id;
            $change->customer_id = $data['customer_id'];
            $change->change_type = $data['change_type'];
            $change->total_payment = $data['total_payment'];
            $change->status = $data['status'] ?? 'pending';
            $change->save();

            // Simpan item lama
            foreach ($data['olds'] ?? [] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
                    'item_type' => 'old',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Simpan item baru
            foreach ($data['news'] ?? [] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
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
    public static function prepareFormData(Model $record): array
    {
        $olds = [];
        $news = [];

        foreach ($record->changeItems as $item) {
            $itemData = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Produk tidak ditemukan',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
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

    /**
     * Memproses data form untuk update dan melakukan validasi/perhitungan.
     * Mirip dengan getCreateChange, tetapi menerima objek Change yang ada.
     *
     * @param Change $change
     * @param array $data
     * @return array
     */
    public static function getUpdateChange(Change $change, array $data): array
    {
        Log::info('Data mentah dari form (UPDATE):', $data);

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
                $productName = $product->name ?? ($item['product_name'] ?? "Tidak diketahui");

                if (!$product) {
                    $errors[] = "Produk lama '$productName' tidak ditemukan.";
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

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
                $productName = $product->name ?? ($item['product_name'] ?? "Tidak diketahui");

                if (!$product) {
                    $errors[] = "Produk baru '$productName' tidak ditemukan.";
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 0);
                $price = isset($item['price']) ? (int) preg_replace('/[^\d]/', '', $item['price']) : 0;

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
            'status' => $data['status'] ?? 'pending',
            'olds' => $olds,
            'news' => $news,
        ];

        Log::info('Data setelah validasi dan perhitungan (UPDATE):', $result);
        return $result;
    }

    /**
     * Memperbarui record Change dan ChangeItems terkait.
     *
     * @param Model $record
     * @param array $data
     * @return Model
     */
    public static function getRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            $change = $record;

            $change->customer_id = $data['customer_id'];
            $change->change_type = $data['change_type'];
            $change->total_payment = $data['total_payment'];
            $change->status = $data['status'] ?? 'pending';
            $change->save();

            $change->changeItems()->delete();

            foreach ($data['olds'] as $item) {
                ChangeItem::create([
                    'change_id' => $change->id,
                    'product_id' => $item['product_id'],
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
