@props(['items' => []])
{{-- Cada item: ['label' => texto estático, 'expr' => expressão Alpine, 'href' => url, 'icon' => nome].
     Porte de components/ui/Breadcrumb.tsx. --}}
<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @foreach ($items as $i => $item)
            @php $last = $i === count($items) - 1; @endphp
            <li class="flex min-w-0 items-center gap-1.5">
                @if (! empty($item['href']) && ! $last)
                    <a href="{{ $item['href'] }}"
                        class="inline-flex min-w-0 items-center gap-1.5 text-muted transition hover:text-brand-600 dark:hover:text-brand-400">
                        @isset($item['icon'])<x-icon :name="$item['icon']" :size="15" />@endisset
                        <span class="truncate" @isset($item['expr']) x-text="{{ $item['expr'] }}" @endisset>{{ $item['label'] ?? '' }}</span>
                    </a>
                @else
                    <span @if ($last) aria-current="page" @endif
                        class="inline-flex min-w-0 items-center gap-1.5 {{ $last ? 'font-medium text-heading' : 'text-muted' }}">
                        @isset($item['icon'])<x-icon :name="$item['icon']" :size="15" />@endisset
                        <span class="truncate" @isset($item['expr']) x-text="{{ $item['expr'] }}" @endisset>{{ $item['label'] ?? '' }}</span>
                    </span>
                @endif
                @unless ($last)
                    <x-icon name="chevron-right" :size="14" class="text-gray-400" />
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
