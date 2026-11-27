{{-- As faixas de comissão, com o exemplo de quanto o vendedor leva em cada uma.
     A comissão é uma fatia da margem, então uma venda com margem maior sobe de
     faixa e paga proporcionalmente mais.
     Porte de components/dl/CommissionTable.tsx. --}}
<div class="surface-card rounded-2xl border p-5 md:p-6"
    x-data="{
        /* Exemplo de margem usado em cada faixa, para a conta não ficar abstrata. */
        offset: 2500,
        get rules() { return $store.dealer.rules },
    }">
    <h3 class="flex items-center gap-2 font-semibold text-heading">
        <x-icon name="percent" :size="18" class="text-brand-500" />
        <span x-text="$t('sellers.tiersTitle')"></span>
    </h3>
    <p class="mt-1 text-xs text-muted" x-text="$t('sellers.tiersSubtitle')"></p>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-themed text-xs text-muted">
                    <th class="py-2 pr-4 font-medium" x-text="$t('sellers.tierFrom')"></th>
                    <th class="py-2 pr-4 font-medium" x-text="$t('sellers.tierPct')"></th>
                    <th class="py-2 font-medium" x-text="$t('common.details')"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="tier in rules.commissionTiers" :key="tier[0]">
                    <tr class="border-b border-themed last:border-0">
                        <td class="py-2.5 pr-4 font-medium text-heading" x-text="$money(tier[0])"></td>
                        <td class="py-2.5 pr-4 text-body" x-text="$percent(tier[1])"></td>
                        <td class="py-2.5 text-xs text-muted"
                            x-text="$t('sellers.tierExample', {
                                margin: $money(tier[0] + offset),
                                value: $money($q.commission(tier[0] + offset, rules)),
                            })"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
