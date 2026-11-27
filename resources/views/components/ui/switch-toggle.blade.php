@props([
    'checked' => 'false',
    'change' => '',
    'size' => 'md',
])
{{-- $checked é uma expressão Alpine; $change é o corpo do @click.
     Porte de components/ui/SwitchToggle.tsx. --}}
@php
    $track = $size === 'sm' ? 'h-5 w-9' : 'h-6 w-11';
    $thumb = $size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    $shift = $size === 'sm' ? 'translate-x-4' : 'translate-x-5';
@endphp
<button type="button" role="switch" :aria-checked="{{ $checked }}"
    @click="{{ $change }}"
    class="relative inline-flex shrink-0 items-center rounded-full transition {{ $track }}"
    :class="({{ $checked }}) ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700'">
    <span class="inline-block transform rounded-full bg-white shadow-theme-xs transition {{ $thumb }}"
        :class="({{ $checked }}) ? '{{ $shift }}' : 'translate-x-1'"></span>
</button>
