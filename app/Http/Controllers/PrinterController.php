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

    public function print($inv)
    {
        // Retrieve the invoice or fail gracefully
        $invoice = Transaction::where('invoice', $inv)->firstOrFail();

        $getData = null;
        $relationMethod = null;

        // Determine transaction type and related model
        if ($invoice->transaction_type === "sale") {
            $getData = $invoice->sale;
            $relationMethod = "saleDetails";
        } elseif ($invoice->transaction_type === "purchase") {
            $getData = $invoice->purchase;
            $relationMethod = "purchaseDetails";
        } elseif ($invoice->transaction_type === "entrust") {
            $getData = $invoice->entrust;
            $relationMethod = "entrustDetails";
        } else {
            abort(404, "Unknown transaction type.");
        }

        // Call the relation method dynamically and eager load 'product'
        $details = $getData->{$relationMethod}()->with('product.karat')->get();

        // Build the items array with poetic precision
        $items = $details->map(function ($detail) {
            $getMayam = GeneralService::getMayam($detail->product->weight) * $detail->quantity;
            $getGram = $detail->product->weight * $detail->quantity;
            $getWeight = ' (' . $getMayam . ' my / ' .  $getGram . ' gr)';
            $rate = optional($detail->product->karat)->rate . '%';

            return [
                'qty'          => $detail->quantity,
                'product_name' => $detail->product->name ?? '-',
                'subtotal'     => $detail->subtotal,
                'weight'       => $getWeight,
                'rate'         => $rate ?? '999.9%',
                'image'        => $detail->product->image,
            ];
        });

        // Assemble the data set to feed the view
        $data = [
            'customer' => optional($getData->customer)->name ?? '-',
            'address'  => optional($getData->customer)->address ?? '-',
            'cashier'  => optional($invoice->user)->name ?? '-',
            'items'    => $items,
            'service'  => $invoice->service ?? 0,
            'discount' => $invoice->discount ?? 0,
            'total'    => $invoice->total ?? 0,
            'date'     => $invoice->transaction_date,
        ];

        return view('print.invoice', compact('data'));
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

    
        return view('print.invoice-change', compact('data'));
    }
}
