{{--
    Texto que sale de un dato (hoy: un monto o número en letras con num2letras). No se edita: sigue al dato.
    En el editor se pide al servidor (CrearResolucion::enLetras) para que dé exactamente lo mismo que al guardar.
--}}
@aware(['editable' => false, 'datos' => [], 'marcadores' => false])
@props(['campo', 'placeholder' => ''])
@php
    $valor = trim((string) ($datos[$campo] ?? ''));
    $texto = $valor === '' ? '' : num2letras($valor);
@endphp
@if ($editable)
    <span class="campo-derivado" title="Se completa solo a partir del número"
        x-data="{ texto: @js($texto) }"
        x-init="$watch(&quot;$wire.datos['{{ $campo }}']&quot;, Alpine.debounce((v) => $wire.enLetras(v ?? '').then((t) => texto = t), 350))"
        x-text="texto || @js($placeholder)">{{ $texto ?: $placeholder }}</span>@elseif ($texto === '' && $marcadores)<span class="marcador-dato">{{ $placeholder }}</span>@else<span>{{ $texto }}</span>@endif
