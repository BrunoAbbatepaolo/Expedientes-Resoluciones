@props(['estado'])

{{-- Chip de estado (EstadoDia o EstadoPedido): ícono animado + etiqueta. --}}
@php
    $enEspera = in_array($estado->value, ['pendiente', 'tarde', 'pendiente_jefe', 'aprobado_jefe'], true);
    $logrado = in_array($estado->value, ['presente', 'confirmado', 'justificado'], true);
@endphp
<span {{ $attributes->merge(['class' => 'sirex-chip inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium '.$estado->clases()]) }}>
    @if ($estado->icono() !== '')
        <svg class="sirex-chip-icono {{ $enEspera ? 'sirex-chip-espera' : '' }} {{ $logrado ? 'sirex-chip-logrado' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="{{ $estado->icono() }}" />
        </svg>
    @endif
    {{ $estado->etiqueta() }}
</span>
