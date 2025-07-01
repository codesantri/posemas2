<?php

namespace App\Http\Controllers;

use Mpdf\Mpdf;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function printInvoicePurchase($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->first();
        // dd($invoice);
        return view('prints.invoice-purchase', ['invoice' => $invoice]);
    }

    public function printInvoiceSale($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->first();
        return view('prints.invoice-sale', ['invoice' => $invoice]);
    }

    public function printInvoicePawning($inv)
    {

        $invoice = Transaction::where('invoice', $inv)->first();
        return view('prints.invoice-pawning', ['invoice' => $invoice]);
    }

    public function printorderservice($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->first();
        return view('prints.invoice-orderservice', ['invoice' => $invoice]);
    }

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

        return view('print.purchase', compact('data'));
    }

    public function printSale($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->firstOrFail();

        // Ambil purchase terkait
        $sale = $invoice->sale;

        // Ambil semua detail barang yang dibeli
        $details = $sale->saleDetails()->with('product')->get();

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
            'customer' => $sale->customer ? $sale->customer->name : '-',
            'address' => $sale->customer ? $sale->customer->address : '-',
            'cashier' => $invoice->user->name,
            'items' => $items,
            'service' => $invoice->service ?? 0,
            'discount' => $invoice->discount ?? 0,
            'total' => $total,
            'date' => $invoice->transaction_date,
        ];

        return view('print.purchase', compact('data'));
    }
}
