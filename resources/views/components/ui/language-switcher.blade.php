{{-- Troca de idioma — porte de components/ui/LanguageSwitcher.tsx. --}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" aria-label="Language" :title="$store.ui.currentLanguage.label"
        class="flex h-10 w-10 items-center justify-center rounded-full border border-themed text-base hover:bg-gray-50 dark:hover:bg-gray-800"
        @click="open = !open">
        <span aria-hidden x-text="$store.ui.currentLanguage.flag"></span>
    </button>
    <div x-show="open" x-cloak @click="open = false"
        class="surface-card absolute right-0 mt-3 w-44 rounded-xl border py-1 shadow-theme-lg z-50">
        <template x-for="lang in $store.ui.languages" :key="lang.code">
            <button type="button" @click="$store.ui.setLang(lang.code)"
                class="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                :class="lang.code === $store.ui.lang ? 'font-medium text-brand-600' : 'text-body'">
                <span aria-hidden x-text="lang.flag"></span>
                <span x-text="lang.label"></span>
                <template x-if="lang.code === $store.ui.lang">
                    <span class="ml-auto"><x-icon name="check" :size="14" /></span>
                </template>
            </button>
        </template>
    </div>
</div>
