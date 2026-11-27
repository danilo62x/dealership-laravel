@props([
    'title' => "''",
    'subtitle' => null,
    'crumbs' => [],
])
{{-- Cabeçalho das telas da área do criador — porte de components/app/PageHeader.tsx. --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if (count($crumbs) > 0)
            <div class="mb-2"><x-ui.breadcrumb :items="$crumbs" /></div>
        @endif
        <h1 class="text-2xl font-bold text-heading" x-text="{{ $title }}"></h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-muted" x-text="{{ $subtitle }}"></p>
        @endif
    </div>
    @isset($action)
        <div class="shrink-0">{{ $action }}</div>
    @endisset
</div>
