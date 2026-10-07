<div class="space-y-5">
    <x-rrhh.encabezado titulo="Calendario" subtitulo="Feriados y días no laborables cargados por Recursos Humanos.">
        <div class="flex items-center gap-1">
            <button type="button" wire:click="$set('anio', {{ $anio - 1 }})" class="rounded-lg px-2 py-1 text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5" aria-label="Año anterior">‹</button>
            <span class="min-w-16 text-center font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $anio }}</span>
            <button type="button" wire:click="$set('anio', {{ $anio + 1 }})" class="rounded-lg px-2 py-1 text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5" aria-label="Año siguiente">›</button>
        </div>
    </x-rrhh.encabezado>

    <x-rrhh.aviso-error :mostrar="$error" />

    @if (! $error)
        @if ($porMes->isEmpty())
            <p class="sirex-glass-card rounded-[14px] p-5 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">No hay feriados cargados para {{ $anio }}.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-50">
                @foreach ($porMes as $mes => $feriados)
                    <section wire:key="mes-{{ $anio }}-{{ $mes }}" class="sirex-glass-card rounded-[14px] p-4">
                        <h2 class="font-semibold capitalize text-ipv-ink dark:text-ipv-ink-dark">{{ \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('F') }}</h2>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            @foreach ($feriados as $f)
                                <li class="flex items-baseline gap-3 {{ $f->hasta->isPast() && ! $f->hasta->isToday() ? 'opacity-55' : '' }}">
                                    <span class="w-24 shrink-0 font-medium text-ipv-ink dark:text-ipv-ink-dark">
                                        {{ $f->desde->format('d/m') }}@if (! $f->desde->isSameDay($f->hasta)) – {{ $f->hasta->format('d/m') }}@endif
                                    </span>
                                    <span class="text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $f->tipo ?: 'Feriado' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    @endif
</div>
