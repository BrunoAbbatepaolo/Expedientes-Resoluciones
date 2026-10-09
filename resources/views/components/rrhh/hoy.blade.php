{{-- Estado del día: totales por estado y personas agrupadas por oficina.
     $filas: Collection<array{empleado, dia}>. --}}
@props(['filas', 'agrupar' => true])

@php
    use App\Enums\EstadoDia;

    $totales = collect($filas)->countBy(fn ($f) => $f['dia']->estado->value);
    $resumen = [
        [EstadoDia::Presente, 'Presentes'],
        [EstadoDia::Tarde, 'Tarde'],
        [EstadoDia::Falta, 'Sin fichar / ausentes'],
        [EstadoDia::Licencia, 'De licencia'],
        [EstadoDia::Justificado, 'Justificados / no fichan'],
        [EstadoDia::Pendiente, 'Por entrar'],
    ];
    $grupos = $agrupar ? collect($filas)->groupBy(fn ($f) => $f['empleado']->oficinaRrhh?->nombre ?? 'Sin oficina') : collect(['' => collect($filas)]);
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($resumen as [$estado, $etiqueta])
            <div class="rounded-[12px] px-3 py-2 {{ $estado->clases() }}">
                <p class="text-2xl font-semibold">{{ $totales[$estado->value] ?? 0 }}</p>
                <p class="text-xs">{{ $etiqueta }}</p>
            </div>
        @endforeach
    </div>

    @forelse ($grupos as $oficina => $personas)
        <section wire:key="grupo-{{ md5($oficina) }}">
            @if ($oficina !== '')
                <h3 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $oficina }} · {{ $personas->count() }}</h3>
            @endif
            <ul class="grid gap-1.5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($personas->sortBy(fn ($f) => [$f['dia']->estado === EstadoDia::Presente ? 1 : 0, $f['empleado']->apellido]) as $f)
                    <li wire:key="hoy-{{ $f['empleado']->id }}" class="min-w-0">
                        <a href="{{ route('rrhh.ficha', $f['empleado']->id) }}" wire:navigate
                            class="flex items-center justify-between gap-2 rounded-lg border border-ipv-blue/10 px-3 py-2 text-sm transition hover:border-ipv-blue/30 dark:border-white/10">
                            <span class="min-w-0 truncate text-ipv-ink dark:text-ipv-ink-dark">{{ $f['empleado']->apellido }}, {{ $f['empleado']->nombre }}</span>
                            <span class="flex shrink-0 items-center gap-2">
                                @if ($f['dia']->marcas)
                                    <span class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $f['dia']->marcas[0] }}</span>
                                @endif
                                <x-rrhh.estado :estado="$f['dia']->estado" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados activos para mostrar.</p>
    @endforelse
</div>
