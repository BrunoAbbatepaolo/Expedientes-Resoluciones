<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen overflow-x-hidden sirex-shell text-ipv-ink dark:text-ipv-ink-dark">
    <flux:sidebar sticky stashable
        class="sirex-glass gap-1 border-r border-ipv-blue/12 !p-3.5 dark:border-white/10">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <!-- Logo -->
        <div class="flex items-center gap-2.5 px-2.5 pb-4 pt-2">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-ipv-gold">
                <flux:icon.folder-git-2 variant="outline" class="size-[19px] !text-ipv-blue-dark" />
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate class="min-w-0">
                <div class="truncate text-[16px] font-semibold leading-tight">SiRex</div>
                <div class="truncate text-[11px] text-ipv-ink/55 dark:text-ipv-ink-dark/50">Gestión de expedientes</div>
            </a>
        </div>

        <!-- Buscador -->
        <div class="relative mb-3.5">
            <flux:icon.magnifying-glass
                class="pointer-events-none absolute left-[11px] top-1/2 size-3.5 -translate-y-1/2 !text-ipv-ink/40 dark:!text-ipv-ink-dark/45" />
            <input type="text" placeholder="Buscar…"
                class="w-full rounded-full border border-ipv-blue/15 bg-white/70 py-2 pl-[30px] pr-2.5 text-[12.5px] text-ipv-ink placeholder-ipv-ink/40 focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark dark:placeholder-ipv-ink-dark/40" />
        </div>

        <flux:navlist variant="outline" class="flex-1 overflow-y-auto">

            <div class="px-3 pb-1.5 pt-1 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                General
            </div>

            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate
                class="relative font-semibold before:absolute before:inset-y-2 before:left-1 before:w-[3px] before:rounded-full before:content-[''] data-current:before:!bg-ipv-gold data-current:!bg-ipv-gold/20 data-current:!text-[#7a5200] dark:data-current:!bg-ipv-gold/15 dark:data-current:!text-ipv-gold-light">
                {{ __('Dashboard') }}
            </flux:navlist.item>

            <div class="px-3 pb-1.5 pt-3 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                Gestión
            </div>

            <!-- @if (auth()->user()->permiso('expediente_ver')) -->
            <div x-data="{ open: {{ request()->routeIs('expedientes*') ? 'true' : 'false' }} }" class="space-y-1">
                <flux:navlist.item @click="open = !open" icon="folder" as="button" type="button"
                    :current="request()->routeIs('expedientes*')" class="!text-ipv-ink/75 dark:!text-ipv-ink-dark/82">
                    <div class="flex w-full items-center justify-between">
                        <span>{{ __('Expedientes') }}</span>
                        <flux:icon.chevron-down class="size-3.5 transition-transform" x-bind:class="{ 'rotate-180': open }" />
                    </div>
                </flux:navlist.item>

                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="ml-3.5 space-y-1 border-l-2 border-ipv-blue/15 pl-2.5 dark:border-white/15">
                    <flux:navlist.item icon="document" :href="route('expedientes')"
                        :current="request()->routeIs('expedientes') && !request()->routeIs('expedientes.ingresados') && !request()->routeIs('expedientes.egresados')"
                        wire:navigate class="text-[13px] !text-ipv-ink/65 dark:!text-ipv-ink-dark/65">
                        Todos
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-plus" :href="route('expedientes.ingresados')"
                        :current="request()->routeIs('expedientes.ingresados')" wire:navigate
                        class="text-[13px] !text-ipv-ink/65 dark:!text-ipv-ink-dark/65">
                        Ingresados
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-check" :href="route('expedientes.egresados')"
                        :current="request()->routeIs('expedientes.egresados')" wire:navigate
                        class="text-[13px] !text-ipv-ink/65 dark:!text-ipv-ink-dark/65">
                        Egresados
                    </flux:navlist.item>
                    <flux:navlist.item icon="inbox" :href="route('expedientes.entrantes')"
                        :current="request()->routeIs('expedientes.entrantes')" wire:navigate
                        class="text-[13px] font-semibold !bg-ipv-gold/15 !text-[#7a5200] dark:!text-ipv-gold-light">
                        <div class="flex w-full items-center justify-between">
                            <span>Entrantes</span>
                            <livewire:entrantes-badge />
                        </div>
                    </flux:navlist.item>
                </div>
            </div>
            <!-- @endif -->

            <!-- @if (auth()->user()->permiso('resolucion_ver')) -->
            <flux:navlist.item icon="folder-open" :href="route('resoluciones')" :current="request()->routeIs('resoluciones')"
                wire:navigate
                class="relative before:absolute before:inset-y-2 before:left-1 before:w-[3px] before:rounded-full before:content-[''] !text-ipv-ink/75 data-current:font-semibold data-current:before:!bg-ipv-gold data-current:!bg-ipv-gold/20 data-current:!text-[#7a5200] dark:!text-ipv-ink-dark/82 dark:data-current:!bg-ipv-gold/15 dark:data-current:!text-ipv-gold-light">
                {{ __('Resoluciones') }}
            </flux:navlist.item>
            <!-- @endif -->

            <flux:navlist.item icon="building-office" :href="route('oficinas')" :current="request()->routeIs('oficinas')"
                wire:navigate
                class="relative before:absolute before:inset-y-2 before:left-1 before:w-[3px] before:rounded-full before:content-[''] !text-ipv-ink/75 data-current:font-semibold data-current:before:!bg-ipv-gold data-current:!bg-ipv-gold/20 data-current:!text-[#7a5200] dark:!text-ipv-ink-dark/82 dark:data-current:!bg-ipv-gold/15 dark:data-current:!text-ipv-gold-light">
                {{ __('Oficinas') }}
            </flux:navlist.item>
            <flux:navlist.item icon="user" :href="route('listausuarios')" :current="request()->routeIs('listausuario')"
                wire:navigate
                class="relative before:absolute before:inset-y-2 before:left-1 before:w-[3px] before:rounded-full before:content-[''] !text-ipv-ink/75 data-current:font-semibold data-current:before:!bg-ipv-gold data-current:!bg-ipv-gold/20 data-current:!text-[#7a5200] dark:!text-ipv-ink-dark/82 dark:data-current:!bg-ipv-gold/15 dark:data-current:!text-ipv-gold-light">
                {{ __('Usuarios') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Switch modo oscuro -->
        <div class="flex items-center justify-between border-t border-ipv-blue/10 px-2.5 py-2.5 dark:border-white/10">
            <span class="flex items-center gap-1.5 text-[13px] font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/82">
                <span x-data x-show="$flux.dark" x-cloak><flux:icon.moon variant="outline" class="size-[15px]" /></span>
                <span x-data x-show="!$flux.dark" x-cloak><flux:icon.sun variant="outline" class="size-[15px]" /></span>
                Modo oscuro
            </span>
            <flux:switch x-data x-model="$flux.dark" />
        </div>

        <!-- Perfil -->
        <flux:dropdown position="top" align="start" class="w-full">
            <button
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1 hover:bg-black/[0.03] dark:hover:bg-white/5">
                <div
                    class="flex size-8 shrink-0 items-center justify-center rounded-[9px] bg-ipv-gold text-[13px] font-bold text-ipv-gold-ink dark:text-ipv-gold-ink-dark">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="min-w-0 flex-1 text-left">
                    <div class="truncate text-[13px] font-semibold">{{ auth()->user()->nombre }}</div>
                    <div class="truncate text-[11px] text-ipv-ink/55 dark:text-ipv-ink-dark/50">
                        {{ auth()->user()->permisos()->where('nombre', 'oficina_asignada')->first()?->oficina?->nombre ?? 'Sin oficina' }}
                    </div>
                </div>
                <flux:icon.chevrons-up-down class="size-3.5 shrink-0 !text-ipv-ink/40 dark:!text-ipv-ink-dark/45" />
            </button>

            <flux:menu class="w-[220px]">
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="sirex-glass border-b border-ipv-blue/12 lg:hidden dark:border-white/10">
        <flux:sidebar.toggle class="lg:hidden !text-ipv-blue dark:!text-ipv-blue-light" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down"
                avatar:class="bg-ipv-gold text-ipv-gold-ink" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-ipv-gold text-ipv-gold-ink">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
