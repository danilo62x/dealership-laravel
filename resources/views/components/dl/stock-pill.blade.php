@props(['state' => "''"])
{{-- Situação do veículo no estoque — é ela que decide a vitrine.
     Porte de StockPill em components/dl/StockPill.tsx. $state é expressão Alpine. --}}
@php
    $colorExpr = "({ disponivel: 'success', reservado: 'warning', vendido: 'gray', preparacao: 'brand' })[".$state.']';
@endphp
<x-ui.badge :color="$colorExpr" dot>
    <span x-text="$t('status.stock.' + ({!! $state !!}))"></span>
</x-ui.badge>
