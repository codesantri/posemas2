<x-print :name="$data['customer']" :images="$images" :address="$data['address']" :details="$rows" :total="$data['total']"
    :cs="$data['cashier']" :date="$data['date']">
    {{-- Jika items ada --}}
@if (count($data['items']) > 0)
@foreach ($data['items'] as $item)
@include('print.item', ['item' => $item])
@endforeach
@endif

{{-- Jika olds atau news ada --}}
@if (count($data['olds']) > 0 || count($data['news']) > 0)
@foreach ($data['olds'] as $item)
@include('print.item', ['item' => $item])
@endforeach

@foreach ($data['news'] as $item)
@include('print.item', ['item' => $item])
@endforeach
@endif

    {{-- Jasa Pembuatan --}}
    @if ($data['service'] >0)
    <tr>
        <td class="text-center border-0 py-1"></td>
        <td style="width: 45%" class="border-0 py-1"><span class="ms-3">Jasa Pembuatan</span></td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            Rp.{{ number_format($data['service'], 0, ',', '.') }}
        </td>
    </tr>
    @endif

    {{-- Diskon --}}
    @if ($data['discount'] >0)
    <tr>
        <td class="text-center border-0 py-1"></td>
        <td style="width: 45%" class="border-0 py-1"><span class="ms-3">Diskon</span></td>
        <td class="border-0 py-1 mx-3" style="text-align: start !important;">
            - Rp.{{ number_format($data['discount'], 0, ',', '.') }}
        </td>
    </tr>
    @endif
</x-print>