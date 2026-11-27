@props(['stage' => "''"])
{{-- Etapa do funil — porte de DealStagePill em components/dl/StockPill.tsx. --}}
@php
    $colorExpr = "({ contato: 'gray', proposta: 'brand', negociacao: 'warning', aprovada: 'success', perdida: 'error' })[".$stage.']';
@endphp
<x-ui.badge :color="$colorExpr" dot>
    <span x-text="$t('status.deal.' + ({!! $stage !!}))"></span>
</x-ui.badge>
