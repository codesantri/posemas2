<div class="flex items-center justify-center">
    @if($product?->image)
    <img src="{{ asset('storage/'.$product->image) }}" style="max-height:80px; border-radius:8px;" />
    @else
    <span style="color:#999;">Tidak ada gambar</span>
    @endif
</div>