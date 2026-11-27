@props(['status' => "''"])
{{-- Situação do test-drive — porte de DriveStatusPill em StockPill.tsx. --}}
@php
    $colorExpr = "({ agendado: 'brand', realizado: 'success', cancelado: 'gray', faltou: 'error' })[".$status.']';
@endphp
<x-ui.badge :color="$colorExpr" dot>
    <span x-text="$t('status.drive.' + ({!! $status !!}))"></span>
</x-ui.badge>
