@props(['activo' => 'oficina'])

{{-- Pestañas de Expedientes: registro de mi oficina (SiRex) / consulta de todo el IPV (sistema viejo). --}}
@php
    $tabs = [
        'oficina' => ['label' => 'Mi oficina', 'ruta' => route('expedientes'), 'detalle' => 'Registro de ingresos y egresos'],
        'ipv' => ['label' => 'Expedientes IPV', 'ruta' => route('expedientes.ipv'), 'detalle' => 'Consulta y pases de todo el instituto'],
        'ingresos' => ['label' => 'Ingresos a oficina', 'ruta' => route('expedientes.ipv.ingresos'), 'detalle' => 'Qué entró a una oficina en un período'],
    ];
@endphp

<nav class="flex gap-1 rounded-xl border border-ipv-blue/15 bg-white/50 p-1 dark:border-white/10 dark:bg-white/[0.04]"
    aria-label="Secciones de expedientes">
    @foreach ($tabs as $clave => $tab)
        <a href="{{ $tab['ruta'] }}" wire:navigate.hover wire:key="tab-{{ $clave }}"
            @if ($activo === $clave) aria-current="page" @endif
            class="group flex flex-1 flex-col rounded-lg px-4 py-2 text-left transition-all duration-200 sm:flex-none
                   {{ $activo === $clave
                       ? 'bg-ipv-blue text-white shadow-sm'
                       : 'text-ipv-ink/70 hover:-translate-y-px hover:bg-white/80 hover:text-ipv-ink dark:text-ipv-ink-dark/70 dark:hover:bg-white/[0.08] dark:hover:text-ipv-ink-dark' }}">
            <span class="text-sm font-semibold">{{ $tab['label'] }}</span>
            <span class="hidden text-[11px] sm:block {{ $activo === $clave ? 'text-white/75' : 'text-ipv-ink/45 dark:text-ipv-ink-dark/45' }}">
                {{ $tab['detalle'] }}
            </span>
        </a>
    @endforeach
</nav>
