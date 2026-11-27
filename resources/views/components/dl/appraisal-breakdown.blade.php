@props([
    'appraisal' => 'appraisal',
    'bare' => false,
])
{{-- Cada ajuste da avaliação em UMA linha, com o valor em reais — positivo ou
     negativo. Sem essa abertura o cliente só veria o número final e teria de
     acreditar nele. Porte de components/dl/AppraisalBreakdown.tsx. --}}
<div class="{{ $bare ? '' : 'surface-card rounded-2xl border p-5 md:p-6' }}"
    x-data="{
        get appraisal() { return {{ $appraisal }} },
        label(adj) {
            if (adj.key.startsWith('condition.')) {
                const condition = adj.key.slice('condition.'.length);
                return $t('appraisal.adj.conditionLabel', { detail: $t('status.condition.' + condition) });
            }
            return $t('appraisal.adj.' + adj.key, { detail: adj.detail });
        },
    }">
    @unless ($bare)
        <h3 class="mb-4 flex items-center gap-2 font-semibold text-heading">
            <x-icon name="calculator" :size="18" class="text-brand-500" />
            <span x-text="$t('appraisal.breakdown')"></span>
        </h3>
    @endunless

    <ul class="divide-y divide-[color:var(--tx-border)] overflow-hidden rounded-xl border border-themed">
        <li class="flex items-center justify-between gap-4 bg-gray-50 px-4 py-3 dark:bg-white/5">
            <span class="text-sm font-medium text-heading" x-text="$t('appraisal.reference')"></span>
            <span class="text-sm font-semibold text-heading" x-text="$money(appraisal.reference)"></span>
        </li>
        <template x-for="adj in appraisal.adjustments" :key="adj.key">
            <li class="flex items-center justify-between gap-4 px-4 py-3">
                <span class="min-w-0 text-sm text-body" x-text="label(adj)"></span>
                <span class="shrink-0 text-sm font-semibold"
                    :class="adj.amount < 0 ? 'text-error-600 dark:text-error-500' : (adj.amount > 0 ? 'text-success-600 dark:text-success-500' : 'text-muted')"
                    x-text="(adj.amount > 0 ? '+' : '') + $money(adj.amount)"></span>
            </li>
        </template>
        <li class="flex items-center justify-between gap-4 bg-brand-50 px-4 py-3 dark:bg-brand-500/10">
            <span class="text-sm font-semibold text-heading" x-text="$t('appraisal.result')"></span>
            <span class="text-base font-bold text-brand-600 dark:text-brand-400" x-text="$money(appraisal.value)"></span>
        </li>
    </ul>

    <p class="mt-3 flex items-center gap-1.5 text-xs text-muted">
        <x-icon name="info" :size="14" />
        <span x-text="$t('appraisal.range', { low: $money(appraisal.low), high: $money(appraisal.high) })"></span>
    </p>
</div>
