{{-- Ojo para mostrar/ocultar una contraseña: usa la variable `ver` del x-data que lo contiene. --}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <g x-show="! ver">
        <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
        <circle cx="12" cy="12" r="3" />
    </g>
    <g x-show="ver" x-cloak>
        <path d="M3 3l18 18M10.6 6a9.6 9.6 0 0 1 1.4-.1c6 0 9.5 6.1 9.5 6.1a16 16 0 0 1-2.9 3.5M6.4 7.5A16 16 0 0 0 2.5 12S6 18.5 12 18.5a9.4 9.4 0 0 0 4-.9" />
        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
    </g>
</svg>
