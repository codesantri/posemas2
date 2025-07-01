<x-print
:name="$invoice->sale->customer->name"
:address="$invoice->sale->customer->address"
:details="$invoice->sale->saleDetails"
:total="$invoice->total_amount"
:cs="$invoice->sale->user->name"
:date="$invoice->transaction_date"
>
    @foreach ($invoice->sale->saleDetails as $item)
    <tr>
        <td class="text-center border-0 py-1">{{ $item->quantity }}</td>
        <td style="width: 45%" class="border-0 py-1"><span class="ms-3">{{ $item->product->name }}</span></td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            Rp.{{ number_format($item->subtotal, 0, ',', '.') }}
        </td>
    </tr>
    @endforeach
</x-print>