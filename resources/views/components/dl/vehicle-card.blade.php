@props([
    'vehicle' => 'v',
    'showState' => 'false',
    'compact' => 'false',
])
{{-- Card da vitrine — porte de components/dl/VehicleCard.tsx.

     A parcela "a partir de" NÃO é um campo gravado: sai do mesmo `pmt()` do
     simulador, com a entrada mínima da loja e o prazo de 48 meses.

     $vehicle é a expressão Alpine do veículo (o `v` de um x-for, por exemplo);
     $showState e $compact também são expressões. --}}
<article class="surface-card group flex flex-col overflow-hidden rounded-2xl border transition hover:shadow-theme-md"
    x-data="{
        get v() { return {{ $vehicle }} },
        get label() { return $q.vehicleLabel($store.dealer.models, $store.dealer.brands, this.v) },
        get down() { return $q.minDown(this.v.price, $store.dealer.rules) },
        get installment() {
            const rules = $store.dealer.rules;
            return $q.pmt(this.v.price - this.down, rules.defaultMonthlyRate, 48);
        },
    }">
    <a :href="'{{ url('/veiculo') }}/' + v.slug" class="relative block overflow-hidden">
        <img :src="$img(v.photoSeeds?.[0] || v.slug, 640, 420)" :alt="label" loading="lazy"
            class="w-full object-cover transition duration-500 group-hover:scale-105"
            :class="({{ $compact }}) ? 'aspect-[16/10]' : 'aspect-[3/2]'" />
        <template x-if="{{ $showState }}">
            <span class="absolute top-3 left-3"><x-dl.stock-pill state="v.state" /></span>
        </template>
        <span class="absolute top-3 right-3"><x-dl.fuel-badge fuel="v.fuel" /></span>
    </a>

    <div class="flex flex-1 flex-col p-4">
        <h3 class="line-clamp-1 font-semibold text-heading">
            <a :href="'{{ url('/veiculo') }}/' + v.slug"
                class="transition group-hover:text-brand-600 dark:group-hover:text-brand-400" x-text="label"></a>
        </h3>
        <p class="mt-0.5 line-clamp-1 text-xs text-muted"
            x-text="`${$q.yearLabel(v)} · ${$number(v.km)} km · ${v.color}`"></p>

        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
            <span class="inline-flex items-center gap-1">
                <x-icon name="settings" :size="13" />
                <span x-text="$t('spec.transmission.' + (v.transmission))"></span>
            </span>
            <span class="inline-flex items-center gap-1">
                <x-icon name="gauge" :size="13" />
                <span x-text="`${$number(v.km)} km`"></span>
            </span>
        </div>

        <div class="mt-auto pt-4">
            <p class="text-lg font-bold text-heading" x-text="$money(v.price)"></p>
            <p class="text-xs text-muted"
                x-text="$t('vehicle.fromInstallment', { value: $money(installment) + $t('common.perMonth') })"></p>
        </div>
    </div>
</article>
