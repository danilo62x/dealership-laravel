@props(['vehicle' => 'vehicle'])
{{-- Ficha técnica em grade — cada linha vem do veículo, nada é escrito à mão.
     Porte de components/dl/SpecGrid.tsx. --}}
<dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
    x-data="{
        get v() { return {{ $vehicle }} },
        get rows() {
            const v = this.v;
            if (!v) return [];
            const model = $q.modelById($store.dealer.models, v.modelId);
            return [
                { icon: 'calendar', label: $t('vehicle.year'), value: $q.yearLabel(v) },
                { icon: 'gauge', label: $t('vehicle.km'), value: $number(v.km) + ' km' },
                { icon: 'settings', label: $t('vehicle.transmission'), value: $t('spec.transmission.' + v.transmission) },
                { icon: 'fuel', label: $t('vehicle.fuel'), value: $t('spec.fuel.' + v.fuel) },
                { icon: 'palette', label: $t('vehicle.color'), value: v.color },
                { icon: 'grid', label: $t('vehicle.doors'), value: String(v.doors) },
                { icon: 'car', label: $t('vehicle.category'), value: model ? $t('spec.category.' + model.category) : '—' },
                { icon: 'barcode', label: $t('vehicle.plateEnd'), value: v.plateEnd },
            ];
        },
    }">
    <template x-for="row in rows" :key="row.label">
        <div class="rounded-xl border border-themed p-3.5">
            <dt class="flex items-center gap-1.5 text-xs text-muted">
                <x-ui.icon-dyn expr="row.icon"
                    :names="['calendar', 'gauge', 'settings', 'fuel', 'palette', 'grid', 'car', 'barcode']" :size="14" />
                <span x-text="row.label"></span>
            </dt>
            <dd class="mt-1 text-sm font-semibold text-heading" x-text="row.value"></dd>
        </div>
    </template>
</dl>
