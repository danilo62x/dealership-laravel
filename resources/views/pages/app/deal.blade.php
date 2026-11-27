{{-- Proposta — porte de pages/app/DealPage.tsx.
     Veículo, financiamento simulado, troca avaliada, desconto e aprovação. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['href' => url('/app/propostas'), 'expr' => "\$t('deals.title')"], ['expr' => "'#' + deal.id"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="dealPage(@js($dealId))">
        <template x-if="!deal || !vehicle">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="handshake" title="$t('deal.notFound')">
                    <a href="{{ url('/app/propostas') }}"
                        class="focus-ring rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white"
                        x-text="$t('deal.backToBoard')"></a>
                </x-ui.empty-state>
            </div>
        </template>

        <template x-if="deal && vehicle">
            <div>
                <x-app.page-header title="`${$t('deal.title')} #${deal.id}`"
                    subtitle="`${customer?.name ?? '—'} · ${$date(deal.createdAt)}`"
                    :crumbs="$crumbs">
                    <x-slot:action>
                        <a href="{{ url('/app/propostas') }}"
                            class="focus-ring flex h-11 items-center gap-2 rounded-lg border border-themed px-5 text-sm font-medium text-body">
                            <x-icon name="arrow-right" :size="16" class="rotate-180" />
                            <span x-text="$t('deal.backToBoard')"></span>
                        </a>
                    </x-slot:action>
                </x-app.page-header>

                <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
                    <div class="min-w-0 space-y-5">
                        {{-- ---- Veículo ---- --}}
                        <section class="surface-card rounded-2xl border p-5 md:p-6">
                            <h2 class="mb-4 font-semibold text-heading" x-text="$t('deal.vehicleSection')"></h2>
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <img :src="$img(vehicle.photoSeeds?.[0] || vehicle.slug, 320, 220)" :alt="label"
                                    class="h-32 w-full shrink-0 rounded-xl object-cover sm:w-48" />
                                <div class="min-w-0 flex-1">
                                    <a :href="'{{ url('/app/veiculo') }}/' + vehicle.id"
                                        class="font-semibold text-heading hover:text-brand-600" x-text="label"></a>
                                    <p class="mt-0.5 text-sm text-muted"
                                        x-text="`${$q.yearLabel(vehicle)} · ${$number(vehicle.km)} km · ${vehicle.color}`"></p>
                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                        <x-dl.deal-stage-pill stage="deal.stage" />
                                        <span class="text-sm font-bold text-heading" x-text="$money(vehicle.price)"></span>
                                    </div>
                                    <template x-if="deal.stage === 'perdida' && deal.lostReason">
                                        <p class="mt-3 flex items-center gap-2 text-xs text-error-600 dark:text-error-500">
                                            <x-icon name="ban" :size="14" />
                                            <span x-text="`${$t('deals.lostReason')}: ${deal.lostReason}`"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </section>

                        {{-- ---- Financiamento ---- --}}
                        <section class="surface-card rounded-2xl border p-5 md:p-6">
                            <h2 class="mb-4 font-semibold text-heading" x-text="$t('deal.financeSection')"></h2>
                            <template x-if="simulation">
                                <div>
                                    <dl class="mb-4 grid gap-3 sm:grid-cols-4">
                                        <div class="rounded-xl border border-themed p-3.5">
                                            <dt class="text-xs text-muted" x-text="$t('finance.down')"></dt>
                                            <dd class="mt-1 text-sm font-semibold text-heading" x-text="$money(simulation.down)"></dd>
                                        </div>
                                        <div class="rounded-xl border border-themed p-3.5">
                                            <dt class="text-xs text-muted" x-text="$t('finance.months')"></dt>
                                            <dd class="mt-1 text-sm font-semibold text-heading"
                                                x-text="$t('finance.monthsUnit', { months: simulation.months })"></dd>
                                        </div>
                                        <div class="rounded-xl border border-themed p-3.5">
                                            <dt class="text-xs text-muted" x-text="$t('finance.installment')"></dt>
                                            <dd class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-400"
                                                x-text="$money(simulation.installment)"></dd>
                                        </div>
                                        <div class="rounded-xl border border-themed p-3.5">
                                            <dt class="text-xs text-muted" x-text="$t('finance.cet')"></dt>
                                            <dd class="mt-1 text-sm font-semibold text-heading" x-text="$rate(simulation.cet)"></dd>
                                        </div>
                                    </dl>
                                    <x-dl.finance-memo simulation="simulation" :bare="true" />
                                </div>
                            </template>
                            <template x-if="!simulation">
                                <p class="text-sm text-muted" x-text="$t('deal.noFinance')"></p>
                            </template>
                        </section>

                        {{-- ---- Troca ---- --}}
                        <section class="surface-card rounded-2xl border p-5 md:p-6">
                            <h2 class="mb-4 font-semibold text-heading" x-text="$t('deal.tradeSection')"></h2>
                            <template x-if="tradeIn && appraisal">
                                <div>
                                    <div class="mb-4 flex flex-wrap items-center gap-3 text-sm">
                                        <span class="font-medium text-heading" x-text="$q.modelLabel(s.models, s.brands, tradeIn.modelId)"></span>
                                        <span class="text-muted" x-text="`${tradeIn.year} · ${$number(tradeIn.km)} km`"></span>
                                        <x-dl.condition-pill condition="tradeIn.condition" />
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                            :class="tradeIn.accepted
                                                ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                                            x-text="tradeIn.accepted ? $t('appraisal.accepted') : $t('appraisal.pending')"></span>
                                    </div>

                                    <x-dl.appraisal-breakdown appraisal="appraisal" :bare="true" />

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="button" @click="toggleTrade()"
                                            class="focus-ring rounded-lg border border-themed px-4 py-2 text-sm font-medium text-body"
                                            x-text="tradeIn.accepted ? $t('deal.rejectTrade') : $t('deal.acceptTrade')"></button>
                                    </div>
                                    <p class="mt-3 text-xs text-muted" x-text="$t('appraisal.applyToDeal')"></p>
                                </div>
                            </template>
                            <template x-if="!tradeIn || !appraisal">
                                <p class="text-sm text-muted" x-text="$t('deal.noTrade')"></p>
                            </template>
                        </section>
                    </div>

                    {{-- ---- Desconto e aprovação ---- --}}
                    <aside class="min-w-0 space-y-5">
                        <section class="surface-card rounded-2xl border p-5 md:p-6">
                            <h2 class="mb-4 font-semibold text-heading" x-text="$t('deal.approvalSection')"></h2>

                            <div class="mb-4 flex items-center gap-3">
                                <x-ui.avatar src="seller?.avatar" name="seller?.name" size="sm" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-heading" x-text="seller?.name ?? '—'"></p>
                                    <p class="truncate text-xs text-muted" x-text="customer?.name ?? '—'"></p>
                                </div>
                            </div>

                            <label class="mb-1.5 block text-sm font-medium text-heading" for="d-discount" x-text="$t('deal.discount')"></label>
                            <input id="d-discount" type="number" min="0" :max="vehicle.price" step="100" :value="discount"
                                @input="discount = Math.max(0, Number($event.target.value))"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />

                            <dl class="mt-4 space-y-2.5 text-sm">
                                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                                    <dt class="text-muted" x-text="$t('deal.finalPrice')"></dt>
                                    <dd class="font-semibold text-heading" x-text="$money(finalPrice)"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                                    <dt class="text-muted" x-text="$t('deal.marginAfter')"></dt>
                                    <dd class="font-semibold"
                                        :class="marginValue < 0 ? 'text-error-600 dark:text-error-500' : 'text-success-600 dark:text-success-500'">
                                        <span x-text="$money(marginValue)"></span>
                                        <span class="ml-1 text-xs font-normal text-muted" x-text="$percent($q.marginPct(vehicle, finalPrice))"></span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-themed pt-2.5">
                                    <dt class="text-muted" x-text="$t('deal.commission')"></dt>
                                    <dd class="font-semibold text-heading">
                                        <span x-text="$money($q.commission(marginValue, s.rules))"></span>
                                        <span class="ml-1 text-xs font-normal text-muted" x-text="$percent($q.commissionRate(marginValue, s.rules))"></span>
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-5 flex flex-col gap-2">
                                <button type="button" @click="applyDiscount()"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg border border-themed text-sm font-medium text-body">
                                    <x-icon name="edit" :size="16" />
                                    <span x-text="$t('common.save')"></span>
                                </button>
                                <button type="button" @click="approve()" :disabled="deal.stage === 'aprovada'"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg bg-success-500 text-sm font-medium text-white transition hover:bg-success-600 disabled:opacity-50">
                                    <x-icon name="check-circle" :size="16" />
                                    <span x-text="$t('deal.approve')"></span>
                                </button>
                                <button type="button" @click="close()" :disabled="deal.stage !== 'aprovada'"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 text-sm font-medium text-white transition hover:bg-brand-600 disabled:opacity-50">
                                    <x-icon name="dollar" :size="16" />
                                    <span x-text="$t('deal.closeSale')"></span>
                                </button>
                                <button type="button" @click="lose()" :disabled="deal.stage === 'perdida'"
                                    class="focus-ring flex h-11 items-center justify-center gap-2 rounded-lg border border-error-200 text-sm font-medium text-error-600 transition hover:bg-error-50 disabled:opacity-50 dark:border-error-500/30 dark:hover:bg-error-500/10">
                                    <x-icon name="ban" :size="16" />
                                    <span x-text="$t('deal.lose')"></span>
                                </button>
                            </div>

                            <p class="mt-4 text-xs text-muted" x-text="$t('deals.approvedNote')"></p>
                        </section>
                    </aside>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
        <script>
            function dealPage(dealId) {
                return {
                    dealId,
                    discount: 0,
                    ready: false,

                    init() {
                        this.discount = this.deal?.discount ?? 0;
                        this.ready = true;
                    },

                    get s() { return $store.dealer },
                    get deal() { return $q.dealById(this.s.deals, this.dealId) },
                    get vehicle() { return this.deal ? $q.vehicleById(this.s.vehicles, this.deal.vehicleId) : undefined },
                    get label() { return this.vehicle ? $q.vehicleLabel(this.s.models, this.s.brands, this.vehicle) : $t('deal.notFound') },
                    get customer() { return this.deal ? $q.customerById(this.s.customers, this.deal.customerId) : undefined },
                    get seller() { return this.deal ? $q.sellerById(this.s.sellers, this.deal.sellerId) : undefined },
                    get tradeIn() { return this.deal?.tradeInId ? $q.tradeInById(this.s.tradeIns, this.deal.tradeInId) : undefined },
                    get appraisal() {
                        return this.tradeIn ? $q.appraiseTradeIn(this.tradeIn, this.s.models, this.s.rules, $store.clock.now) : null;
                    },
                    /** Só a troca APROVADA abate a entrada do carro novo. */
                    get tradeValue() { return this.tradeIn?.accepted && this.appraisal ? this.appraisal.value : 0 },
                    get finalPrice() { return this.vehicle ? Math.max(0, this.vehicle.price - this.discount) : 0 },
                    get marginValue() { return this.vehicle ? $q.margin(this.vehicle, this.finalPrice) : 0 },
                    get simulation() {
                        if (!this.deal?.finance || !this.vehicle) return null;
                        return $q.simulate(
                            {
                                price: this.finalPrice,
                                down: this.deal.finance.down,
                                months: this.deal.finance.months,
                                rate: this.deal.finance.monthlyRate,
                                tradeIn: this.tradeValue,
                            },
                            this.s.rules
                        );
                    },

                    applyDiscount() {
                        this.s.saveDeal({ id: this.deal.id, discount: this.discount });
                        $store.ui.notifySuccess($t('vehicleAdmin.saved'));
                    },
                    approve() {
                        this.s.moveDeal(this.deal.id, 'aprovada');
                        $store.ui.notifyWarning($t('deals.reserved', { name: this.label }));
                    },
                    close() {
                        this.s.closeSale(this.deal.id);
                        $store.ui.notifySuccess($t('deal.closed'));
                    },
                    lose() {
                        this.s.moveDeal(this.deal.id, 'perdida', 'Desistiu da compra');
                        $store.ui.notify($t('deals.released', { name: this.label }), 'info');
                    },
                    toggleTrade() {
                        this.s.setTradeInAccepted(this.tradeIn.id, !this.tradeIn.accepted);
                        $store.ui.notifySuccess($t('appraisals.updated'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
