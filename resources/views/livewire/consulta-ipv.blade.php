<div class="space-y-4">
    <x-expedientes-tabs activo="ipv" />

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Expedientes del IPV</h1>
        <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
            Consulta en tiempo real del sistema de expedientes. Los pases se registran en ese sistema.
        </p>
    </div>

    {{-- Filtros --}}
    <div class="sirex-glass-card grid gap-3 rounded-[14px] p-3 sm:grid-cols-2 2xl:grid-cols-[2fr_1.5fr_1fr_1fr_auto]">
        <label class="relative block sm:col-span-2 2xl:col-span-1">
            <span class="sr-only">Buscar</span>
            <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="search" wire:model.live.debounce.400ms="texto" autofocus
                placeholder="Número (ej. 7780/440-2026), causante o asunto"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2 pl-10 pr-3 text-sm text-ipv-ink transition focus:border-ipv-blue focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </label>

        <label class="block">
            <span class="sr-only">Oficina donde está</span>
            <select wire:model.live="oficinaId"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-[#141c26] dark:text-ipv-ink-dark">
                <option value="">Está en cualquier oficina</option>
                @foreach ($oficinas as $ofi)
                    <option value="{{ $ofi->id }}">{{ $ofi->nombre }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="sr-only">Desde</span>
            <input type="date" wire:model.live="desde" title="Iniciado desde"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </label>
        <label class="block">
            <span class="sr-only">Hasta</span>
            <input type="date" wire:model.live="hasta" title="Iniciado hasta"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </label>

        <button type="button" wire:click="limpiar"
            class="sirex-btn rounded-lg border border-ipv-blue/20 px-4 py-2 text-sm font-medium text-ipv-ink/75 transition hover:bg-white dark:border-white/15 dark:text-ipv-ink-dark/80 dark:hover:bg-white/[0.08]">
            Limpiar
        </button>
    </div>

    @if ($error)
        <div class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
            No se pudo consultar el sistema de expedientes del IPV. Probá de nuevo en unos minutos.
        </div>
    @else
        <div class="flex items-center justify-between text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
            <span>{{ number_format($expedientes->total(), 0, ',', '.') }} expedientes</span>
            <span wire:loading.delay class="flex items-center gap-1.5">
                <span class="size-1.5 animate-pulse rounded-full bg-ipv-blue"></span> Buscando…
            </span>
        </div>

        {{-- Mientras busca se muestra el skeleton en lugar de resultados viejos. --}}
        {{-- class+remove en vez de wire:loading.delay.block: Livewire solo oculta de entrada
             las variantes simples de wire:loading. --}}
        <x-skeleton.tabla class="hidden" wire:loading.delay.class.remove="hidden" :filas="8" :columnas="5" />

        <div wire:loading.delay.remove class="sirex-glass-card overflow-x-auto rounded-[14px]">
            <table class="w-full min-w-[560px]">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">
                        <th class="px-4 py-2.5">Número</th>
                        <th class="px-4 py-2.5">Causante / asunto</th>
                        <th class="hidden px-4 py-2.5 xl:table-cell">Iniciado</th>
                        <th class="px-4 py-2.5">Está en</th>
                        <th class="px-4 py-2.5 text-right">Hace</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($expedientes as $exp)
                        <tr wire:key="ipv-{{ $exp->id }}"
                            class="group cursor-pointer transition-colors duration-150 hover:bg-ipv-blue/[0.04] dark:hover:bg-white/[0.04]"
                            onclick="Livewire.navigate('{{ route('expedientes.ipv.ficha', $exp->id) }}')">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('expedientes.ipv.ficha', $exp->id) }}" wire:navigate.hover
                                    class="font-mono text-sm font-semibold text-ipv-blue group-hover:underline dark:text-ipv-blue-light">
                                    {{ $exp->numero }}
                                </a>
                            </td>
                            <td class="max-w-[320px] px-4 py-3">
                                <div class="truncate text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->causante }}</div>
                                <div class="truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $exp->tipo_asunto ?? $exp->asunto }}</div>
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70 xl:table-cell">
                                {{ $exp->fecha ? \Carbon\Carbon::parse($exp->fecha)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="max-w-[220px] px-4 py-3 text-sm leading-snug text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ $exp->ubicacion ?? 'Sin pases' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <x-dias-badge :dias="$exp->dias_en_ubicacion" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70">No hay expedientes con esos filtros.</p>
                                <p class="mt-1 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Probá con el número completo o parte del nombre del causante.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $expedientes->links() }}</div>
    @endif
</div>
