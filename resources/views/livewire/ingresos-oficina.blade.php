@php
    $entrada = 'w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark';
@endphp

<div class="space-y-4">
    <x-expedientes-tabs activo="ingresos" />

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Ingresos a una oficina</h1>
        <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
            Expedientes que entraron a la oficina en el período elegido, estén donde estén hoy. Cada expediente cuenta una sola vez.
        </p>
    </div>

    <div class="sirex-glass-card grid gap-3 rounded-[14px] p-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto_auto]">
        <label class="block sm:col-span-2 lg:col-span-1">
            <span class="sr-only">Oficina</span>
            <select wire:model.live="oficinaId" class="{{ $entrada }} dark:bg-[#141c26]">
                <option value="">Elegí una oficina</option>
                @foreach ($oficinas as $ofi)
                    <option value="{{ $ofi->id }}">{{ $ofi->nombre }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="sr-only">Desde</span>
            <input type="date" wire:model.live="desde" title="Desde" class="{{ $entrada }}" />
        </label>
        <label class="block">
            <span class="sr-only">Hasta</span>
            <input type="date" wire:model.live="hasta" title="Hasta" class="{{ $entrada }}" />
        </label>
        @if ($filas && $filas->total() > 0)
            <a href="{{ route('expedientes.ipv.ingresos.csv', ['oficina' => $oficinaId, 'desde' => $desde, 'hasta' => $hasta]) }}"
                class="sirex-btn inline-flex items-center justify-center gap-1.5 rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14" /></svg>
                Exportar CSV
            </a>
            <a href="{{ route('expedientes.ipv.ingresos.imprimir', ['oficina' => $oficinaId, 'desde' => $desde, 'hasta' => $hasta]) }}" target="_blank" rel="noopener"
                class="sirex-btn inline-flex items-center justify-center gap-1.5 rounded-lg border border-ipv-blue/30 px-4 py-2 text-sm font-medium text-ipv-blue dark:border-white/20 dark:text-ipv-blue-light">
                Imprimir
            </a>
        @endif
    </div>

    @if ($error)
        <div class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
            No se pudo consultar el sistema de expedientes del IPV. Probá de nuevo en unos minutos.
        </div>
    @elseif (! $oficinaId)
        <x-vacio icono="buscar">
            <p class="font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70">Elegí una oficina para ver sus ingresos.</p>
        </x-vacio>
    @elseif ($aviso)
        <div class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">{{ $aviso }}</div>
    @else
        <div class="flex items-center justify-between text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
            <span>{{ number_format($filas->total(), 0, ',', '.') }} expedientes ingresaron</span>
            <span wire:loading.delay class="flex items-center gap-1.5">
                <span class="size-1.5 animate-pulse rounded-full bg-ipv-blue"></span> Buscando…
            </span>
        </div>

        @if ($truncado)
            <div class="rounded-lg border border-ipv-gold/40 bg-ipv-gold/15 px-4 py-3 text-sm text-ipv-gold-ink dark:text-ipv-gold-light">
                Hay demasiados pases en el período y la lista quedó cortada. Achicá el rango de fechas para verla completa.
            </div>
        @endif

        <div class="sirex-glass-card overflow-x-auto rounded-[14px]">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">
                        <th class="px-4 py-2.5">Número</th>
                        <th class="px-4 py-2.5">Causante / asunto</th>
                        <th class="px-4 py-2.5">Primer ingreso</th>
                        <th class="px-4 py-2.5">Viene de</th>
                        <th class="px-4 py-2.5 text-right">Veces</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($filas as $f)
                        <tr wire:key="ing-{{ $f->id }}" class="transition-colors hover:bg-ipv-blue/[0.04] dark:hover:bg-white/[0.04]">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('expedientes.ipv.ficha', $f->id) }}" wire:navigate.hover
                                    class="font-mono text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">{{ $f->numero }}</a>
                            </td>
                            <td class="max-w-[320px] px-4 py-3">
                                <div class="truncate text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $f->causante }}</div>
                                <div class="truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $f->asunto }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ \Carbon\Carbon::parse($f->fecha_hora)->format('d/m/Y H:i') }}</td>
                            <td class="max-w-[220px] px-4 py-3 text-sm leading-snug text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ $f->viene_de ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ $f->veces }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-vacio icono="buscar">
                                    <p class="font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70">No entró ningún expediente a esa oficina en el período.</p>
                                </x-vacio>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $filas->links() }}</div>
    @endif
</div>
