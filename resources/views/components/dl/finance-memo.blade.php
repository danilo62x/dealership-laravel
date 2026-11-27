@props([
    'simulation' => 'simulation',
    'bare' => false,
])
{{-- A memória do cálculo, linha a linha, na ordem em que a conta acontece.
     Cada linha traz o rótulo, a fórmula já com os números substituídos e o
     resultado — é isso que separa uma simulação de um número solto na tela.
     Porte de components/dl/FinanceMemo.tsx. $simulation é expressão Alpine. --}}
<div class="{{ $bare ? '' : 'surface-card rounded-2xl border p-5 md:p-6' }}"
    x-data="{
        get sim() { return {{ $simulation }} },
        render(value, kind) {
            if (kind === 'money') return $money(value);
            if (kind === 'rate') return $rate(value);
            return Number(value).toLocaleString($lang, { maximumFractionDigits: 6 });
        },
    }">
    @unless ($bare)
        <div class="mb-4">
            <h3 class="flex items-center gap-2 font-semibold text-heading">
                <x-icon name="calculator" :size="18" class="text-brand-500" />
                <span x-text="$t('finance.memoTitle')"></span>
            </h3>
            <p class="mt-1 text-xs text-muted" x-text="$t('finance.memoSubtitle')"></p>
        </div>
    @endunless

    <ol class="divide-y divide-[color:var(--tx-border)] overflow-hidden rounded-xl border border-themed">
        <template x-for="(step, i) in sim.steps" :key="step.key">
            <li class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-center sm:gap-4">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-[11px] font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"
                    x-text="i + 1"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-heading" x-text="$t('finance.memo.' + step.key)"></p>
                    <p class="mt-0.5 font-mono text-xs break-words text-muted" x-text="step.formula"></p>
                </div>
                <span class="shrink-0 text-sm font-semibold sm:text-right"
                    :class="step.key === 'cet' || step.key === 'installment' ? 'text-brand-600 dark:text-brand-400' : 'text-heading'"
                    x-text="render(step.value, step.kind)"></span>
            </li>
        </template>
    </ol>

    <p class="mt-3 text-xs text-muted" x-text="$t('finance.cetHint')"></p>
</div>
