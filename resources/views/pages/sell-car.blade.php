{{-- Vender meu carro — porte de pages/public/SellCarPage.tsx. --}}
<x-layout.public :pageTitle="$pageTitle ?? null">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{
            saved: null,
            save(appraisal, input) {
                $store.dealer.saveTradeIn({
                    customerId: window.VISITOR_CUSTOMER_ID,
                    modelId: input.modelId,
                    year: input.year,
                    km: input.km,
                    condition: input.condition,
                    options: input.options,
                    accepted: false,
                });
                this.saved = appraisal;
                $store.ui.notifySuccess($t('appraisal.saved'));
            },
        }">
        <header class="mb-6">
            <h1 class="text-title-sm font-bold text-heading" x-text="$t('appraisal.title')"></h1>
            <p class="mt-1 max-w-2xl text-sm text-muted" x-text="$t('appraisal.subtitle')"></p>
        </header>

        <x-dl.trade-in-form submit="save(appraisal, input)" />

        <template x-if="saved">
            <div class="mt-5 flex items-start gap-3 rounded-2xl border border-success-200 bg-success-50 p-5 dark:border-success-500/30 dark:bg-success-500/10">
                <span class="mt-0.5 text-success-600"><x-icon name="check-circle" :size="20" /></span>
                <div>
                    <p class="text-sm font-semibold text-heading" x-text="`${$t('appraisal.result')}: ${$money(saved.value)}`"></p>
                    <p class="mt-1 text-sm text-muted" x-text="$t('appraisal.applyToDeal')"></p>
                </div>
            </div>
        </template>
    </div>
</x-layout.public>
