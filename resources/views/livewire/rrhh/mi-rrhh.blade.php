<div class="space-y-5">
    <x-rrhh.encabezado titulo="Mi RRHH" subtitulo="Tus fichadas, licencias y saldo de vacaciones. Datos del sistema de personal del IPV." />

    <x-rrhh.aviso-error :mostrar="$error" />

    @if (! $empleado && ! $error)
        <div class="sirex-glass-card rounded-[14px] p-5 text-sm text-ipv-ink/75 dark:text-ipv-ink-dark/75">
            @if ($sinLegajo)
                Tu usuario no tiene un legajo cargado. Pedile al administrador de SiRex que lo complete para ver tus datos de RRHH.
            @else
                No encontramos tu legajo en el sistema de personal. Consultá con Recursos Humanos.
            @endif
        </div>
    @elseif ($empleado)
        {{-- Resumen --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="sirex-glass-card rounded-[14px] p-4">
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Vacaciones pendientes</p>
                <p class="mt-1 text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $saldo->sum('saldo') }} <span class="text-sm font-normal">días</span></p>
                @if ($saldo->where('saldo', '>', 0)->isNotEmpty())
                    <p class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                        {{ $saldo->where('saldo', '>', 0)->map(fn ($s) => $s->anio.': '.$s->saldo)->implode(' · ') }}
                    </p>
                @endif
            </div>
            <div class="sirex-glass-card rounded-[14px] p-4">
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Faltas en {{ $mesActual->translatedFormat('F') }}</p>
                <p class="mt-1 text-2xl font-semibold {{ $faltas ? 'text-ipv-magenta' : 'text-ipv-ink dark:text-ipv-ink-dark' }}">{{ $faltas }}</p>
                <p class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $tardes }} llegada{{ $tardes === 1 ? '' : 's' }} tarde</p>
            </div>
            <div class="sirex-glass-card rounded-[14px] p-4">
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Próxima licencia</p>
                @if ($proxima)
                    <p class="mt-1 font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $proxima->desde->format('d/m') }} al {{ $proxima->hasta->format('d/m') }}</p>
                    <p class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $proxima->tipo?->descripcion }}</p>
                @else
                    <p class="mt-1 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">No tenés licencias cargadas.</p>
                @endif
            </div>
            <div class="sirex-glass-card rounded-[14px] p-4">
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Próximo feriado</p>
                @if ($feriado)
                    <p class="mt-1 font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ ucfirst($feriado->desde->translatedFormat('l j \d\e F')) }}</p>
                    <p class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $feriado->tipo }}</p>
                @else
                    <p class="mt-1 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">Sin feriados cargados.</p>
                @endif
            </div>
        </div>

        {{-- Fichadas del mes --}}
        <section class="sirex-glass-card space-y-4 rounded-[14px] p-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mis fichadas</h2>
                <x-rrhh.selector-mes :mes="$mesActual" />
            </div>
            <div wire:loading.class="opacity-50" class="transition-opacity">
                <x-rrhh.calendario-mes :dias="$dias" />
            </div>
        </section>

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Licencias --}}
            <section class="sirex-glass-card rounded-[14px] p-4">
                <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mis licencias {{ $mesActual->year }}</h2>
                <ul class="mt-3 divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($licencias as $lic)
                        <li wire:key="lic-{{ $lic->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2">
                            <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $lic->tipo?->descripcion ?? 'Licencia' }}</span>
                            <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $lic->desde->format('d/m/Y') }} – {{ $lic->hasta->format('d/m/Y') }}</span>
                            @if ($lic->observacion)
                                <span class="w-full text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $lic->observacion }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin licencias en {{ $mesActual->year }}.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Inasistencias registradas --}}
            <section class="sirex-glass-card rounded-[14px] p-4">
                <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Faltas y tardanzas registradas {{ $mesActual->year }}</h2>
                <ul class="mt-3 divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($inasistencias as $ina)
                        <li wire:key="ina-{{ $ina->id }}" class="flex justify-between gap-2 py-2">
                            <span class="text-ipv-ink dark:text-ipv-ink-dark">{{ $ina->tipo?->nombre ?? 'Inasistencia' }}</span>
                            <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $ina->fecha->format('d/m/Y') }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin registros en {{ $mesActual->year }}.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Mis datos --}}
        <section class="sirex-glass-card rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mis datos</h2>
            <x-rrhh.datos-empleado :empleado="$empleado" :nivel="\App\Enums\NivelDatoRrhh::Sensible" class="mt-3" />
            <p class="mt-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">¿Algún dato está mal? Avisale a Recursos Humanos.</p>
        </section>
    @endif
</div>
