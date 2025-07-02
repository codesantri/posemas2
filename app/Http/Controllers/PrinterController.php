<?php

namespace App\Http\Controllers;

use Mpdf\Mpdf;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Traits\Filament\Services\GeneralService;

class PrinterController extends Controller
{

    public function printPurchase($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->firstOrFail();

        // Ambil purchase terkait
        $purchase = $invoice->purchase;

        // Ambil semua detail barang yang dibeli
        $details = $purchase->purchaseDetails()->with('product')->get();

        // Siapkan array items
        // $items = [];
        $total = $invoice->total;
        $items = collect();
        foreach ($details as $detail) {
            $items->push([
                'qty' => $detail->quantity,
                'product_name' => $detail->product->name,
                'subtotal' => $detail->subtotal,
            ]);
        }
        $data = [
            'customer' => $purchase->customer ? $purchase->customer->name : '-',
            'address' => $purchase->customer ? $purchase->customer->address : '-',
            'cashier' => $invoice->user->name,
            'items' => $items,
            'service' => $invoice->service ?? 0,
            'discount' => $invoice->discount ?? 0,
            'total' => $total,
            'date' => $invoice->transaction_date,
        ];

        return view('print.invoice-purchase', compact('data'));
    }

    // public function printSale($inv)
    // {
    //     $invoice = Transaction::where('invoice', $inv)->firstOrFail();

    //     $change = $invoice->exchange;

    //     $details = $change->changeItems()->with('product')->get();


    //     $total = $invoice->total;




    //     $items = collect();
    //     foreach ($details as $detail) {
    //         $items->push([
    //             'qty' => $detail->quantity,
    //             'product_name' => $detail->product->name,
    //             'subtotal' => $detail->subtotal,
    //         ]);
    //     }
    //     $data = [
    //         'customer' => $change->customer ? $change->customer->name : '-',
    //         'address' => $change->customer ? $change->customer->address : '-',
    //         'cashier' => $invoice->user->name,
    //         'items' => $items,
    //         'service' => $invoice->service ?? 0,
    //         'discount' => $invoice->discount ?? 0,
    //         'total' => $total,
    //         'date' => $invoice->transaction_date,
    //     ];

    //     return view('print.invoice-purchase', compact('data'));
    // }

    public function exchange($inv)
    {
        // Retrieve the transaction
        $invoice = Transaction::where('invoice', $inv)->firstOrFail();

        // Ensure there is an exchange record
        $change = $invoice->exchange;
        if (!$change) {
            abort(404, 'Exchange data not found.');
        }

        // Retrieve all change items with products
        $details = $change->changeItems()->with('product')->get();

        // Group items by item_type
        $olds = $details
            ->where('item_type', 'old')
            ->map(function ($detail) {
                $getMayam = GeneralService::getMayam($detail->product->weight) * $detail->quantity;
                $getGram = $detail->product->weight * $detail->quantity;
                $getWeight = '  (' . $getMayam . ' my' . ' / ' .  $getGram . ' gr' . ')';
                $rate = $detail->product->karat->rate . '%';
                return [
                    'qty'          => $detail->quantity,
                    'product_name' => $detail->product->name ?? '-',
                    'subtotal'     => $detail->subtotal,
                    'weight' => $getWeight,
                    'rate' => $rate ?? '999.9%',
                    'image' => $detail->product->image,
                ];
            });

        $news = $details
            ->where('item_type', 'new')
            ->map(function ($detail) {
                $getMayam = GeneralService::getMayam($detail->product->weight) * $detail->quantity;
                $getGram = $detail->product->weight * $detail->quantity;
                $getWeight = '  (' . $getMayam . ' my' . ' / ' .  $getGram . ' gr' . ')';
                $rate =  $detail->product->karat->rate . '%';
                return [
                    'qty'          => $detail->quantity,
                    'product_name' => $detail->product->name ?? '-',
                    'subtotal'     => $detail->subtotal,
                    'weight' => $getWeight,
                    'rate' => $rate ?? '999.9%',
                    'image' => $detail->product->image,
                ];
            });

        $data = [
            'customer' => optional($change->customer)->name ?? '-',
            'address'  => optional($change->customer)->address ?? '-',
            'cashier'  => optional($invoice->user)->name ?? '-',
            'olds'     => $olds,
            'news'     => $news,
            'service'  => $invoice->service ?? 0,
            'discount' => $invoice->discount ?? 0,
            'total'    => $invoice->total ?? 0,
            'date'     => $invoice->transaction_date,
        ];

        // Return the invoice-change view
        return view('print.invoice-change', compact('data'));
    }
}
