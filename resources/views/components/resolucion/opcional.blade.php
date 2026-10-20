{{--
    Parte del texto que solo va si el dato 'si' tiene valor (p. ej. "y GOMEZ ANA DNI N° ..." del cotitular);
    'espacio': la parte arranca con un espacio (Blade recorta los espacios de los bordes del slot y de la salida del componente: si va pegada a un dato, hay que pedirlo);
    con 'vacio', al revés: solo va si el dato está vacío (el texto estándar que un dato libre reemplaza).
    En el editor se muestra siempre (atenuada cuando no va a salir) para poder completarla.
--}}
@aware(['editable' => false, 'datos' => []])
@props(['si', 'vacio' => false, 'espacio' => false])
@php
    $tieneDato = trim((string) ($datos[$si] ?? '')) !== '';
@endphp
@if ($editable)
    <span class="campo-opcional" x-bind:class="{ 'campo-opcional-vacio': {{ $vacio ? '' : '!' }} $wire.datos['{{ $si }}'] }" title="{{ $vacio ? 'Texto estándar: no sale si completás el texto propio' : 'Opcional: si queda vacío no sale en la resolución' }}">{!! $espacio ? '&#32;' : '' !!}{{ $slot }}</span>@elseif ($tieneDato !== $vacio){!! $espacio ? '&#32;' : '' !!}{{ $slot }}@endif
