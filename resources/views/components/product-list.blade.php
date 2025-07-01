@php
use App\Models\Product;

$products = Product::with(['karat', 'category', 'type'])->latest()->take(10)->get();
@endphp

<div x-data="{
        query: '',
        products: {{ $products->toJson() }},
        get filteredProducts() {
            if (this.query === '') {
                return this.products;
            }
            return this.products.filter(p =>
                p.name.toLowerCase().includes(this.query.toLowerCase()) ||
                p.type.name.toLowerCase().includes(this.query.toLowerCase()) ||
                p.category.name.toLowerCase().includes(this.query.toLowerCase())
            );
        },
        select(id) {
            $wire.set('data.product_id', id);
            $dispatch('close-modal');
        }
    }" class="space-y-4">

<input x-model="query" type="search" placeholder="Cari Produk..."
    class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" style="outline: none; box-shadow: none;"
    autofocus />

    {{-- Produk List --}}
<div class="">
    <template x-for="product in filteredProducts" :key="product.id">
        <div class="items-center gap-4 border p-3 rounded bg-white shadow-sm transition hover:border-orange-500">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <img :src="'/storage/' + product.image" :alt="product.name" class="w-16 h-16 object-cover rounded" />
                <div class="font-medium" x-text="product.name + ' (' + product.type.name + ')'"></div>
                <div class="text-gray-500" x-text="product.karat.karat + ' / ' + product.karat.rate + '% - ' + product.category.name"></div>
            </div>
            <x-filament::button type="button" @click="select(product.id)">
                Pilih
            </x-filament::button>
        </div>
    </template>
</div>

    {{-- Jika Tidak Ada Produk --}}
    <div x-show="filteredProducts.length === 0" class="text-center text-gray-500">
        Tidak ada produk ditemukan.
    </div>
</div>