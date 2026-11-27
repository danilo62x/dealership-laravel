{{-- Funil arrastável — porte de components/dl/DealBoard.tsx.

     A coluna `aprovada` não é só um rótulo: soltar um card nela reserva o
     veículo e ele sai da vitrine; tirar de lá devolve o carro, sem zerar os
     dias em estoque. --}}
<div x-data="{
        dragging: null,
        over: null,
        get s() { return $store.dealer },
        stages: window.DLQ.STAGES,
        tone: { contato: 'bg-gray-400', proposta: 'bg-brand-500', negociacao: 'bg-warning-500', aprovada: 'bg-success-500', perdida: 'bg-error-500' },
        rowsOf(stage) { return $q.dealsOfStage(this.s.deals, stage) },
        totalOf(stage) {
            return this.rowsOf(stage).reduce((sum, d) => sum + $q.dealPrice(d, $q.vehicleById(this.s.vehicles, d.vehicleId)), 0);
        },
        vehicleOf(deal) { return $q.vehicleById(this.s.vehicles, deal.vehicleId) },
        customerOf(deal) { return $q.customerById(this.s.customers, deal.customerId) },
        sellerOf(deal) { return $q.sellerById(this.s.sellers, deal.sellerId) },
        labelOf(deal) {
            const vehicle = this.vehicleOf(deal);
            return vehicle ? $q.vehicleLabel(this.s.models, this.s.brands, vehicle) : '—';
        },
        drop(stage) {
            this.over = null;
            if (this.dragging === null) return;
            const deal = this.s.deals.find((d) => d.id === this.dragging);
            this.dragging = null;
            if (!deal || deal.stage === stage) return;

            const vehicle = this.vehicleOf(deal);
            const name = vehicle ? $q.vehicleLabel(this.s.models, this.s.brands, vehicle) : $t('common.vehicle');
            const wasApproved = deal.stage === 'aprovada';
            const wasAvailable = vehicle?.state === 'disponivel';
            const wasReserved = vehicle?.state === 'reservado';

            this.s.moveDeal(deal.id, stage);
            $store.ui.notify($t('deals.moved', { stage: $t('status.deal.' + stage) }), 'info');

            if (stage === 'aprovada' && wasAvailable) $store.ui.notifyWarning($t('deals.reserved', { name }));
            if (wasApproved && stage !== 'aprovada' && wasReserved) $store.ui.notifySuccess($t('deals.released', { name }));
        },
    }">
    <p class="mb-3 flex items-center gap-1.5 text-xs text-muted">
        <x-icon name="info" :size="14" />
        <span x-text="$t('deals.approvedNote')"></span>
    </p>

    <div class="no-scrollbar flex gap-4 overflow-x-auto pb-2">
        <template x-for="stage in stages" :key="stage">
            <section @dragover.prevent="over = stage" @dragleave="over = (over === stage ? null : over)"
                @drop="drop(stage)"
                class="flex w-72 shrink-0 flex-col rounded-2xl border p-3 transition"
                :class="over === stage ? 'border-brand-500 bg-brand-50/70 dark:bg-brand-500/10' : 'surface-card'">
                <header class="mb-3 flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full" :class="tone[stage]"></span>
                    <h3 class="text-sm font-semibold text-heading" x-text="$t('status.deal.' + stage)"></h3>
                    <span class="ml-auto rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        x-text="rowsOf(stage).length"></span>
                </header>
                <p class="mb-3 text-[11px] text-muted" x-text="`${$t('deals.stageValue')}: ${$money(totalOf(stage))}`"></p>

                <ul class="flex max-h-[62vh] flex-col gap-2 overflow-y-auto pr-0.5">
                    <template x-for="deal in rowsOf(stage)" :key="deal.id">
                        <li draggable="true" @dragstart="dragging = deal.id" @dragend="dragging = null"
                            class="surface-card cursor-grab rounded-xl border p-3 transition active:cursor-grabbing"
                            :class="dragging === deal.id ? 'opacity-40' : 'hover:shadow-theme-sm'">
                            <a :href="'{{ url('/app/proposta') }}/' + deal.id" class="block">
                                <p class="line-clamp-2 text-sm font-medium text-heading" x-text="labelOf(deal)"></p>
                                <p class="mt-1 text-sm font-bold text-brand-600 dark:text-brand-400"
                                    x-text="$money($q.dealPrice(deal, vehicleOf(deal)))"></p>
                                <template x-if="deal.discount > 0">
                                    <p class="text-[11px] text-muted" x-text="`${$t('deal.discount')}: ${$money(deal.discount)}`"></p>
                                </template>
                            </a>

                            <div class="mt-3 flex items-center gap-2 border-t border-themed pt-2.5">
                                <x-ui.avatar src="sellerOf(deal)?.avatar" name="sellerOf(deal)?.name" size="xs" />
                                <span class="min-w-0 flex-1 truncate text-[11px] text-muted" x-text="customerOf(deal)?.name ?? '—'"></span>
                                <template x-if="deal.tradeInId">
                                    <span class="text-brand-500"><x-icon name="refresh-cw" :size="12" /></span>
                                </template>
                                <template x-if="deal.finance">
                                    <span class="text-muted"><x-icon name="credit-card" :size="12" /></span>
                                </template>
                            </div>
                        </li>
                    </template>
                    <template x-if="rowsOf(stage).length === 0">
                        <li class="rounded-xl border border-dashed border-themed px-3 py-6 text-center text-xs text-muted"
                            x-text="$t('deals.emptyColumn')"></li>
                    </template>
                </ul>
            </section>
        </template>
    </div>
</div>
