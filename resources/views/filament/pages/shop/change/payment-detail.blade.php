<div class="flex justify-between items-start mb-1 border-b p-3">
    <div>
        <h2 class="font-bold">Pelanggan</h2>
        <p class="text-gray-500"><span class="font-medium">{{$customer}}</span></p>
    </div>
    <div class="text-right">
        <p class="font-semibold">Kasir</p>
        <p class="text-gray-500">{{$cashier}}</p>
    </div>
</div>
<table class="w-full text-sm mt-3">
    <thead>
        <tr>
            <th class="py-2 text-left">Deskripsi</th>
            <th class="py-2 text-right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        {{-- Jasa Pembuatan --}}
        <tr>
            <td class="py-4">Jasa Pembuatan</td>
            <td class="py-4 text-right">
                <strong>Rp. {{ number_format($service ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
        {{-- Diskon --}}
        <tr>
            <td class="py-4">Diskon</td>
            <td class="py-4 text-right">
                <strong>- Rp. {{ number_format($discount ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="py-4">Uang Tunai</td>
            <td class="py-4 text-right">
                <strong> Rp. {{ number_format($cash ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="py-4">Kembalian</td>
            <td class="py-4 text-right">
                <strong>Rp. {{ number_format($change_return ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="py-3">Harga</td>
            <td class="py-3 text-right">
                <strong>Rp. {{ number_format(($subtotal ?? 0), 0, ',', '.') }}</strong>
            </td>
        </tr>
        {{-- Subtotal --}}
        <tr class="font-bold border-t">
            <td class="py-4">Jumlah Pembayaran</td>
            <td class="py-4 text-right">
               <h1 class="text-2xl fontbold">
                Rp. {{ number_format($total, 0, ',', '.') }}
            </h1>
            </td>
        </tr>
    </tbody>
</table>

