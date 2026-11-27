@props([
    'submit' => null,
    'submitLabel' => '$t("appraisal.cta")',
])
{{-- Formulário de troca — porte de components/dl/TradeInForm.tsx.

     A faixa sai na hora e os ajustes aparecem abertos: nada de "entraremos em
     contato com uma proposta". `$submit` é o corpo do @click do botão de
     gravar; sem ele o formulário só estima. Dentro dele valem `appraisal` e
     `input`. --}}
<div class="grid gap-5 lg:grid-cols-[1fr_1fr]"
    x-data="{
        modelId: $store.dealer.models[0]?.id ?? 1,
        year: new Date($store.clock.now).getFullYear() - 5,
        km: 60000,
        condition: 'bom',
        options: ['Ar-condicionado', 'Central multimídia'],
        get currentYear() { return new Date($store.clock.now).getFullYear() },
        get years() {
            const out = [];
            for (let y = this.currentYear; y >= this.currentYear - 20; y--) out.push(y);
            return out;
        },
        get input() {
            return { modelId: this.modelId, year: this.year, km: this.km, condition: this.condition, options: this.options };
        },
        get appraisal() {
            return $q.appraise(this.input, $store.dealer.models, $store.dealer.rules, $store.clock.now);
        },
        toggleOption(opt) {
            this.options = this.options.includes(opt) ? this.options.filter((o) => o !== opt) : [...this.options, opt];
        },
    }">
    <div class="surface-card rounded-2xl border p-5 md:p-6">
        <div class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-heading" for="ti-model" x-text="$t('appraisal.model')"></label>
                <select id="ti-model" x-model.number="modelId"
                    class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                    <template x-for="model in $store.dealer.models" :key="model.id">
                        <option :value="model.id"
                            x-text="`${$q.brandById($store.dealer.brands, model.brandId)?.name} ${model.name}`"></option>
                    </template>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="ti-year" x-text="$t('appraisal.year')"></label>
                    <select id="ti-year" x-model.number="year"
                        class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body">
                        <template x-for="y in years" :key="y">
                            <option :value="y" x-text="y"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="ti-km" x-text="$t('appraisal.km')"></label>
                    <input id="ti-km" type="number" min="0" step="1000" :value="km"
                        @input="km = Math.max(0, Number($event.target.value))"
                        class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
            </div>

            <div>
                <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('appraisal.condition')"></span>
                <div class="flex flex-wrap gap-2">
                    <template x-for="c in ['excelente', 'bom', 'regular', 'ruim']" :key="c">
                        <button type="button" @click="condition = c"
                            class="focus-ring rounded-lg border px-3.5 py-2 text-sm font-medium transition"
                            :class="condition === c ? 'border-brand-500 bg-brand-500 text-white' : 'border-themed text-body hover:text-heading'"
                            x-text="$t('status.condition.' + c)"></button>
                    </template>
                </div>
            </div>

            <div>
                <span class="mb-1.5 block text-sm font-medium text-heading" x-text="$t('appraisal.options')"></span>
                <div class="flex flex-wrap gap-2">
                    <template x-for="opt in window.OPTION_CATALOG" :key="opt">
                        <button type="button" @click="toggleOption(opt)"
                            class="focus-ring inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition"
                            :class="options.includes(opt)
                                ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'
                                : 'border-themed text-muted hover:text-body'">
                            <template x-if="options.includes(opt)">
                                <span class="contents"><x-icon name="check" :size="12" :strokeWidth="2.5" /></span>
                            </template>
                            <span x-text="opt"></span>
                        </button>
                    </template>
                </div>
            </div>

            @if ($submit)
                <button type="button" @click="{{ $submit }}"
                    class="focus-ring flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 text-sm font-medium text-white transition hover:bg-brand-600">
                    <x-icon name="calculator" :size="16" />
                    <span x-text="{{ $submitLabel }}"></span>
                </button>
            @endif
        </div>
    </div>

    <x-dl.appraisal-breakdown appraisal="appraisal" />
</div>
