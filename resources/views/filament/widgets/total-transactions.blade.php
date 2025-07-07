<x-filament-widgets::widget>
    <h1 class="text-center text-3xl font-bold mb-5">HARGA EMAS TERJUAL</h1>
    <di class="flex justify-between items-center mr-3">
        <div class="w-1/2">
            <x-filament::section class="w-full">
                <div class="flex items-center border-b py-2">
                    <img src="{{ asset('icon/total.png') }}" alt="" width="50">
                    <h1 class="text-3xl font-bold mb-5 mx-3 ">Rp. {{ number_format($data['total'], 0, ',', '.') }}</h1>
                </div>
                <div class="flex items-center justify-between">
                    <h1 class="text-lg text-dark text-start font-bold">Jumlah = {{ $data['total_mayam'] }} my </h1>
                    <img src="{{ asset('icon/gold.png') }}" alt="gold-icon" width="80">
                </div>
            </x-filament::section>
        </div>
        <div class="w-1/2 mx-3">
            <x-filament::section class="w-full">
                <div class="flex items-center border-b py-2">
                    <img src="{{ asset('icon/total.png') }}" alt="" width="50">
                    <h1 class="text-3xl font-bold mb-5 mx-3 ">Rp. {{ number_format($data['total'], 0, ',', '.') }}</h1>
                </div>
                <div class="flex items-center justify-between">
                    <h1 class="text-lg text-dark text-start font-bold">Jumlah = {{ $data['total_weight'] }}  gr </h1>
                    <img src="{{ asset('icon/gold.png') }}" alt="gold-icon" width="80">
                </div>
            </x-filament::section>

        </div>
    </di>
</x-filament-widgets::widget>