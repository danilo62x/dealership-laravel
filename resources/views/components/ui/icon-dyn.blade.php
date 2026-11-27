@props([
    'expr' => "''",
    'names' => [],
    'size' => 20,
    'strokeWidth' => 1.8,
    'iconClass' => '',
])
{{-- Ícone escolhido por uma expressão do Alpine.
     Os <template x-if> ficam FORA do <svg> de propósito: dentro de <svg> o
     parser do navegador não cria o `content` do template no namespace certo e o
     Alpine estoura no cloneNode.
     A expressão vai entre parênteses porque `a ? b : c === d` em JS é
     `a ? b : (c === d)` — sem eles um ternário acenderia os dois ícones. --}}
@foreach ($names as $iconName)
    <template x-if="({{ $expr }}) === '{{ $iconName }}'">
        <span class="contents"><x-icon :name="$iconName" :size="$size" :strokeWidth="$strokeWidth" :class="$iconClass" /></span>
    </template>
@endforeach
