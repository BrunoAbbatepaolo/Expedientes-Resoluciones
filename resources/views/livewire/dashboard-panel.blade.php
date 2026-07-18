{{-- ── Bienvenida ── --}}
<div class="flex min-w-0 flex-1 flex-col gap-4">

    <div class="mb-1">
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">
            ¡Hola, {{ Auth::user()->nombre }}!
        </h1>
        <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">
            {{ Auth::user()->permisos()->where('nombre', 'oficina_asignada')->first()?->oficina?->nombre ?? 'Sistema Administrativo' }}
            — {{ now()->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
        </p>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Activos en oficina', 'valor' => $activos, 'icon' => 'archive-box'],
            ['label' => 'Ingresados este mes', 'valor' => $ingresados, 'icon' => 'arrow-down-tray'],
            ['label' => 'Egresados este mes', 'valor' => $egresados, 'icon' => 'arrow-up-tray'],
            ['label' => 'Resoluciones emitidas', 'valor' => $resoluciones, 'icon' => 'check-badge'],
        ] as $m)
            <div class="sirex-glass-card rounded-[14px] p-[18px_20px]">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                        {{ $m['label'] }}
                    </span>
                    <div class="flex size-[30px] items-center justify-center rounded-lg bg-ipv-blue/12 dark:bg-ipv-blue-light/20">
                        <flux:icon :icon="$m['icon']" variant="outline" class="size-4 !text-ipv-blue dark:!text-ipv-blue-light" />
                    </div>
                </div>
                <div class="text-[26px] font-bold leading-none text-ipv-ink dark:text-ipv-ink-dark">{{ $m['valor'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[1.3fr_1fr]">

        {{-- Expedientes recientes --}}
        <div class="sirex-glass-card min-w-0 overflow-hidden rounded-[14px]">
            <div class="flex items-center justify-between border-b border-ipv-blue/10 px-5 py-4 dark:border-white/10">
                <h2 class="text-[15px] font-semibold text-ipv-ink dark:text-ipv-ink-dark">Expedientes recientes</h2>
                <a href="{{ route('expedientes') }}" wire:navigate
                    class="text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">
                    Ver todos →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Número</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Causante</th>
                            <th class="hidden px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50 md:table-cell">Oficina</th>
                            <th class="hidden px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50 sm:table-cell">Fecha ingreso</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recientes as $exp)
                            @php $egresado = (bool) $exp->fecha_salida; @endphp
                            <tr class="border-t border-ipv-blue/10 dark:border-white/10">
                                <td class="whitespace-nowrap px-5 py-3 font-bold text-ipv-blue dark:text-ipv-blue-light">
                                    {{ $exp->num_exp }}
                                </td>
                                <td class="px-5 py-3 text-ipv-ink dark:text-ipv-ink-dark">
                                    <p class="max-w-[200px] truncate">{{ $exp->asunto }}</p>
                                    @if($exp->causante)
                                        <p class="truncate text-xs text-ipv-ink/45 dark:text-ipv-ink-dark/45">{{ $exp->causante }}</p>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap px-5 py-3 text-ipv-ink/60 dark:text-ipv-ink-dark/60 md:table-cell">
                                    {{ $exp->oficinaById?->nombre ?? '—' }}
                                </td>
                                <td class="hidden whitespace-nowrap px-5 py-3 text-ipv-ink/60 dark:text-ipv-ink-dark/60 sm:table-cell">
                                    {{ \Carbon\Carbon::parse($exp->fecha_ingreso)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3">
                                    <flux:badge size="sm" :color="$egresado ? 'zinc' : 'blue'">
                                        {{ $egresado ? 'Egresado' : 'Activo' }}
                                    </flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                                    No hay expedientes registrados para esta oficina.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Calendario (componente existente) --}}
        <livewire:dashboard-calendar />
    </div>

    {{-- ── Accesos rápidos ── --}}
    <div class="sirex-glass-card rounded-[14px] p-4 sm:p-6">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-base font-semibold text-ipv-ink dark:text-ipv-ink-dark">Accesos rápidos</h3>
            <span class="text-xs text-ipv-ink/40 dark:text-ipv-ink-dark/40">Sistema v0.5</span>
        </div>

        <div class="grid grid-cols-5 gap-3">
            @php
                $accesos = [
                    ['route' => 'expedientes',      'label' => 'Expedientes',    'icon' => 'folder'],
                    ['route' => 'resoluciones',     'label' => 'Resoluciones',   'icon' => 'check-badge'],
                    ['route' => 'listausuarios',    'label' => 'Usuarios',       'icon' => 'users'],
                    ['route' => '#',                'label' => 'Faltas',         'icon' => 'calendar-days'],
                    ['route' => 'settings.profile', 'label' => 'Configuración',  'icon' => 'cog-6-tooth'],
                ];
            @endphp

            @foreach($accesos as $item)
                @php $href = $item['route'] === '#' ? '#' : route($item['route']); @endphp
                <a href="{{ $href }}"
                   class="group flex flex-col items-center gap-2 rounded-xl border border-ipv-blue/12 bg-white/40 p-3 transition-all duration-200 hover:scale-105 hover:bg-white/60 dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/[0.08]">
                    <div class="flex size-9 items-center justify-center rounded-full bg-ipv-blue/12 dark:bg-ipv-blue-light/20">
                        <flux:icon :icon="$item['icon']" variant="outline" class="size-5 !text-ipv-blue dark:!text-ipv-blue-light" />
                    </div>
                    <span class="text-center text-xs font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
