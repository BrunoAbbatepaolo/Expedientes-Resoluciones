@props([
    'variante' => 'eliminar', // eliminar: la etiqueta cae en un tacho · pase: un avión de papel se va volando
    'accion',                 // expresión JS que se ejecuta cuando termina la animación, ej. $wire.borrar()
    'etiqueta' => 'Eliminar',
    'listo' => 'Listo',
    'confirmar' => null,      // texto para confirm() antes de empezar
    'si' => null,             // condición JS: si da falso se ejecuta la acción sin animar (que valide el servidor)
])

{{-- Botón con animación en tres fases (data-fase): reposo → corre → listo (verde) → se ejecuta la acción.
     Con "menos movimiento" activado en el sistema se ejecuta directo. --}}
@php
    $clase = $variante === 'pase' ? 'sirex-boton-primario' : 'sirex-boton-peligro';
@endphp
<button type="button" {{ $attributes->class(['sirex-boton', 'sirex-anim', $clase]) }}
    x-data="{
        fase: 'reposo',
        ir() {
            if (this.fase !== 'reposo') return;
            @if ($confirmar) if (! confirm('{{ addslashes($confirmar) }}')) return; @endif
            const animar = {{ $si ? '('.$si.')' : 'true' }};
            if (! animar || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { {!! $accion !!}; return; }
            this.fase = 'corre';
            setTimeout(() => {
                this.fase = 'listo';
                setTimeout(() => { {!! $accion !!}; setTimeout(() => this.fase = 'reposo', 600); }, 600);
            }, 900);
        },
    }"
    :data-fase="fase" x-on:click="ir()">
    <span class="sirex-anim-pila">
        <span class="sirex-anim-texto">{{ $etiqueta }}</span>

        <span class="sirex-anim-icono" aria-hidden="true">
            @if ($variante === 'pase')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="sirex-vuela" d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z" />
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l1 11a2 2 0 002 2h6a2 2 0 002-2l1-11" />
                    <path d="M10 12.5v5M14 12.5v5" />
                    <g class="sirex-tapa"><path d="M4 6h16M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" /></g>
                </svg>
            @endif
        </span>

        <span class="sirex-anim-ok" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path class="sirex-trazo" d="M5 13l4 4L19 7" /></svg>
            {{ $listo }}
        </span>
    </span>
</button>
