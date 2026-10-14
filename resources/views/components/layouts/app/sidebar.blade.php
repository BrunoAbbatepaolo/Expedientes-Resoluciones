<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

@php
    $usuario = auth()->user();
    $menuRrhh = \App\Services\Rrhh\MenuRrhh::para($usuario);

    // Íconos (trazos SVG propios de este archivo, no vienen del usuario).
    $iconos = [
        'dashboard' => '<path d="M3 11.5 12 4l9 7.5" /><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />',
        'expedientes' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />',
        'resoluciones' => '<path d="M9 12.75 11.25 15 15 9.75" /><circle cx="12" cy="12" r="9" />',
        'rrhh' => '<circle cx="9" cy="8" r="3.5" /><path d="M2.5 20a6.5 6.5 0 0 1 13 0" /><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5" />',
        'oficinas' => '<rect x="4" y="3" width="16" height="18" rx="1" /><path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1" />',
        'usuarios' => '<circle cx="12" cy="8" r="3.5" /><path d="M5 20a7 7 0 0 1 14 0" />',
        'admin' => '<path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-3Z" /><path d="m9 12 2 2 4-4" />',
        'entrantes' => '<path d="M22 12h-6l-2 3h-4l-2-3H2" /><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z" />',
    ];

    $expedientesActivo = request()->routeIs('expedientes*');
    $rrhhActivo = request()->routeIs('rrhh.*');
@endphp

