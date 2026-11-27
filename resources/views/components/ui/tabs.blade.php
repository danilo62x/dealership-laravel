@props([
    'tabs' => [],
    'model' => 'tab',
])
{{-- Barra de abas. Cada item: ['label' => expressão Alpine, 'icon' => nome].
     O painel de cada aba fica na página, com x-show="{{ $model }} === i".
     Porte de components/ui/Tabs.tsx. --}}
<div class="no-scrollbar flex gap-1 overflow-x-auto border-b border-themed" role="tablist">
    @foreach ($tabs as $i => $tabItem)
        <button type="button" role="tab" :aria-selected="{{ $model }} === {{ $i }}" @click="{{ $model }} = {{ $i }}"
            class="-mb-px inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition"
            :class="{{ $model }} === {{ $i }}
                ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                : 'border-transparent text-muted hover:border-gray-300 hover:text-body dark:hover:border-gray-700'">
            @isset($tabItem['icon'])<x-icon :name="$tabItem['icon']" :size="16" />@endisset
            <span x-text="{{ $tabItem['label'] }}"></span>
        </button>
    @endforeach
</div>
