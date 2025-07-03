<x-filament::widget>
    
    <h1 class="text-lg font-bold mb-4">Menu Transaksi</h1>
    <div class="flex justify-between gap-6">
        @foreach ($this->getMenus() as $menu)
        <x-filament::button tag="a" color="gray" href="{{ route($menu['route']) }}"
            class="card-menu shadow flex justify-center items-center text-center rounded-lg transition">
            @if(!empty($menu['icon']))
            <img src="{{ asset($menu['icon']) }}" alt="{{ $menu['title'] }} icon" width="130" />
            @endif
            <span class="text-lg font-semibold">{{ $menu['title'] }}</span>
        </x-filament::button>
        @endforeach
    </div>
</x-filament::widget>