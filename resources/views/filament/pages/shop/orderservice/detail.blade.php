@props(['discount'=>0,'change'=>0])
<div class="w-[360px]  flex flex-col">
    <div class="border-gray-200 px-6 py-5">
        <div class="flex justify-between font-semibold mb-4 text-1xl">
            <span>Diskon</span>
            <h3>-Rp {{ number_format($discount, 0, ',', '.') }}</h3>
        </div>

        <div class="flex justify-between font-semibold mb-4 text-1xl">
            <span>Kembalian</span>
            <h3>Rp {{ number_format($change, 0, ',', '.') }}</h3>
        </div>
    </div>
</div>