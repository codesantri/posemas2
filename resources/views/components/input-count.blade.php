@props(['label' => '', 'model' => ''])

@push('styles')
<style>
.input-money {
    font-size: 24px;
    font-weight: bold;
    text-align: right;
    padding-right: 1rem;
    color: #f97e03 !important;
    background: transparent;
    border: 0; 
}

/* Hilangkan fokus biru */
.input-money:focus {
    outline: none;
    box-shadow: none;
    border-color: #f97e03; /* optional: warna border saat fokus */
}

/* Hilangkan highlight seleksi teks */
.input-money::selection {
    background: transparent;
    color: inherit;
}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.directive('money', (el, { expression }) => {
            const model = expression.trim();

            const formatIDR = (val) => {
                const number = val.toString().replace(/[^\d]/g, '');
                return number ? new Intl.NumberFormat('id-ID').format(number) : '';
            };

            const updateValue = (value) => {
                const formatted = formatIDR(value);
                el.value = formatted;
            };

            if (el.value) {
                updateValue(el.value);
            }

            el.addEventListener('input', (e) => {
                const raw = e.target.value.replace(/[^\d]/g, '');
                e.target.value = formatIDR(raw);

                const component = Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
                if (component && model) {
                    component.set(model, raw ? parseInt(raw) : 0);
                }
            });

            const audio = new Audio('/keyboard.wav');
            el.addEventListener('keyup', (e) => {
                const raw = e.target.value.replace(/[^\d]/g, '');
                e.target.value = formatIDR(raw);
                audio.currentTime = 0;
                // audio.play();
            });

            // Auto-update dari Livewire (pas "change" berubah)
            const observer = new MutationObserver(() => {
                updateValue(el.value);
            });

            observer.observe(el, { attributes: true, childList: true, subtree: true });
        });
    });
</script>
@endpush

<div class="w-full my-4">
    <label for="{{ $model }}" class="block text-sm font-medium mb-2">
        {{ $label }}
    </label>
    <x-filament::input.wrapper prefix="Rp" :valid="! $errors->has($model)" wire:ignore>
        <input id="{{ $model }}" maxlength="18" name="{{ $model }}" type="text" inputmode="numeric" x-data
            x-money="{{ $model }}" wire:model.live="{{ $model }}" class="input-money w-full rounded-lg" required autofocus />
    </x-filament::input.wrapper>
</div>
