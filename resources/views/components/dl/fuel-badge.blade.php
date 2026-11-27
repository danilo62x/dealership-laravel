@props([
    'fuel' => "''",
    'size' => 12,
])
{{-- Combustível com ícone — o dado mais consultado depois do preço.
     Porte de components/dl/FuelBadge.tsx. $fuel é uma expressão Alpine. --}}
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
    x-data="{ get fuel() { return {{ $fuel }} } }"
    :class="({
        flex: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
        gasolina: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        diesel: 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500',
        hibrido: 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-500',
        eletrico: 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
    })[fuel]">
    <x-ui.icon-dyn
        expr="({ flex: 'fuel', gasolina: 'fuel', diesel: 'truck', hibrido: 'refresh-cw', eletrico: 'zap' })[fuel]"
        :names="['fuel', 'truck', 'refresh-cw', 'zap']" :size="$size" :strokeWidth="2.2" />
    <span x-text="$t('spec.fuel.' + fuel)"></span>
</span>
