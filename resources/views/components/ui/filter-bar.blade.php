@props([
    'search' => null,
    'searchPlaceholder' => "''",
])
{{-- Barra de filtros com busca opcional. $search é uma expressão Alpine usada
     como x-model. Porte de components/ui/FilterBar.tsx. --}}
<div class="surface-card mb-5 flex flex-col gap-3 rounded-xl border p-4 lg:flex-row lg:items-center">
    @if ($search)
        <div class="relative w-full lg:max-w-xs">
            <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted">
                <x-icon name="search" :size="16" />
            </span>
            <input id="filter-search" x-model="{{ $search }}" :placeholder="{{ $searchPlaceholder }}"
                class="focus-ring w-full rounded-lg border border-themed bg-transparent py-2 pr-3 pl-9 text-sm text-body" />
        </div>
    @endif
    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-3">{{ $slot }}</div>
    @endif
    @isset($action)
        <div class="lg:ml-auto">{{ $action }}</div>
    @endisset
</div>
