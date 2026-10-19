{{--
    Un dato de la resolución dentro del documento. Todos los <x-resolucion.campo> con el mismo 'campo' leen y escriben
    $wire.datos[campo]: si el dato aparece varias veces, al tipear en uno se actualizan todos.
    'predeterminado' se usa si el dato está vacío (y se precarga en el input). 'formato' solo afecta el texto final.
--}}
@aware(['editable' => false, 'datos' => []])
@props([
    'campo',
    'placeholder' => '',
    'predeterminado' => '',
    'tipo' => 'text',
    'formato' => null,
    'maxlength' => null,
])
@php
    $valor = trim((string) ($datos[$campo] ?? ''));
    if ($valor === '') {
        $valor = $predeterminado;
    }
    $modelo = "\$wire.datos['{$campo}']";
    $minimo = max(mb_strlen($placeholder), 3);
@endphp
@if ($editable)
    <input type="{{ $tipo }}"
        x-model="{{ $modelo }}"
        @if ($predeterminado !== '') x-init="if (! {{ $modelo }}) {{ $modelo }} = @js($predeterminado)" @endif
        @if ($tipo !== 'date') x-bind:style="'width:' + Math.ceil(Math.max(String({{ $modelo }} ?? '').length, {{ $minimo }}) * 1.3 + 1) + 'ch'" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        placeholder="{{ $placeholder }}"
        data-campo="{{ $campo }}"
        aria-label="{{ $placeholder ?: $campo }}"
        autocomplete="off"
        {{ $attributes->class('campo-plantilla') }} />{{-- sin salto antes de @else/@endif: sumaría un espacio en el texto ("(123 )") --}}@else
    <span {{ $attributes }}>{{ match ($formato) {
        'fecha' => formatearFecha($valor),
        'fecha_larga' => formatearFechaLarga($valor),
        'moneda' => formatearMoneda($valor),
        default => $valor,
    } }}</span>@endif
