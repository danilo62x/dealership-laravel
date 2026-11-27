{{-- Avaliações — porte de pages/app/AppraisalsPage.tsx.
     Cada linha reabre a memória do cálculo na gaveta, com o aceite. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('appraisals.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="appraisalsPage()">
        <x-app.page-header title="$t('appraisals.title')" subtitle="$t('appraisals.subtitle')" :crumbs="$crumbs" />

        <x-ui.filter-bar search="term" searchPlaceholder="$t('common.search')">
            <x-ui.filter-select model="status" label="$t('common.status')">
                <option value="" x-text="$t('common.all')"></option>
                <option value="accepted" x-text="$t('appraisal.accepted')"></option>
                <option value="pending" x-text="$t('appraisal.pending')"></option>
            </x-ui.filter-select>
        </x-ui.filter-bar>

        <template x-if="rows.length">
            <div class="surface-card overflow-hidden rounded-2xl border">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-themed text-xs text-muted">
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.customer')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.model')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.year')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.km')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.condition')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.value')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.deal')"></th>
                                <th class="px-5 py-3 font-medium" x-text="$t('appraisals.columns.accepted')"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="tradeIn in rows.slice(0, 30)" :key="tradeIn.id">
                                <tr class="cursor-pointer border-b border-themed last:border-0 hover:bg-gray-50 dark:hover:bg-white/5"
                                    @click="selectedId = tradeIn.id">
                                    <td class="px-5 py-3 font-medium text-heading">
                                        <span x-text="$q.customerById(s.customers, tradeIn.customerId)?.name ?? '—'"></span>
                                        <span class="block text-xs font-normal text-muted" x-text="$date(tradeIn.createdAt)"></span>
                                    </td>
                                    <td class="px-5 py-3 text-body" x-text="$q.modelLabel(s.models, s.brands, tradeIn.modelId)"></td>
                                    <td class="px-5 py-3 text-body" x-text="tradeIn.year"></td>
                                    <td class="px-5 py-3 text-body" x-text="$number(tradeIn.km)"></td>
                                    <td class="px-5 py-3"><x-dl.condition-pill condition="tradeIn.condition" /></td>
                                    <td class="px-5 py-3 font-semibold text-heading"
                                        x-text="$money($q.appraiseTradeIn(tradeIn, s.models, s.rules, $store.clock.now).value)"></td>
                                    <td class="px-5 py-3 text-body">
                                        <template x-if="tradeIn.dealId">
                                            <a :href="'{{ url('/app/proposta') }}/' + tradeIn.dealId" @click.stop
                                                class="text-brand-500 hover:text-brand-600" x-text="'#' + tradeIn.dealId"></a>
                                        </template>
                                        <template x-if="!tradeIn.dealId">
                                            <span class="text-muted" x-text="$t('appraisals.standalone')"></span>
                                        </template>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                            :class="tradeIn.accepted
                                                ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                                            x-text="tradeIn.accepted ? $t('appraisal.accepted') : $t('appraisal.pending')"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <template x-if="!rows.length">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="calculator" title="$t('appraisals.empty')" description="$t('appraisals.emptyDesc')" />
            </div>
        </template>

        {{-- ---- Detalhe com a memória do cálculo ---- --}}
        <x-ui.drawer open="Boolean(selected)" close="selectedId = null" title="$t('appraisals.detail')"
            subtitle="selected ? $q.modelLabel(s.models, s.brands, selected.modelId) : ''" width="max-w-lg">
            <template x-if="selected && detail">
                <div class="space-y-4">
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl border border-themed p-3">
                            <dt class="text-xs text-muted" x-text="$t('appraisals.columns.customer')"></dt>
                            <dd class="mt-0.5 font-medium text-heading" x-text="$q.customerById(s.customers, selected.customerId)?.name ?? '—'"></dd>
                        </div>
                        <div class="rounded-xl border border-themed p-3">
                            <dt class="text-xs text-muted" x-text="$t('appraisals.columns.year')"></dt>
                            <dd class="mt-0.5 font-medium text-heading" x-text="selected.year"></dd>
                        </div>
                        <div class="rounded-xl border border-themed p-3">
                            <dt class="text-xs text-muted" x-text="$t('appraisals.columns.km')"></dt>
                            <dd class="mt-0.5 font-medium text-heading" x-text="`${$number(selected.km)} km`"></dd>
                        </div>
                        <div class="rounded-xl border border-themed p-3">
                            <dt class="text-xs text-muted" x-text="$t('appraisals.columns.condition')"></dt>
                            <dd class="mt-1"><x-dl.condition-pill condition="selected.condition" /></dd>
                        </div>
                    </dl>

                    <template x-if="selected.options.length > 0">
                        <div>
                            <p class="mb-1.5 text-xs text-muted" x-text="$t('vehicle.options')"></p>
                            <ul class="flex flex-wrap gap-1.5">
                                <template x-for="opt in selected.options" :key="opt">
                                    <li class="rounded-full border border-themed px-2.5 py-1 text-xs text-body" x-text="opt"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <x-dl.appraisal-breakdown appraisal="detail" :bare="true" />
                </div>
            </template>

            <x-slot:footer>
                <div class="flex gap-2">
                    <button type="button" @click="accept(true)"
                        class="focus-ring flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-success-500 text-sm font-medium text-white transition hover:bg-success-600">
                        <x-icon name="check" :size="16" />
                        <span x-text="$t('appraisals.accept')"></span>
                    </button>
                    <button type="button" @click="accept(false)"
                        class="focus-ring flex h-11 flex-1 items-center justify-center gap-2 rounded-lg border border-themed text-sm font-medium text-body">
                        <x-icon name="x" :size="16" />
                        <span x-text="$t('appraisals.reject')"></span>
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.drawer>
    </div>

    @push('scripts')
        <script>
            function appraisalsPage() {
                return {
                    term: '',
                    status: '',
                    selectedId: null,

                    get s() { return $store.dealer },
                    get rows() {
                        const lower = this.term.trim().toLowerCase();
                        return this.s.tradeIns
                            .filter((tradeIn) => {
                                if (this.status === 'accepted' && !tradeIn.accepted) return false;
                                if (this.status === 'pending' && tradeIn.accepted) return false;
                                if (!lower) return true;
                                const customer = $q.customerById(this.s.customers, tradeIn.customerId);
                                const model = $q.modelLabel(this.s.models, this.s.brands, tradeIn.modelId);
                                return `${customer?.name ?? ''} ${model}`.toLowerCase().includes(lower);
                            })
                            .sort((a, b) => b.createdAt.localeCompare(a.createdAt));
                    },
                    get selected() { return this.selectedId ? $q.tradeInById(this.s.tradeIns, this.selectedId) : null },
                    get detail() {
                        return this.selected ? $q.appraiseTradeIn(this.selected, this.s.models, this.s.rules, $store.clock.now) : null;
                    },
                    accept(value) {
                        if (!this.selected) return;
                        this.s.setTradeInAccepted(this.selected.id, value);
                        $store.ui.notifySuccess($t('appraisals.updated'));
                        this.selectedId = null;
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
