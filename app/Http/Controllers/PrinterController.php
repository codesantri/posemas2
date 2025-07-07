<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Traits\Filament\Services\GeneralService;

class PrinterController extends Controller
{

    public function print($inv)
    {
        // Retrieve the transaction or fail gracefully
        $transaction = Transaction::where('invoice', $inv)->firstOrFail();

        $data = [
            'invoice' => '',
            'customer' => '-',
            'address'  => '-',
            'cashier'  => optional($transaction->user)->name ?? '-',
            'service'  => $transaction->service ?? 0,
            'discount' => $transaction->discount ?? 0,
            'total'    => $transaction->total ?? 0,
            'date'     => $transaction->transaction_date,
            'olds'     => collect(), // Initialize as collection
            'news'     => collect(), // Initialize as collection
            'items'    => collect(), // Initialize as collection
            'total_items' => 0,
        ];

        if ($transaction->transaction_type === 'change') {
            $change = $transaction->exchange;

            $details = $change->changeItems()->with('product.karat')->get();

            $data['olds'] = $details
                ->where('item_type', 'old')
                ->map(fn($detail) => $this->mapChangeItem($detail))
                ->values();

            $data['news'] = $details
                ->where('item_type', 'new')
                ->map(fn($detail) => $this->mapChangeItem($detail))
                ->values();

            // Hitung total items
            $data['total_items'] = $data['olds']->count() + $data['news']->count();

            $data['customer'] = optional($change->customer)->name ?? '-';
            $data['address']  = optional($change->customer)->address ?? '-';
        } else {
            // Resolve model & relation
            switch ($transaction->transaction_type) {
                case 'sale':
                    $model = $transaction->sale;
                    $relation = 'saleDetails';
                    break;
                case 'purchase':
                    $model = $transaction->purchase;
                    $relation = 'purchaseDetails';
                    break;
                case 'entrust':
                    $model = $transaction->entrust;
                    $relation = 'entrustDetails';
                    break;
                default:
                    abort(404, "Unknown transaction type.");
            }

            $details = $model->{$relation}()->with('product.karat')->get();

            $data['items'] = $details
                ->map(fn($detail) => $this->mapRegularItem($detail))
                ->values();

            // Hitung total items
            $data['total_items'] = $data['items']->count();

            $data['customer'] = optional($model->customer)->name ?? '-';
            $data['address']  = optional($model->customer)->address ?? '-';
        }
        $images = collect()
            ->merge($data['items']->pluck('image'))
            ->merge($data['olds']->pluck('image'))
            ->merge($data['news']->pluck('image'))
            ->unique()
            ->values()
            ->toArray();
        $rows = $data['total_items'] + $data['service'] + $data['discount'];
        return view('print.invoice', compact('data', 'images', 'rows'));
    }

    /**
     * Map an item from a "change" transaction
     */
    protected function mapChangeItem($detail)
    {
        $mayam = GeneralService::getMayam($detail->total_weight);
        $gram  = $detail->total_weight;

        return [
            'qty'          => $detail->quantity,
            'product_name' => $detail->product_name ?? '-',
            'subtotal'     => $detail->subtotal,
            'weight'       => sprintf(' (%s my / %s gr)', $mayam, $gram),
            'rate'         => optional($detail->product->karat)->name ?? '999.9%',
            'image'        => $detail->product->image,
        ];
    }

    /**
     * Map an item from sale/purchase/entrust
     */
    protected function mapRegularItem($detail)
    {
        $mayam = GeneralService::getMayam($detail->total_weight);
        $gram  = $detail->total_weight;

        return [
            'qty'          => $detail->quantity,
            'product_name' => $detail->product->name ?? '-',
            'subtotal'     => $detail->subtotal,
            'weight'       => sprintf(' (%s my / %s gr)', $mayam, $gram),
            'rate'         => optional($detail->product->karat)->name ?? '999.9%',
            'image'        => $detail->product->image,
        ];
    }
}
