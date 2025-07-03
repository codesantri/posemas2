<x-print :name="$data['customer']" :address="$data['address']" :details="$data['items']" :total="$data['total']"
    :cs="$data['cashier']" :date="$data['date']">
    @foreach ($data['items'] as $item)
    <tr>
        <td class="text-center border-0 py-1">{{ $item['qty'] }}</td>
        <td style="width: 45%" class="border-0 py-1">
                    <div class="d-flex justify-content-between">
                        <div class="ms-3">{{ $item['product_name'] }}</div>
                        <div>
                            <img src="{{ asset('storage/'.$item['image']) }}" width="50">
                        </div>
                        <div>
                            <span>{{ $item['weight'] }} {{ $item['rate'] }}</span>
                        </div>
                    </div>
                </td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            Rp.{{ number_format($item['subtotal'], 0, ',', '.') }}
        </td>
    </tr>
    @if ($data['service'] >0)
    <tr>
        <td class="text-center border-0 py-1"></td>
        <td style="width: 45%" class="border-0 py-1"><span class="ms-3">Jasa Pembuatan</span></td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            Rp.{{ number_format($data['service'], 0, ',', '.') }}
        </td>
    </tr>
    @endif
    @if ($data['discount'] >0)
    <tr>
        <td class="text-center border-0 py-1"></td>
        <td style="width: 45%" class="border-0 py-1"><span class="ms-3">Diskon</span></td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            - Rp.{{ number_format($data['discount'], 0, ',', '.') }}
        </td>
    </tr>
    @endif
    @endforeach
</x-print>