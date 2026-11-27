{{-- Clientes — porte de pages/app/CustomersPage.tsx.
     CRUD com histórico de propostas, veículos comprados e validade da CNH. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('customers.title')"]];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="customersPage()">
        <x-app.page-header title="$t('customers.title')" subtitle="$t('customers.subtitle')" :crumbs="$crumbs">
            <x-slot:action>
                <button type="button" @click="open = true"
                    class="focus-ring flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <x-icon name="plus" :size="16" />
                    <span x-text="$t('customers.new')"></span>
                </button>
            </x-slot:action>
        </x-app.page-header>

        <x-ui.filter-bar search="term" searchPlaceholder="$t('customers.searchPlaceholder')">
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" x-model="onlyExpired" class="h-4 w-4 rounded border-themed accent-brand-500" />
                <span x-text="$t('customers.onlyExpired')"></span>
            </label>
        </x-ui.filter-bar>

        <template x-if="pageItems.length">
            <div>
                <div class="surface-card overflow-hidden rounded-2xl border">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-themed text-xs text-muted">
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.name')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.contact')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.document')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.cnh')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.deals')"></th>
                                    <th class="px-5 py-3 font-medium" x-text="$t('customers.columns.since')"></th>
                                    <th class="px-5 py-3 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="customer in pageItems" :key="customer.id">
                                    <tr class="cursor-pointer border-b border-themed last:border-0 hover:bg-gray-50 dark:hover:bg-white/5"
                                        @click="selectedId = customer.id">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <x-ui.avatar src="customer.avatar" name="customer.name" size="sm" />
                                                <span class="font-medium text-heading" x-text="customer.name"></span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-body">
                                            <span class="block truncate text-xs" x-text="customer.email"></span>
                                            <span class="block text-xs text-muted" x-text="customer.phone"></span>
                                        </td>
                                        <td class="px-5 py-3 text-xs text-muted" x-text="customer.document"></td>
                                        <td class="px-5 py-3">
                                            <template x-if="!customer.cnhUntil">
                                                <span class="text-xs text-muted" x-text="$t('customers.cnhMissing')"></span>
                                            </template>
                                            <template x-if="customer.cnhUntil">
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
                                                    :class="$q.cnhValid(customer, today)
                                                        ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500'
                                                        : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'">
                                                    <x-ui.icon-dyn expr="$q.cnhValid(customer, today) ? 'check' : 'ban'"
                                                        :names="['check', 'ban']" :size="11" />
                                                    <span x-text="$q.cnhValid(customer, today)
                                                        ? $t('customers.cnhValid', { date: $date(customer.cnhUntil) })
                                                        : $t('customers.cnhExpired', { date: $date(customer.cnhUntil) })"></span>
                                                </span>
                                            </template>
                                        </td>
                                        <td class="px-5 py-3 text-body" x-text="$q.dealsOfCustomer(s.deals, customer.id).length"></td>
                                        <td class="px-5 py-3 text-xs text-muted" x-text="$date(customer.createdAt)"></td>
                                        <td class="px-5 py-3 text-right">
                                            <button type="button" :title="$t('common.remove')" @click.stop="remove(customer.id)"
                                                class="text-muted transition hover:text-error-600">
                                                <x-icon name="trash" :size="16" />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <x-ui.pagination page="page" totalPages="totalPages" change="page = $event" />
                </div>
            </div>
        </template>

        <template x-if="!pageItems.length">
            <div class="surface-card rounded-2xl border">
                <x-ui.empty-state icon="users" title="$t('customers.empty')" description="$t('customers.emptyDesc')" />
            </div>
        </template>

        {{-- ---- Histórico ---- --}}
        <x-ui.drawer open="Boolean(selected)" close="selectedId = null" title="$t('customers.historyTitle')"
            subtitle="selected?.name ?? ''" width="max-w-lg">
            <template x-if="selected">
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar src="selected.avatar" name="selected.name" size="lg" />
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-heading" x-text="selected.name"></p>
                            <p class="truncate text-xs text-muted" x-text="selected.email"></p>
                            <p class="mt-1 text-xs"
                                :class="$q.cnhValid(selected, today) ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500'"
                                x-text="!selected.cnhUntil
                                    ? $t('customers.cnhMissing')
                                    : ($q.cnhValid(selected, today)
                                        ? $t('customers.cnhValid', { date: $date(selected.cnhUntil) })
                                        : $t('customers.cnhExpired', { date: $date(selected.cnhUntil) }))"></p>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-heading" x-text="$t('customers.bought')"></h4>
                        <template x-if="bought.length">
                            <ul class="divide-y divide-[color:var(--tx-border)]">
                                <template x-for="deal in bought" :key="deal.id">
                                    <li class="flex items-center justify-between gap-3 py-2.5">
                                        <a :href="'{{ url('/app/proposta') }}/' + deal.id"
                                            class="min-w-0 flex-1 truncate text-sm text-heading hover:text-brand-600"
                                            x-text="$q.vehicleLabel(s.models, s.brands, $q.vehicleById(s.vehicles, deal.vehicleId))"></a>
                                        <span class="shrink-0 text-sm font-semibold text-heading"
                                            x-text="$money($q.dealPrice(deal, $q.vehicleById(s.vehicles, deal.vehicleId)))"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>
                        <template x-if="!bought.length">
                            <p class="text-sm text-muted" x-text="$t('customers.noHistory')"></p>
                        </template>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-heading" x-text="$t('deals.title')"></h4>
                        <template x-if="history.length">
                            <ul class="divide-y divide-[color:var(--tx-border)]">
                                <template x-for="deal in history.slice(0, 10)" :key="deal.id">
                                    <li class="flex items-center gap-3 py-2.5">
                                        <x-dl.deal-stage-pill stage="deal.stage" />
                                        <a :href="'{{ url('/app/proposta') }}/' + deal.id"
                                            class="min-w-0 flex-1 truncate text-sm text-body hover:text-brand-600"
                                            x-text="$q.vehicleLabel(s.models, s.brands, $q.vehicleById(s.vehicles, deal.vehicleId))"></a>
                                        <span class="shrink-0 text-xs text-muted" x-text="$date(deal.movedAt)"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>
                        <template x-if="!history.length">
                            <p class="text-sm text-muted" x-text="$t('customers.noHistory')"></p>
                        </template>
                    </div>

                    <template x-if="trades.length > 0">
                        <div>
                            <h4 class="mb-2 text-sm font-semibold text-heading" x-text="$t('appraisals.title')"></h4>
                            <ul class="divide-y divide-[color:var(--tx-border)]">
                                <template x-for="trade in trades.slice(0, 6)" :key="trade.id">
                                    <li class="flex items-center justify-between gap-3 py-2.5">
                                        <span class="min-w-0 flex-1 truncate text-sm text-body"
                                            x-text="`${$q.modelLabel(s.models, s.brands, trade.modelId)} · ${trade.year}`"></span>
                                        <span class="shrink-0 text-xs text-muted" x-text="$date(trade.createdAt)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </template>
        </x-ui.drawer>

        {{-- ---- Novo cliente ---- --}}
        <x-ui.modal open="open" close="open = false" title="$t('customers.new')">
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-heading" for="c-name" x-text="$t('customers.columns.name')"></label>
                    <input id="c-name" x-model="form.name" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-email" x-text="$t('auth.email')"></label>
                        <input id="c-email" type="email" x-model="form.email" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-phone" x-text="$t('drive.yourPhone')"></label>
                        <input id="c-phone" x-model="form.phone" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-doc" x-text="$t('customers.columns.document')"></label>
                        <input id="c-doc" x-model="form.document" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="c-cnh" x-text="$t('drive.cnh')"></label>
                        <input id="c-cnh" type="date" x-model="form.cnhUntil" class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <button type="button" @click="open = false"
                    class="focus-ring rounded-lg border border-themed px-4 py-2 text-sm font-medium text-body" x-text="$t('common.cancel')"></button>
                <button type="button" @click="submit()"
                    class="focus-ring rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                    x-text="$t('common.save')"></button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    @push('scripts')
        <script>
            function customersPage() {
                return {
                    term: '',
                    onlyExpired: false,
                    page: 1,
                    pageSize: 12,
                    selectedId: null,
                    open: false,
                    form: { name: '', email: '', phone: '', document: '', cnhUntil: '' },

                    init() {
                        this.$watch('term', () => { this.page = 1 });
                        this.$watch('onlyExpired', () => { this.page = 1 });
                    },

                    get s() { return $store.dealer },
                    get today() { return new Date($store.clock.now).toISOString().slice(0, 10) },
                    get rows() {
                        const lower = this.term.trim().toLowerCase();
                        return this.s.customers.filter((c) => {
                            if (this.onlyExpired && $q.cnhValid(c, this.today)) return false;
                            if (!lower) return true;
                            return `${c.name} ${c.email} ${c.phone}`.toLowerCase().includes(lower);
                        });
                    },
                    get totalPages() { return Math.max(1, Math.ceil(this.rows.length / this.pageSize)) },
                    get pageItems() { return this.rows.slice((this.page - 1) * this.pageSize, this.page * this.pageSize) },
                    get selected() { return this.selectedId ? $q.customerById(this.s.customers, this.selectedId) : null },
                    get history() { return this.selected ? $q.dealsOfCustomer(this.s.deals, this.selected.id) : [] },
                    get bought() { return this.history.filter((d) => d.stage === 'aprovada') },
                    get trades() { return this.selected ? $q.tradeInsOfCustomer(this.s.tradeIns, this.selected.id) : [] },

                    remove(id) {
                        this.s.removeCustomer(id);
                        $store.ui.notifySuccess($t('customers.removed'));
                    },
                    submit() {
                        this.s.saveCustomer({
                            name: this.form.name,
                            email: this.form.email,
                            phone: this.form.phone,
                            document: this.form.document,
                            cnhUntil: this.form.cnhUntil ? new Date(this.form.cnhUntil + 'T12:00:00').toISOString() : null,
                        });
                        this.open = false;
                        this.form = { name: '', email: '', phone: '', document: '', cnhUntil: '' };
                        $store.ui.notifySuccess($t('customers.saved'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