<body class="min-h-screen overflow-x-hidden text-ipv-ink dark:text-ipv-ink-dark lg:flex"
    x-data="{
        sidebarOpen: false,
        {{-- Barra de íconos (solo desde 1024px). Sin preferencia guardada: completa desde 1280px, contraída antes. --}}
        rail: (() => {
            try { const v = localStorage.getItem('sirex-sidebar'); if (v) return v === 'rail'; } catch (e) {}
            return window.innerWidth < 1280;
        })(),
        {{-- La clase 'dark' del <html> la pone el script de partials/head; acá solo se refleja. --}}
        darkMode: document.documentElement.classList.contains('dark'),
        inicioToque: null,
        escritorio() { return window.innerWidth >= 1024; },
        alternarRail() {
            this.rail = !this.rail;
            try { localStorage.setItem('sirex-sidebar', this.rail ? 'rail' : 'full'); } catch (e) {}
        },
        {{-- En la barra de íconos, tocar un grupo la expande para mostrar sus pantallas. --}}
        expandirSiHaceFalta() {
            if (this.escritorio() && this.rail) { this.rail = false; return true; }
            return false;
        },
    }"
    x-effect="document.body.style.overflow = (sidebarOpen && !escritorio()) ? 'hidden' : ''"
    x-on:theme-changed.window="darkMode = $event.detail.dark"
    x-on:keydown.escape.window="sidebarOpen = false"
    x-on:resize.window.debounce.150ms="if (escritorio()) sidebarOpen = false">

    <div class="fixed inset-0 z-[-1] sirex-shell" aria-hidden="true"></div>

    <!-- Fondo del cajón en celulares y tablets -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-[#001428]/50 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    <aside id="menu-lateral"
        class="sirex-glass sirex-aside fixed left-0 top-0 z-50 flex h-dvh shrink-0 flex-col border-r border-ipv-blue/12 p-3 dark:border-white/10 lg:sticky lg:top-0 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        :data-rail="rail ? 'on' : 'off'"
        x-on:focusin="if ($event.target.id === 'busqueda-global') expandirSiHaceFalta()"
        x-on:touchstart.passive="inicioToque = $event.touches[0].clientX"
        x-on:touchend.passive="if (inicioToque !== null && inicioToque - $event.changedTouches[0].clientX > 70) sidebarOpen = false; inicioToque = null"
        aria-label="Menú principal">

        <!-- Marca -->
        <div class="sirex-marca flex items-center gap-2.5 px-2 pb-3 pt-1.5">
            <a href="{{ route('dashboard') }}" wire:navigate.hover class="flex min-w-0 flex-1 items-center gap-2.5" title="SiRex · Gestión de expedientes">
                <img src="{{ asset('images/logo-ipv-simbolo.png') }}" alt="" class="size-10 shrink-0 drop-shadow-[0_3px_8px_rgba(255,184,26,0.4)]" />
                <span class="sirex-marca-texto min-w-0">
                    <span class="block truncate text-[17px] font-bold leading-tight tracking-tight">SiRex</span>
                    <span class="block truncate text-[11px] text-ipv-ink/55 dark:text-ipv-ink-dark/50">Gestión de expedientes</span>
                </span>
            </a>
            <button @click="sidebarOpen = false" type="button" aria-label="Cerrar menú"
                class="rounded-lg p-1.5 text-ipv-ink/45 hover:bg-black/[0.05] dark:text-ipv-ink-dark/45 dark:hover:bg-white/10 lg:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M6 6l12 12M6 18 18 6" />
                </svg>
            </button>
        </div>

        <!-- Búsqueda global (Ctrl+K o /): expedientes del IPV, registros de mi oficina y resoluciones -->
        <livewire:busqueda-global />

        <nav class="-mx-1 flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto overflow-x-hidden px-2 pb-2 [scrollbar-width:thin]" aria-label="Secciones">

            <div class="sirex-nav-seccion"><span>General</span></div>

            <a href="{{ route('dashboard') }}" wire:navigate.hover title="{{ __('Dashboard') }}"
                @if (request()->routeIs('dashboard')) aria-current="page" @endif
                class="sirex-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['dashboard'] !!}</svg></span>
                <span class="sirex-nav-texto">{{ __('Dashboard') }}</span>
            </a>

            <div class="sirex-nav-seccion"><span>Gestión</span></div>

            @if ($usuario?->permiso('expediente_ver'))
                <div x-data="{ open: {{ $expedientesActivo ? 'true' : 'false' }} }">
                    <button type="button" title="{{ __('Expedientes') }}"
                        @click="if (expandirSiHaceFalta()) open = true; else open = !open"
                        :aria-expanded="open.toString()"
                        class="sirex-nav-item {{ $expedientesActivo ? 'has-active' : '' }}" :class="{ 'is-open': open }">
                        <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['expedientes'] !!}</svg></span>
                        <span class="sirex-nav-texto">{{ __('Expedientes') }}</span>
                        <svg class="sirex-nav-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-collapse.duration.220ms class="sirex-subnav">
                        @foreach (['expedientes' => ['Mi oficina', ['expedientes', 'expedientes.ingresados', 'expedientes.egresados', 'expedientes.detalle']], 'expedientes.ipv' => ['Expedientes IPV', ['expedientes.ipv', 'expedientes.ipv.ficha']], 'expedientes.ipv.ingresos' => ['Ingresos a oficina', ['expedientes.ipv.ingresos*']], 'expedientes.ipv.pases' => ['Pases emitidos', ['expedientes.ipv.pases*']]] as $ruta => [$etiqueta, $activas])
                            <a href="{{ route($ruta) }}" wire:navigate.hover
                                @if (request()->routeIs(...$activas)) aria-current="page" @endif
                                class="sirex-subitem">{{ $etiqueta }}</a>
                        @endforeach
                        @if (config('sirex.pases_internos'))
                            <a href="{{ route('expedientes.entrantes') }}" wire:navigate.hover class="sirex-subitem sirex-subitem-aviso">
                                <span class="flex items-center gap-1.5">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['entrantes'] !!}</svg>
                                    Entrantes
                                </span>
                                <livewire:entrantes-badge />
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($usuario?->permiso('resolucion_ver'))
                <a href="{{ route('resoluciones') }}" wire:navigate.hover title="{{ __('Resoluciones') }}"
                    @if (request()->routeIs('resoluciones')) aria-current="page" @endif
                    class="sirex-nav-item {{ request()->routeIs('resoluciones') ? 'is-active' : '' }}">
                    <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['resoluciones'] !!}</svg></span>
                    <span class="sirex-nav-texto">{{ __('Resoluciones') }}</span>
                </a>
            @endif

            @if ($menuRrhh !== [])
                <div x-data="{ open: {{ $rrhhActivo ? 'true' : 'false' }} }">
                    <button type="button" title="{{ __('RRHH') }}"
                        @click="if (expandirSiHaceFalta()) open = true; else open = !open"
                        :aria-expanded="open.toString()"
                        class="sirex-nav-item {{ $rrhhActivo ? 'has-active' : '' }}" :class="{ 'is-open': open }">
                        <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['rrhh'] !!}</svg></span>
                        <span class="sirex-nav-texto">{{ __('RRHH') }}</span>
                        <svg class="sirex-nav-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-collapse.duration.220ms class="sirex-subnav">
                        @foreach ($menuRrhh as $item)
                            @php($activa = request()->routeIs($item['ruta'] === 'rrhh.personal' ? 'rrhh.personal*' : $item['ruta']) || ($item['ruta'] === 'rrhh.personal' && request()->routeIs('rrhh.ficha')))
                            <a href="{{ route($item['ruta']) }}" wire:navigate.hover
                                @if ($activa) aria-current="page" @endif
                                class="sirex-subitem">
                                <span>{{ $item['etiqueta'] }}</span>
                                @if (($item['contador'] ?? 0) > 0)
                                    <span class="sirex-contador">{{ $item['contador'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="{{ route('oficinas') }}" wire:navigate.hover title="{{ __('Oficinas') }}"
                @if (request()->routeIs('oficinas')) aria-current="page" @endif
                class="sirex-nav-item {{ request()->routeIs('oficinas') ? 'is-active' : '' }}">
                <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['oficinas'] !!}</svg></span>
                <span class="sirex-nav-texto">{{ __('Oficinas') }}</span>
            </a>

            @if ($usuario?->permiso('lista_usuario_ver'))
            <a href="{{ route('listausuarios') }}" wire:navigate.hover title="{{ __('Usuarios') }}"
                @if (request()->routeIs('listausuarios')) aria-current="page" @endif
                class="sirex-nav-item {{ request()->routeIs('listausuarios') ? 'is-active' : '' }}">
                <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['usuarios'] !!}</svg></span>
                <span class="sirex-nav-texto">{{ __('Usuarios') }}</span>
            </a>
            @endif

            @if ($usuario?->permiso('admin_ver'))
            <a href="{{ route('admin') }}" wire:navigate.hover title="{{ __('Administración') }}"
                @if (request()->routeIs('admin')) aria-current="page" @endif
                class="sirex-nav-item {{ request()->routeIs('admin') ? 'is-active' : '' }}">
                <span class="sirex-nav-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $iconos['admin'] !!}</svg></span>
                <span class="sirex-nav-texto">{{ __('Administración') }}</span>
            </a>
            @endif
        </nav>

        <!-- Pie: contraer menú (solo escritorio) y modo oscuro -->
        <div class="sirex-aside-pie space-y-0.5 pt-2">
            <button type="button" @click="alternarRail()" class="sirex-nav-item hidden lg:flex"
                :title="rail ? 'Expandir menú' : 'Contraer menú'" :aria-label="rail ? 'Expandir menú' : 'Contraer menú'" :aria-expanded="(!rail).toString()" aria-controls="menu-lateral">
                <span class="sirex-nav-icono">
                    <svg class="sirex-rail-flecha transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11 17-5-5 5-5M18 17l-5-5 5-5" /></svg>
                </span>
                <span class="sirex-nav-texto" x-text="rail ? 'Expandir menú' : 'Contraer menú'">Contraer menú</span>
            </button>

            <button @click="aplicarTema(darkMode ? 'light' : 'dark')" type="button" class="sirex-nav-item"
                role="switch" :aria-checked="darkMode.toString()" aria-label="Modo oscuro" title="Modo oscuro">
                <span class="sirex-nav-icono">
                    <svg x-show="darkMode" x-cloak class="sirex-tema-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                    </svg>
                    <svg x-show="!darkMode" x-cloak class="sirex-tema-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                    </svg>
                </span>
                <span class="sirex-nav-texto">Modo oscuro</span>
                <span class="sirex-ocultar-rail flex h-[21px] w-[38px] shrink-0 items-center rounded-full bg-gray-300 p-[2px] transition-colors dark:bg-ipv-blue" aria-hidden="true">
                    <span class="size-[17px] rounded-full bg-white shadow transition-transform duration-200"
                        :class="darkMode ? 'translate-x-[17px]' : 'translate-x-0'"></span>
                </span>
            </button>
        </div>

        <!-- Perfil -->
        <div x-data="{ open: false }" class="relative pb-1 pt-2">
            <button @click="open = !open" @click.outside="open = false" type="button" :aria-expanded="open.toString()"
                title="{{ $usuario->nombre }}"
                class="sirex-perfil group flex w-full items-center gap-3 rounded-[14px] border border-ipv-blue/15 bg-white/40 px-2.5 py-2 transition-all hover:border-ipv-blue/30 hover:bg-white/70 hover:shadow-sm dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-white/20 dark:hover:bg-white/[0.08]">
                <span class="relative flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-[11px] bg-ipv-gold text-[13px] font-bold text-ipv-gold-ink transition-transform duration-300 group-hover:scale-105 dark:text-ipv-gold-ink-dark">
                    @if ($usuario->profile_photo_path && \Illuminate\Support\Facades\Storage::exists($usuario->profile_photo_path))
                        <img src="{{ Storage::url($usuario->profile_photo_path) }}" alt="{{ $usuario->nombre }}" class="h-full w-full object-cover">
                    @else
                        {{ $usuario->initials() }}
                    @endif
                    <span class="absolute bottom-0 right-0 size-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-[#141c26]" aria-hidden="true"></span>
                </span>
                <div class="sirex-ocultar-rail min-w-0 flex-1 text-left">
                    <div class="truncate text-[13.5px] font-semibold tracking-tight text-ipv-ink dark:text-ipv-ink-dark">{{ $usuario->nombre }}</div>
                    <div class="truncate text-[11px] font-medium text-ipv-ink/60 dark:text-ipv-ink-dark/50">
                        {{ $usuario->oficinaAsignada()?->nombre ?? 'Sin oficina' }}
                    </div>
                </div>
                <svg class="sirex-ocultar-rail size-4 shrink-0 text-ipv-ink/40 transition-transform duration-200 group-hover:text-ipv-ink/70 dark:text-ipv-ink-dark/45 dark:group-hover:text-ipv-ink-dark/70" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="open" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="absolute bottom-full left-0 z-50 mb-3 w-[240px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-[14px] border border-ipv-blue/15 bg-white shadow-xl dark:border-white/10 dark:bg-[#1a2332]">

                <div class="border-b border-ipv-blue/10 px-4 py-3 dark:border-white/10">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-ipv-ink/50 dark:text-ipv-ink-dark/50">Sesión iniciada como</p>
                    <p class="mt-0.5 truncate text-[13px] font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $usuario->email }}</p>
                </div>

                <div class="p-1.5">
                    <a href="{{ route('settings.profile') }}" wire:navigate.hover
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ipv-ink/80 transition-colors hover:bg-black/[0.04] dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                        <svg class="size-[18px] text-ipv-ink/50 dark:text-ipv-ink-dark/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ __('Settings') }}
                    </a>
                </div>

                <div class="border-t border-ipv-blue/10 p-1.5 dark:border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-ipv-ink/80 transition-colors hover:bg-black/[0.04] dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                            <svg class="size-[18px] text-ipv-magenta/70 dark:text-ipv-magenta-light/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="text-ipv-magenta dark:text-ipv-magenta-light">{{ __('Log Out') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Barra superior en celulares y tablets: marca a la izquierda, menú a la derecha -->
    <header class="sirex-glass sirex-topbar sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-ipv-blue/12 px-3 py-2 dark:border-white/10 lg:hidden">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2.5">
            <img src="{{ asset('images/logo-ipv-simbolo.png') }}" alt="" class="size-8 shrink-0" />
            <span class="truncate text-[15px] font-bold tracking-tight">SiRex</span>
        </a>
        <button @click="sidebarOpen = true" type="button" aria-label="Abrir menú" aria-controls="menu-lateral" :aria-expanded="sidebarOpen.toString()"
            class="rounded-xl p-2 text-ipv-blue transition-colors hover:bg-ipv-blue/10 dark:text-ipv-gold-light dark:hover:bg-white/10">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>
    </header>

    <main class="sirex-pagina min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </main>
</body>

</html>
