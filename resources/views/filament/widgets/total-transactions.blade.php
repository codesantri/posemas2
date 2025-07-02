<x-filament-widgets::widget>
    <div class="flex gap-3">
        @foreach ($data as $item)
        <div style="width: 33.3% !important;" class="shadow-lg p-2 my-3 border">
            <div style="height: 3rem" class="text-sm font-semibold text-dark border-b d-flex items-end" style="font-size: 12px;"><h1>{{ $item['desc'] }}</h1></div>
            <div class="mb-4">
                <h1 class="text-xl font-extrabold my-4 flex items-end" style="font-size: 1.6rem">
                    <img src="{{ asset($item['icon']) }}" alt="" width="50">
                    <span class="mx-3">Rp. {{ number_format($item['total'], 0, ',', '.') }}</span>
                </h1>
                <div class="flex justify-between items-center mb-2">
                    <x-filament::badge size="xs">
                        <h1 class="text-lg text-dark">{{ $item['mayam'] }} Mayam</h1>
                    </x-filament::badge>
                    <x-filament::badge size="xs" color="info">
                        <h1 class="text-lg text-dark">{{ $item['gram'] }} Gram</h1>
                    </x-filament::badge>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-filament-widgets::widget>