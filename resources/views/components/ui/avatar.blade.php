@props([
    'src' => 'null',
    'name' => "''",
    'size' => 'md',
])
{{-- $src e $name são expressões Alpine. Porte de components/ui/Avatar.tsx. --}}
@php
    $sizeMap = [
        'xs' => 'h-7 w-7 text-[10px]',
        'sm' => 'h-9 w-9 text-xs',
        'md' => 'h-11 w-11 text-sm',
        'lg' => 'h-14 w-14 text-base',
        'xl' => 'h-16 w-16 text-lg',
    ];
    $dim = $sizeMap[$size] ?? $sizeMap['md'];
@endphp
<span class="relative inline-flex shrink-0"
    x-data="{
        get src() { return {{ $src }} },
        get name() { return {{ $name }} },
        get initials() {
            return String(this.name || '').trim().split(/\s+/).slice(0, 2).map((p) => (p[0] || '').toUpperCase()).join('') || '?'
        },
    }">
    <template x-if="src">
        <img :src="src" :alt="name || ''" class="rounded-full object-cover {{ $dim }}" />
    </template>
    <template x-if="!src">
        <span class="flex items-center justify-center rounded-full bg-brand-500 font-bold text-white {{ $dim }}" x-text="initials"></span>
    </template>
</span>
