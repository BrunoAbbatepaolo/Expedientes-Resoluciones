<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen overflow-x-hidden text-ipv-ink dark:text-ipv-ink-dark lg:flex" x-data="{ sidebarOpen: false }">
    <div class="fixed inset-0 z-[-1] sirex-shell" aria-hidden="true"></div>

    <!-- Overlay móvil -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-[#001428]/40 backdrop-blur-sm lg:hidden"></div>

    <aside
        class="sirex-glass fixed left-0 top-0 z-50 flex h-screen w-[264px] shrink-0 flex-col gap-1 border-r border-ipv-blue/12 p-3.5 transition-transform duration-200 dark:border-white/10 lg:sticky lg:top-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo -->
        <div class="flex items-center gap-2.5 px-2.5 pb-4 pt-2">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-ipv-gold">
                <svg class="size-[19px] text-ipv-blue-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M6.7 13.8a42 42 0 0 1 10.6 0M6.3 18h11.4m-1.1-4.2a48 48 0 0 1 1.9.2M17.7 18l.2 2.5a1.1 1.1 0 0 1-1.1 1.2H7.2a1.1 1.1 0 0 1-1.1-1.2L6.3 18m11.3 0h1.1a2.25 2.25 0 0 0 2.3-2.25V9.5c0-1.1-.8-2-1.8-2.2M6.3 18H5.3a2.25 2.25 0 0 1-2.3-2.25V9.5c0-1.1.8-2 1.8-2.2m10.5 0a48 48 0 0 0-10.5 0M18 3.4v3.7" />
                </svg>
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate class="min-w-0">
                <div class="truncate text-[16px] font-semibold leading-tight">SiRex</div>
                <div class="truncate text-[11px] text-ipv-ink/55 dark:text-ipv-ink-dark/50">Gestión de expedientes</div>
            </a>
            <button @click="sidebarOpen = false" type="button" class="ml-auto text-ipv-ink/45 dark:text-ipv-ink-dark/45 lg:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M6 6l12 12M6 18 18 6" />
                </svg>
            </button>
        </div>

        <!-- Buscador -->
        <div class="relative mb-3.5">
            <svg class="pointer-events-none absolute left-[11px] top-1/2 size-3.5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3m2-5.2a7.2 7.2 0 1 1-14.4 0 7.2 7.2 0 0 1 14.4 0Z" />
            </svg>
            <input type="text" placeholder="Buscar…"
                class="w-full rounded-full border border-ipv-blue/15 bg-white/70 py-2 pl-[30px] pr-2.5 text-[12.5px] text-ipv-ink placeholder-ipv-ink/40 focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark dark:placeholder-ipv-ink-dark/40" />
        </div>

        <nav class="flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto">

            <div class="px-3 pb-1.5 pt-1 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                General
            </div>

            <a href="{{ route('dashboard') }}" wire:navigate
                class="relative flex items-center gap-2.5 rounded-lg py-2.5 pl-4 pr-3 text-sm font-semibold
                       {{ request()->routeIs('dashboard')
                           ? 'bg-ipv-gold/20 text-[#7a5200] dark:bg-ipv-gold/15 dark:text-ipv-gold-light'
                           : 'font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5' }}">
                @if (request()->routeIs('dashboard'))
                    <span class="absolute inset-y-2 left-1 w-[3px] rounded-full bg-ipv-gold"></span>
                @endif
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11.5 12 4l9 7.5" />
                    <path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />
                </svg>
                {{ __('Dashboard') }}
            </a>

            <div class="px-3 pb-1.5 pt-3 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                Gestión
            </div>

            <!-- @if (auth()->user()->permiso('expediente_ver')) -->
            <div x-data="{ open: {{ request()->routeIs('expedientes*') ? 'true' : 'false' }} }">
                <button @click="open = !open" type="button"
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5">
                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
                    </svg>
                    <span class="flex-1 text-left">{{ __('Expedientes') }}</span>
                    <svg class="size-3.5 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="ml-3.5 space-y-0.5 border-l-2 border-ipv-blue/15 pl-2.5 dark:border-white/15">
                    <a href="{{ route('expedientes') }}" wire:navigate
                        class="block rounded-md px-2.5 py-1.5 text-[13px] text-ipv-ink/65 hover:bg-black/[0.03] dark:text-ipv-ink-dark/65 dark:hover:bg-white/5">
                        Todos
                    </a>
                    <a href="{{ route('expedientes.ingresados') }}" wire:navigate
                        class="block rounded-md px-2.5 py-1.5 text-[13px] text-ipv-ink/65 hover:bg-black/[0.03] dark:text-ipv-ink-dark/65 dark:hover:bg-white/5">
                        Ingresados
                    </a>
                    <a href="{{ route('expedientes.egresados') }}" wire:navigate
                        class="block rounded-md px-2.5 py-1.5 text-[13px] text-ipv-ink/65 hover:bg-black/[0.03] dark:text-ipv-ink-dark/65 dark:hover:bg-white/5">
                        Egresados
                    </a>
                    <a href="{{ route('expedientes.entrantes') }}" wire:navigate
                        class="flex items-center justify-between rounded-md bg-ipv-gold/15 px-2.5 py-1.5 text-[13px] font-semibold text-[#7a5200] dark:text-ipv-gold-light">
                        <span class="flex items-center gap-1.5">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 12h-6l-2 3h-4l-2-3H2" />
                                <path
                                    d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z" />
                            </svg>
                            Entrantes
                        </span>
                        <livewire:entrantes-badge />
                    </a>
                </div>
            </div>
            <!-- @endif -->

            <!-- @if (auth()->user()->permiso('resolucion_ver')) -->
            <a href="{{ route('resoluciones') }}" wire:navigate
                class="relative flex items-center gap-2.5 rounded-lg py-2.5 pl-4 pr-3 text-sm
                       {{ request()->routeIs('resoluciones')
                           ? 'font-semibold bg-ipv-gold/20 text-[#7a5200] dark:bg-ipv-gold/15 dark:text-ipv-gold-light'
                           : 'font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5' }}">
                @if (request()->routeIs('resoluciones'))
                    <span class="absolute inset-y-2 left-1 w-[3px] rounded-full bg-ipv-gold"></span>
                @endif
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12.75 11.25 15 15 9.75" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
                {{ __('Resoluciones') }}
            </a>
            <!-- @endif -->

            <a href="{{ route('oficinas') }}" wire:navigate
                class="relative flex items-center gap-2.5 rounded-lg py-2.5 pl-4 pr-3 text-sm
                       {{ request()->routeIs('oficinas')
                           ? 'font-semibold bg-ipv-gold/20 text-[#7a5200] dark:bg-ipv-gold/15 dark:text-ipv-gold-light'
                           : 'font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5' }}">
                @if (request()->routeIs('oficinas'))
                    <span class="absolute inset-y-2 left-1 w-[3px] rounded-full bg-ipv-gold"></span>
                @endif
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="3" width="16" height="18" rx="1" />
                    <path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1" />
                </svg>
                {{ __('Oficinas') }}
            </a>
            <a href="{{ route('listausuarios') }}" wire:navigate
                class="relative flex items-center gap-2.5 rounded-lg py-2.5 pl-4 pr-3 text-sm
                       {{ request()->routeIs('listausuario')
                           ? 'font-semibold bg-ipv-gold/20 text-[#7a5200] dark:bg-ipv-gold/15 dark:text-ipv-gold-light'
                           : 'font-medium text-ipv-ink/75 hover:bg-black/[0.03] dark:text-ipv-ink-dark/82 dark:hover:bg-white/5' }}">
                @if (request()->routeIs('listausuario'))
                    <span class="absolute inset-y-2 left-1 w-[3px] rounded-full bg-ipv-gold"></span>
                @endif
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="3.5" />
                    <path d="M5 20a7 7 0 0 1 14 0" />
                </svg>
                {{ __('Usuarios') }}
            </a>
        </nav>

        <!-- Switch modo oscuro -->
        <div x-data class="flex items-center justify-between border-t border-ipv-blue/10 px-2.5 py-2.5 dark:border-white/10">
            <span class="flex items-center gap-1.5 text-[13px] font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/82">
                <svg x-show="$flux.dark" x-cloak class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                </svg>
                <svg x-show="!$flux.dark" x-cloak class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                </svg>
                Modo oscuro
            </span>
            <button @click="$flux.dark = !$flux.dark" type="button"
                class="flex h-[21px] w-[38px] items-center rounded-full bg-gray-300 p-[2px] transition-colors dark:bg-ipv-blue"
                :class="$flux.dark ? 'justify-end' : 'justify-start'">
                <span class="size-[17px] rounded-full bg-white shadow"></span>
            </button>
        </div>

        <!-- Perfil -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" type="button"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 hover:bg-black/[0.03] dark:hover:bg-white/5">
                <span class="relative flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-[9px] bg-ipv-gold text-[13px] font-bold text-ipv-gold-ink dark:text-ipv-gold-ink-dark">
                    @if (auth()->user()->profile_photo_path)
                        <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}"
                            alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                    @else
                        {{ auth()->user()->initials() }}
                    @endif
                </span>
                <div class="min-w-0 flex-1 text-left">
                    <div class="truncate text-[13px] font-semibold">{{ auth()->user()->nombre }}</div>
                    <div class="truncate text-[11px] text-ipv-ink/55 dark:text-ipv-ink-dark/50">
                        {{ auth()->user()->permisos()->where('nombre', 'oficina_asignada')->first()?->oficina?->nombre ?? 'Sin oficina' }}
                    </div>
                </div>
                <svg class="size-3.5 shrink-0 text-ipv-ink/40 dark:text-ipv-ink-dark/45" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.53 3.47a.75.75 0 0 0-1.06 0L6.22 6.72a.75.75 0 0 0 1.06 1.06L10 5.06l2.72 2.72a.75.75 0 1 0 1.06-1.06l-3.25-3.25Zm-3.31 9.81 3.25 3.25a.75.75 0 0 0 1.06 0l3.25-3.25a.75.75 0 1 0-1.06-1.06L10 14.94l-2.72-2.72a.75.75 0 0 0-1.06 1.06Z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition
                class="absolute bottom-full left-0 z-50 mb-2 w-[220px] overflow-hidden rounded-lg border border-ipv-blue/10 bg-white shadow-xl dark:border-white/10 dark:bg-slate-800">
                <a href="{{ route('settings.profile') }}" wire:navigate
                    class="block px-4 py-2.5 text-sm text-ipv-ink/80 hover:bg-black/[0.03] dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                    {{ __('Settings') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-4 py-2.5 text-left text-sm text-ipv-ink/80 hover:bg-black/[0.03] dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Header móvil -->
    <div class="sirex-glass sticky top-0 z-30 flex items-center justify-end border-b border-ipv-blue/12 p-3 dark:border-white/10 lg:hidden">
        <button @click="sidebarOpen = true" type="button" class="rounded-lg p-2 text-ipv-blue dark:text-ipv-gold-light">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <main class="min-w-0 flex-1 p-6 lg:p-8">
        {{ $slot }}
    </main>

    @fluxScripts
</body>

</html>
