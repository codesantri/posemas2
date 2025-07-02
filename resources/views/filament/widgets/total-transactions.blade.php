<x-filament-widgets::widget>
    <div class="flex grid-cols-1 md:grid-cols-2 gap-4 ">
        @foreach ($data as $item)
        <x-filament::card>
            <h1 class="text-dark font-bold">{{$item['title']}}</h1>
            <div class="border-b mb-4">
                <h1 class="text-xl font-extrabold my-4 flex items-end" style="font-size: 1.6rem">
                    <img src="{{ asset($item['icon']) }}" alt="" width="50">
                    <span class="mx-3">Rp. {{ number_format($item['total'], 0, ',', '.') }}</span>
                </h1>
                <div class="flex justify-between items-center mb-2">
                    <x-filament::badge size="xs">
                        <h1 class="text-lg text-dark">{{$item['mayam']}} Mayam</h1>
                    </x-filament::badge>
                    <x-filament::badge size="xs" color="info">
                        <h1 class="text-lg text-dark">{{$item['gram']}} Gram</h1>
                    </x-filament::badge>
                </div>
            </div>
            <p style="font-size: 12px;" class="text-sm font-semibold text-dark">{{$item['desc']}}</p>
        </x-filament::card>
        @endforeach
    </div>
</x-filament-widgets::widget>