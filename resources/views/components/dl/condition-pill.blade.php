@props(['condition' => "''"])
{{-- Estado de conservação da troca — porte de ConditionPill em StockPill.tsx. --}}
@php
    $colorExpr = "({ excelente: 'success', bom: 'brand', regular: 'warning', ruim: 'error' })[".$condition.']';
@endphp
<x-ui.badge :color="$colorExpr">
    <span x-text="$t('status.condition.' + ({!! $condition !!}))"></span>
</x-ui.badge>
