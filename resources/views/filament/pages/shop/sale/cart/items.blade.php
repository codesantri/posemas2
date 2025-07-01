@props(['state' => [], 'record' => null])
<div class="w-[360px] flex flex-col">
    <div class="flex-1 overflow-y-auto px-6 py-5">
        <ul class="space-y-6">
            @foreach($state as $item)
            <li class="flex items-center justify-between space-x-4">
                <div class="flex items-center justify-between space-x-4">
                    @if ($product && ($product['image'] ?? $product->image ?? false))
                    <img alt="" class="w-24 h-24 rounded border object-cover flex-shrink-0" height="85"
                        src="{{ asset('storage/'. (is_array($product) ? $product['image'] : $product->image)) }}"
                        width="85" />
                    @else
                    <img alt="" class="w-24 h-24 rounded border object-cover flex-shrink-0" height="85"
                        src="{{ asset('logo-cetak.png') }}" width="85" />
                    @endif
                    <div class="flex-1 min-w-0 mx-3">
                        <p class="font-semibold flex text-sm truncate mb-2">
                            {{ ($product ? (is_array($product) ? $product['name'] : $product->name) : 'Produk tidak
                            ditemukan') }}
                            <x-filament::badge color="warning" class="mx-1">
                                x{{ $quantity }}
                            </x-filament::badge>
                            @if ($product && ($product['karat'] ?? $product->karat ?? false))
                            <x-filament::badge color="success" class="mx-1">
                                {{ is_array($product) ? $product['karat']['karat'] : $product->karat->karat }}
                            </x-filament::badge>
                            @endif
                            <x-filament::badge color="info">
                                {{ $weight .'g' }}
                            </x-filament::badge>
                        </p>
                        <div class="flex flex-row items-start">
                            <x-filament::input.wrapper prefix="Rp">
                                <x-filament::input wire:model="carts.{{ $loop->index }}.price" x-data="{}" x-init="
                                        function formatRupiah(input) {
                                            // Remove all non-digit characters
                                            let value = input.value.replace(/[^\d]/g, '');
                                            // Format as Rupiah
                                            let formatted = new Intl.NumberFormat('id-ID').format(value);
                                            // Update display value
                                            input.value = formatted;
                                            // Update Livewire property (raw number)
                                            \$wire.set('carts.{{ $loop->index }}.price', value);
                                        }
                                        
                                        // Initial format
                                        formatRupiah(\$el);
                                        
                                        // Add event listener
                                        \$el.addEventListener('input', () => formatRupiah(\$el));
                                    " />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>
                <div class="flex flex-rows items-center">
                    <x-filament::button size="sm" color="danger" wire:click="decrement({{ $id }})">
                        <x-heroicon-o-minus class="w-4 text-white" />
                    </x-filament::button>

                    <x-filament::button class="mx-1" size="sm" color="primary" wire:click="increment({{ $id }})">
                        <x-heroicon-o-plus class="w-4 text-white" />
                    </x-filament::button>
                </div>
                <div class="flex flex-col items-end space-y-1">
                    <x-filament::badge style="cursor: pointer" color="danger" wire:click="removeItem({{ $id }})">
                        Hapus
                    </x-filament::badge>
                    <p class="font-semibold text-gray-900 text-sm">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="border-t border-gray-200 px-6 py-5">
        <div class="flex justify-between">
            <x-filament::button href="{{ route('filament.admin.shop.resources.sales.create') }}" color="danger"
                type="button">
                Kembali
            </x-filament::button>
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="checkout">
                <div wire:loading wire:target="checkout">
                    Memproses...
                </div>
                <span wire:loading.remove wire:target="checkout">
                    Proses Penjualan
                </span>
            </x-filament::button>
        </div>
    </div>
</div>