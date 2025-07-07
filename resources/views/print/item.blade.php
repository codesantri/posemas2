<tr >
    <td class="text-center border-0 py-1">{{ $item['qty'] }}</td>
    <td style="width: 45%" class="border-0 py-1">
        <div class="d-flex justify-content-between">
            <div class="ms-3">{{ $item['product_name'] }}</div>
            <div>
                <span>{{ $item['weight'] }} {{ $item['rate'] }}</span>
            </div>
        </div>
    </td>
    <td class="border-0 py-1 mx-3" style="text-align: start !important;">
        Rp.{{ number_format($item['subtotal'], 0, ',', '.') }}
    </td>
</tr>