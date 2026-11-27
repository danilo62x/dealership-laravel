@props([
    'seeds' => '[]',
    'alt' => "''",
])
{{-- Galeria do veículo: foto grande e miniaturas clicáveis.
     Porte de components/dl/VehicleGallery.tsx. --}}
<div x-data="{
        index: 0,
        get list() {
            const s = {{ $seeds }};
            return (s && s.length) ? s : ['veiculo-sem-foto'];
        },
        get current() { return Math.min(this.index, this.list.length - 1) },
        prev() { this.index = (this.current - 1 + this.list.length) % this.list.length },
        next() { this.index = (this.current + 1) % this.list.length },
    }">
    <div class="relative overflow-hidden rounded-2xl border border-themed">
        <img :src="$img(list[current], 1200, 800)" :alt="{{ $alt }}" class="aspect-[3/2] w-full object-cover" />
        <template x-if="list.length > 1">
            <span class="contents">
                <button type="button" @click="prev()" :aria-label="$t('common.previous')"
                    class="absolute top-1/2 left-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-gray-700 backdrop-blur transition hover:bg-white dark:bg-gray-900/80 dark:text-gray-100">
                    <x-icon name="chevron-left" :size="18" />
                </button>
                <button type="button" @click="next()" :aria-label="$t('common.next')"
                    class="absolute top-1/2 right-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-gray-700 backdrop-blur transition hover:bg-white dark:bg-gray-900/80 dark:text-gray-100">
                    <x-icon name="chevron-right" :size="18" />
                </button>
                <span class="absolute right-3 bottom-3 rounded-full bg-gray-900/70 px-2.5 py-1 text-xs font-medium text-white"
                    x-text="`${current + 1}/${list.length}`"></span>
            </span>
        </template>
    </div>

    <template x-if="list.length > 1">
        <div class="no-scrollbar mt-3 flex gap-3 overflow-x-auto">
            <template x-for="(seed, i) in list" :key="seed">
                <button type="button" @click="index = i" :aria-label="`${$t('vehicle.gallery')} ${i + 1}`"
                    class="shrink-0 overflow-hidden rounded-xl border-2 transition"
                    :class="i === current ? 'border-brand-500' : 'border-transparent opacity-70 hover:opacity-100'">
                    <img :src="$img(seed, 200, 150)" alt="" class="h-16 w-24 object-cover" />
                </button>
            </template>
        </div>
    </template>
</div>
