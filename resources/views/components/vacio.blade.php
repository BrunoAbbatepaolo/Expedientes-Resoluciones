@props(['icono' => 'caja'])

{{-- Pantalla vacía: ilustración liviana que flota, con un detalle que entra con rebote, y el mensaje. --}}
<div {{ $attributes->class(['sirex-vacio']) }}>
    <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @switch($icono)
            @case('buscar')
                <circle cx="28" cy="28" r="15" />
                <path d="M39 39l14 14" />
                <path class="sirex-vacio-extra" d="M22 28h12" />
                @break
            @case('persona')
                <circle cx="32" cy="22" r="9" />
                <path d="M14 54a18 18 0 0136 0" />
                <path class="sirex-vacio-extra" d="M46 14l4 4m0-4l-4 4" />
                @break
            @case('calendario')
                <rect x="10" y="14" width="44" height="40" rx="6" />
                <path d="M10 26h44M22 8v10M42 8v10" />
                <path class="sirex-vacio-extra" d="M24 40h16" />
                @break
            @case('reloj')
                <circle cx="32" cy="32" r="22" />
                <path class="sirex-vacio-extra" d="M32 20v13l8 5" />
                @break
            @default
                <path d="M8 24l6-14h36l6 14" />
                <path d="M8 24h16l2 6h12l2-6h16v26a4 4 0 01-4 4H12a4 4 0 01-4-4V24z" />
                <path class="sirex-vacio-extra" d="M26 42h12" />
        @endswitch
    </svg>
    <div>{{ $slot }}</div>
</div>
