@props([
    'model' => '',
    'label' => null,
    'change' => null,
    'id' => null,
])
{{-- Select de filtro — $model é a expressão do x-model.
     Porte de FilterSelect em components/ui/FilterBar.tsx. --}}
@php $selectId = $id ?: 'filter-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $model)); @endphp
<label class="flex min-w-0 items-center gap-2 text-sm text-muted" for="{{ $selectId }}">
    @if ($label)
        <span class="whitespace-nowrap" x-text="{{ $label }}"></span>
    @endif
    <select id="{{ $selectId }}" x-model="{{ $model }}" @if ($change) @change="{{ $change }}" @endif
        class="focus-ring min-w-0 max-w-full rounded-lg border border-themed bg-transparent px-3 py-2 text-sm text-body">
        {{ $slot }}
    </select>
</label>
