{{-- Calendario mensual de asistencia: un casillero por día coloreado por estado; al tocar
     un día se ve el detalle (horario, fichadas, motivo). $dias: list<DiaAsistencia>. --}}
@props(['dias'])

@php
    $detalle = collect($dias)->mapWithKeys(fn ($d) => [$d->fecha->day => [
        'fecha' => ucfirst($d->fecha->translatedFormat('l j \d\e F')),
        'estado' => $d->estado->etiqueta(),
        'horario' => $d->horario,
        'marcas' => $d->marcas,
        'motivo' => $d->motivo,
    ]]);
    $primero = $dias[0]->fecha ?? null;
@endphp

@if ($primero)
<div x-data="{ sel: null, dias: @js($detalle) }" class="space-y-3">
    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-medium uppercase tracking-wide text-ipv-ink/45 dark:text-ipv-ink-dark/45">
        @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $d)
            <div>{{ $d }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 gap-1">
        @for ($i = 1; $i < $primero->dayOfWeekIso; $i++)
            <div></div>
        @endfor
        @foreach ($dias as $dia)
            <button type="button" @click="sel = sel === {{ $dia->fecha->day }} ? null : {{ $dia->fecha->day }}"
                :class="sel === {{ $dia->fecha->day }} ? 'ring-2 ring-ipv-blue' : ''"
                title="{{ $dia->estado->etiqueta() }}"
                class="flex aspect-square flex-col items-center justify-center rounded-lg text-sm transition hover:scale-105 sm:aspect-auto sm:h-16 {{ $dia->estado->clases() }} {{ $dia->fecha->isToday() ? 'font-bold underline' : '' }}">
                <span>{{ $dia->fecha->day }}</span>
                <span class="hidden text-[10px] leading-tight sm:block">{{ $dia->estado->etiqueta() }}</span>
                @if ($dia->marcas)
                    <span class="hidden text-[10px] opacity-70 sm:block">{{ $dia->marcas[0] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <template x-if="sel !== null">
        <div class="sirex-glass-card rounded-[14px] p-4 text-sm">
            <div class="flex items-center justify-between gap-2">
                <p class="font-semibold text-ipv-ink dark:text-ipv-ink-dark" x-text="dias[sel].fecha"></p>
                <span class="text-xs font-medium text-ipv-ink/60 dark:text-ipv-ink-dark/60" x-text="dias[sel].estado"></span>
            </div>
            <dl class="mt-2 grid gap-1 text-ipv-ink/75 dark:text-ipv-ink-dark/75 sm:grid-cols-3">
                <div><dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Horario</dt><dd x-text="dias[sel].horario ?? 'Sin horario'"></dd></div>
                <div><dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Fichadas</dt><dd x-text="dias[sel].marcas.length ? dias[sel].marcas.join(' · ') : 'Ninguna'"></dd></div>
                <div><dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Detalle</dt><dd x-text="dias[sel].motivo || '—'"></dd></div>
            </dl>
        </div>
    </template>

    <div class="flex flex-wrap gap-1.5 text-xs">
        @foreach ([\App\Enums\EstadoDia::Presente, \App\Enums\EstadoDia::Tarde, \App\Enums\EstadoDia::Incompleto, \App\Enums\EstadoDia::Falta, \App\Enums\EstadoDia::Justificado, \App\Enums\EstadoDia::Licencia, \App\Enums\EstadoDia::Feriado, \App\Enums\EstadoDia::Franco] as $e)
            <x-rrhh.estado :estado="$e" />
        @endforeach
    </div>
</div>
@endif
