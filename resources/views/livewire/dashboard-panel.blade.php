{{-- ── Bienvenida ── --}}
<div class="flex min-w-0 flex-1 flex-col gap-4">

    <div class="mb-1" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)">
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark transition-all duration-700 ease-out"
            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'">
            ¡Hola, {{ Auth::user()->nombre }}!
        </h1>
        <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55 transition-all duration-700 ease-out delay-150"
            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'">
            {{ Auth::user()->permisos()->where('nombre', 'oficina_asignada')->first()?->oficina?->nombre ?? 'Sistema Administrativo' }}
            — {{ now()->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
        </p>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Activos en oficina', 'valor' => $activos, 'icon' => 'M4 4a2 2 0 0 1 2-2h4.6a2 2 0 0 1 1.4.6L15.4 6a2 2 0 0 1 .6 1.4V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Z'],
            ['label' => 'Ingresados este mes', 'valor' => $ingresados, 'icon' => 'M12 4v12m0 0-4-4m4 4 4-4M4 18h16'],
            ['label' => 'Egresados este mes', 'valor' => $egresados, 'icon' => 'M12 20V8m0 0 4 4m-4-4-4 4M4 6h16'],
            ['label' => 'Resoluciones emitidas', 'valor' => $resoluciones, 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ] as $m)
            <div class="sirex-glass-card rounded-[14px] p-5">
                <div class="mb-3.5 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                        {{ $m['label'] }}
                    </span>
                    <div class="flex size-[30px] items-center justify-center rounded-lg bg-ipv-blue/12 dark:bg-ipv-blue-light/20">
                        <svg class="size-4 text-ipv-blue dark:text-ipv-blue-light" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $m['icon'] }}" />
                        </svg>
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
                <table class="w-full border-collapse text-[13.5px]">
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
                            <tr class="border-t border-ipv-blue/10 hover:bg-black/[0.02] dark:border-white/10 dark:hover:bg-white/[0.03]">
                                <td class="whitespace-nowrap px-5 py-3 font-bold text-ipv-blue dark:text-ipv-blue-light">
                                    {{ $exp->num_exp }}
                                </td>
                                <td class="px-5 py-3">
                                    <p class="max-w-[200px] truncate text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->asunto }}</p>
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
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $egresado
                                            ? 'bg-gray-500/10 text-gray-500 dark:text-gray-300 border border-gray-400/20'
                                            : 'bg-ipv-blue/12 text-ipv-blue border border-ipv-blue/25 dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light' }}">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        {{ $egresado ? 'Egresado' : 'Activo' }}
                                    </span>
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
                    ['route' => 'expedientes',      'label' => 'Expedientes',   'icon' => 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z'],
                    ['route' => 'resoluciones',     'label' => 'Resoluciones',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'listausuarios',    'label' => 'Usuarios',      'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['route' => '#',                'label' => 'Faltas',        'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'],
                    ['route' => 'settings.profile', 'label' => 'Configuración', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ];
            @endphp

            @foreach($accesos as $item)
                @php $href = $item['route'] === '#' ? '#' : route($item['route']); @endphp
                <a href="{{ $href }}"
                   class="group flex flex-col items-center gap-2 rounded-xl border border-ipv-blue/12 bg-white/40 p-3 transition-all duration-200 hover:scale-105 hover:bg-white/60 dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/[0.08]">
                    <div class="flex size-9 items-center justify-center rounded-full bg-ipv-blue/12 dark:bg-ipv-blue-light/20">
                        <svg class="size-5 text-ipv-blue dark:text-ipv-blue-light" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="{{ $item['icon'] }}" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-center text-xs font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
