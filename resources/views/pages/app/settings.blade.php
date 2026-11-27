{{-- Configurações — porte de pages/app/SettingsPage.tsx.
     Perfil, loja, regras (taxa padrão, entrada mínima, faixas de comissão,
     giro lento), tema e idioma. --}}
@php
    $crumbs = [['href' => url('/app'), 'expr' => "\$t('appNav.dashboard')", 'icon' => 'dashboard'], ['expr' => "\$t('settings.title')"]];
    $tabs = [
        ['label' => "\$t('settings.tabs.profile')", 'icon' => 'user'],
        ['label' => "\$t('settings.tabs.store')", 'icon' => 'store'],
        ['label' => "\$t('settings.tabs.rules')", 'icon' => 'calculator'],
        ['label' => "\$t('settings.tabs.preferences')", 'icon' => 'settings'],
    ];
@endphp
<x-layout.app :pageTitle="$pageTitle ?? null">
    <div x-data="settingsPage()">
        <x-app.page-header title="$t('settings.title')" subtitle="$t('settings.subtitle')" :crumbs="$crumbs">
            <x-slot:action>
                <button type="button" @click="$store.ui.notifySuccess($t('settings.saved'))"
                    class="focus-ring flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <x-icon name="check" :size="16" />
                    <span x-text="$t('common.save')"></span>
                </button>
            </x-slot:action>
        </x-app.page-header>

        <div class="surface-card rounded-2xl border p-5 md:p-6">
            <x-ui.tabs model="tab" :tabs="$tabs" />

            <div class="pt-5 text-sm text-body">
                {{-- ---- Perfil ---- --}}
                <div x-show="tab === 0" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="p-name" x-text="$t('settings.profile.name')"></label>
                        <input id="p-name" :value="$store.auth.user?.name ?? ''"
                            @input="$store.auth.updateProfile({ name: $event.target.value })"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="p-email" x-text="$t('settings.profile.email')"></label>
                        <input id="p-email" type="email" :value="$store.auth.user?.email ?? ''"
                            @input="$store.auth.updateProfile({ email: $event.target.value })"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="p-role" x-text="$t('settings.profile.role')"></label>
                        <input id="p-role" :value="$store.auth.user?.role ?? ''"
                            @input="$store.auth.updateProfile({ role: $event.target.value })"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-heading" for="p-store" x-text="$t('settings.profile.store')"></label>
                        <input id="p-store" :value="$store.auth.user?.store ?? ''"
                            @input="$store.auth.updateProfile({ store: $event.target.value })"
                            class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                    </div>
                </div>

                {{-- ---- Loja ---- --}}
                <div x-show="tab === 1" class="grid gap-4 sm:grid-cols-2">
                    <template x-for="field in storeFields" :key="field.key">
                        <div :class="field.key === 'address' ? 'sm:col-span-2' : ''">
                            <label class="mb-1.5 block text-sm font-medium text-heading" :for="'s-' + field.key"
                                x-text="$t(field.label)"></label>
                            <input :id="'s-' + field.key" :value="shop[field.key]"
                                @input="shop[field.key] = $event.target.value"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                    </template>
                </div>

                {{-- ---- Regras ---- --}}
                <div x-show="tab === 2" class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-rate" x-text="$t('settings.rules.defaultMonthlyRate')"></label>
                            <input id="r-rate" type="number" step="0.01" :value="(rules.defaultMonthlyRate * 100).toFixed(2)"
                                @input="s.saveRules({ defaultMonthlyRate: Number($event.target.value) / 100 })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-down" x-text="$t('settings.rules.minDownPct')"></label>
                            <input id="r-down" type="number" step="1" :value="Math.round(rules.minDownPct * 100)"
                                @input="s.saveRules({ minDownPct: Number($event.target.value) / 100 })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-fee" x-text="$t('settings.rules.registrationFee')"></label>
                            <input id="r-fee" type="number" :value="rules.registrationFee"
                                @input="s.saveRules({ registrationFee: Number($event.target.value) })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-iof" x-text="$t('settings.rules.iocPct')"></label>
                            <input id="r-iof" type="number" step="0.01" :value="(rules.iocPct * 100).toFixed(2)"
                                @input="s.saveRules({ iocPct: Number($event.target.value) / 100 })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-slow" x-text="$t('settings.rules.slowStockDays')"></label>
                            <input id="r-slow" type="number" :value="rules.slowStockDays"
                                @input="s.saveRules({ slowStockDays: Number($event.target.value) })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-dep" x-text="$t('settings.rules.depreciationPerYear')"></label>
                            <input id="r-dep" type="number" step="0.5" :value="(rules.depreciationPerYear * 100).toFixed(1)"
                                @input="s.saveRules({ depreciationPerYear: Number($event.target.value) / 100 })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-km" x-text="$t('settings.rules.kmPerYearRef')"></label>
                            <input id="r-km" type="number" step="500" :value="rules.kmPerYearRef"
                                @input="s.saveRules({ kmPerYearRef: Number($event.target.value) })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-heading" for="r-kmadj" x-text="$t('settings.rules.kmAdjustPer1000')"></label>
                            <input id="r-kmadj" type="number" step="10" :value="rules.kmAdjustPer1000"
                                @input="s.saveRules({ kmAdjustPer1000: Number($event.target.value) })"
                                class="focus-ring w-full rounded-lg border border-themed bg-transparent px-3 py-2.5 text-sm text-body" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-themed p-4 text-sm text-muted"
                        x-text="`${$t('finance.minDown', { value: $money(100000 * rules.minDownPct) })} · ${$t('finance.rate')} ${$percent(rules.defaultMonthlyRate)} · ${$t('settings.rules.slowStockDays')} ${rules.slowStockDays}`"></div>

                    <x-dl.commission-table />
                </div>

                {{-- ---- Preferências ---- --}}
                <div x-show="tab === 3" class="space-y-5">
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-themed p-4">
                        <div>
                            <p class="text-sm font-medium text-heading" x-text="$t('settings.preferences.darkMode')"></p>
                        </div>
                        <x-ui.switch-toggle checked="$store.ui.darkMode" change="$store.ui.toggleDarkMode()" />
                    </div>

                    <div class="rounded-xl border border-themed p-4">
                        <p class="mb-2 text-sm font-medium text-heading" x-text="$t('settings.preferences.language')"></p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="language in $store.ui.languages" :key="language.code">
                                <button type="button" @click="$store.ui.setLang(language.code)"
                                    class="focus-ring inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition"
                                    :class="$store.ui.lang === language.code
                                        ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'
                                        : 'border-themed text-body'">
                                    <span aria-hidden x-text="language.flag"></span>
                                    <span x-text="language.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-themed p-4">
                        <p class="text-sm font-medium text-heading" x-text="$t('settings.preferences.cookies')"></p>
                        <button type="button" @click="$store.ui.openCookieBanner()"
                            class="focus-ring rounded-lg border border-themed px-4 py-2 text-sm font-medium text-body"
                            x-text="$t('common.open')"></button>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-error-200 p-4 dark:border-error-500/30">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-heading" x-text="$t('settings.preferences.reset')"></p>
                            <p class="mt-0.5 text-xs text-muted" x-text="$t('settings.preferences.resetHint')"></p>
                        </div>
                        <button type="button" @click="reset()"
                            class="focus-ring inline-flex items-center gap-2 rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/30 dark:hover:bg-error-500/10">
                            <x-icon name="refresh-cw" :size="16" />
                            <span x-text="$t('settings.preferences.reset')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function settingsPage() {
                return {
                    tab: 0,
                    shop: { ...window.DL.shop },
                    storeFields: [
                        { key: 'legalName', label: 'settings.store.legalName' },
                        { key: 'address', label: 'settings.store.address' },
                        { key: 'phone', label: 'settings.store.phone' },
                        { key: 'whatsapp', label: 'settings.store.whatsapp' },
                        { key: 'email', label: 'settings.store.email' },
                        { key: 'hours', label: 'settings.store.hours' },
                    ],

                    get s() { return $store.dealer },
                    get rules() { return this.s.rules },

                    reset() {
                        this.s.reset();
                        $store.ui.notifySuccess($t('settings.preferences.resetDone'));
                    },
                };
            }
        </script>
    @endpush
</x-layout.app>
