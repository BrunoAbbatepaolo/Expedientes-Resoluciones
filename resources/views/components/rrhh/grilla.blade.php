{{-- Grilla empleados × días del mes. $grilla: ['dias' => list<CarbonImmutable>, 'filas' => [['empleado', 'dias']]]. --}}
@props(['grilla'])

@php
    $letra = fn ($estado) => match ($estado) {
        \App\Enums\EstadoDia::Presente => '✓',
        \App\Enums\EstadoDia::Tarde => 'T',
        \App\Enums\EstadoDia::Incompleto => 'I',
        \App\Enums\EstadoDia::Falta => 'F',
        \App\Enums\EstadoDia::Justificado => 'J',
        \App\Enums\EstadoDia::Licencia => 'L',
        \App\Enums\EstadoDia::Feriado => 'Fe',
        \App\Enums\EstadoDia::Pendiente => '…',
        default => '',
    };
@endphp

@if (empty($grilla['filas']))
    <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados activos en esta oficina.</p>
@else
    <div class="overflow-x-auto rounded-lg border border-ipv-blue/10 dark:border-white/10">
        <table class="min-w-full border-collapse text-xs">
            <thead>
                <tr class="bg-black/[0.02] dark:bg-white/[0.03]">
                    <th class="sticky left-0 z-10 bg-white px-3 py-2 text-left font-medium text-ipv-ink/70 dark:bg-[#141c26] dark:text-ipv-ink-dark/70">Empleado</th>
                    @foreach ($grilla['dias'] as $d)
                        <th class="w-7 px-0.5 py-2 text-center font-medium {{ $d->isWeekend() ? 'text-ipv-ink/35 dark:text-ipv-ink-dark/35' : 'text-ipv-ink/65 dark:text-ipv-ink-dark/65' }}">{{ $d->day }}</th>
                    @endforeach
                    <th class="px-2 py-2 text-center font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70" title="Faltas">F</th>
                    <th class="px-2 py-2 text-center font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70" title="Llegadas tarde">T</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grilla['filas'] as $fila)
                    @php($dias = collect($fila['dias']))
                    <tr wire:key="fila-{{ $fila['empleado']->id }}" class="border-t border-ipv-blue/10 dark:border-white/10">
                        <td class="sticky left-0 z-10 whitespace-nowrap bg-white px-3 py-1.5 dark:bg-[#141c26]">
                            <a href="{{ route('rrhh.ficha', $fila['empleado']->id) }}" wire:navigate class="text-ipv-ink hover:text-ipv-blue hover:underline dark:text-ipv-ink-dark">
                                {{ $fila['empleado']->apellido }}, {{ \Illuminate\Support\Str::of($fila['empleado']->nombre)->before(' ') }}
                            </a>
                        </td>
                        @foreach ($fila['dias'] as $dia)
                            <td class="p-0.5 text-center">
                                <span title="{{ $dia->fecha->format('d/m') }} · {{ $dia->estado->etiqueta() }}{{ $dia->marcas ? ' · '.implode(' ', $dia->marcas) : '' }}{{ $dia->motivo ? ' · '.$dia->motivo : '' }}"
                                    class="flex h-6 w-6 items-center justify-center rounded text-[10px] font-semibold {{ $dia->estado->clases() }}">{{ $letra($dia->estado) }}</span>
                            </td>
                        @endforeach
                        <td class="px-2 text-center font-semibold text-ipv-magenta">{{ $dias->where('estado', \App\Enums\EstadoDia::Falta)->count() ?: '' }}</td>
                        <td class="px-2 text-center font-semibold text-amber-700 dark:text-amber-300">{{ $dias->where('estado', \App\Enums\EstadoDia::Tarde)->count() ?: '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">✓ presente · T tarde · I incompleto · F falta · J justificado · L licencia · Fe feriado. Pasá el mouse por un día para ver fichadas.</p>
@endif
