<div class="space-y-5">
    <x-rrhh.aviso-error :mostrar="$error" />

    @if ($ficha)
        @php($e = $ficha['empleado'])
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                @if ($puedeVolver)
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('rrhh.equipo') }}" wire:navigate
                        class="text-xs text-ipv-blue hover:underline dark:text-sky-300">‹ Volver</a>
                @endif
                <h1 class="mt-1 text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $e->apellido }}, {{ $e->nombre }}</h1>
                <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                    Legajo {{ $e->legajo }} · {{ $e->oficinaRrhh?->nombre ?? 'Sin oficina' }}
                    @if ($e->espasivo) · <span class="font-medium text-ipv-magenta">Pasivo</span> @endif
                </p>
            </div>
            <span class="w-fit rounded-full bg-black/[0.04] px-3 py-1 text-xs text-ipv-ink/60 dark:bg-white/5 dark:text-ipv-ink-dark/60"
                title="Qué datos podés ver de esta persona">
                Ves datos de nivel {{ $ficha['nivel']->etiqueta() }}
            </span>
        </div>

        <nav class="-mx-4 flex gap-1 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Secciones de la ficha">
            @foreach ($pestanias as $clave => [$etiqueta])
                <button type="button" wire:click="$set('pestania', '{{ $clave }}')"
                    @if ($pestania === $clave) aria-current="page" @endif
                    class="shrink-0 rounded-lg px-3 py-1.5 text-sm transition {{ $pestania === $clave
                        ? 'bg-ipv-blue font-semibold text-white'
                        : 'text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5' }}">
                    {{ $etiqueta }}
                </button>
            @endforeach
        </nav>

        <section class="sirex-glass-card rounded-[14px] p-4" wire:loading.class="opacity-60">
            @switch($pestania)
                @case('familia')
                    <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                        @forelse ($ficha['familia'] as $f)
                            <li class="py-2.5">
                                <p class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $f->apellido_nombre }}</p>
                                <p class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                                    {{ $f->parentesco ?? 'Familiar' }}
                                    @if ($f->fecha_nacimiento) · nacido el {{ \Illuminate\Support\Carbon::parse($f->fecha_nacimiento)->format('d/m/Y') }} @endif
                                    @if ($f->numero_documento) · DNI {{ $f->numero_documento }} @endif
                                </p>
                                @if ($ficha['nivel'] === \App\Enums\NivelDatoRrhh::Sensible)
                                    @php($marcas = collect(['discapacitado' => 'Discapacidad', 'posee_orden_judicial' => 'Orden judicial', 'percibe_perpension_alimenticia' => 'Pensión alimenticia', 'posee_guarda_legal' => 'Guarda legal', 'posee_curatela' => 'Curatela', 'posee_sumario_judicial' => 'Sumario judicial'])->filter(fn ($l, $c) => filter_var($f->{$c}, FILTER_VALIDATE_BOOLEAN)))
                                    @if ($marcas->isNotEmpty())
                                        <p class="mt-1 text-xs text-ipv-magenta">{{ $marcas->implode(' · ') }}</p>
                                    @endif
                                @endif
                            </li>
                        @empty
                            <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin familiares cargados.</li>
                        @endforelse
                    </ul>
                    @break

                @case('trayectoria')
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div>
                            <h2 class="mb-2 text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">Foja de servicio</h2>
                            <ul class="space-y-2 text-sm">
                                @forelse ($ficha['fojas'] as $foja)
                                    <li class="border-l-2 border-ipv-blue/30 pl-3">
                                        <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $foja->fecha?->format('d/m/Y') }} · {{ trim($foja->resolucion_decreto.' '.$foja->nro_res_dec) }}</p>
                                        <p class="text-ipv-ink dark:text-ipv-ink-dark">{{ $foja->observacion ?: '—' }}</p>
                                    </li>
                                @empty
                                    <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin registros.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h2 class="mb-2 text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">Altas y bajas</h2>
                            <ul class="space-y-2 text-sm">
                                @forelse ($ficha['movimientos'] as $mov)
                                    <li class="border-l-2 border-ipv-gold/50 pl-3">
                                        <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $mov->fecha?->format('d/m/Y') }} · {{ $mov->resolucion }}</p>
                                        <p class="text-ipv-ink dark:text-ipv-ink-dark">{{ $mov->motivo ?: '—' }}</p>
                                    </li>
                                @empty
                                    <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin movimientos.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    @break

                @case('asistencia')
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Asistencia</h2>
                        <x-rrhh.selector-mes :mes="$mesActual" />
                    </div>
                    <x-rrhh.calendario-mes :dias="$dias ?? []" />
                    @break

                @case('licencias')
                    @isset($vacaciones)
                        <h2 class="mb-2 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Vacaciones</h2>
                        <x-rrhh.vacaciones :v="$vacaciones" detalle class="mb-5" />
                    @endisset
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Licencias {{ $mesActual->year }}</h2>
                        <x-rrhh.selector-anio :mes="$mesActual" />
                    </div>
                    <ul class="mt-2 divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                        @forelse ($licencias ?? [] as $lic)
                            <li wire:key="lic-{{ $lic->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2">
                                <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $lic->tipo?->descripcion ?? 'Licencia' }}</span>
                                <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $lic->desde->format('d/m/Y') }} – {{ $lic->hasta->format('d/m/Y') }} · {{ $lic->desde->diffInDays($lic->hasta) + 1 }} días</span>
                                @if ($lic->observacion)
                                    <span class="w-full text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $lic->observacion }}</span>
                                @endif
                            </li>
                        @empty
                            <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin licencias en {{ $mesActual->year }}.</li>
                        @endforelse
                    </ul>
                    @break

                @case('inasistencias')
                    <h2 class="mb-2 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Inasistencias registradas {{ $mesActual->year }}</h2>
                    <x-rrhh.selector-anio :mes="$mesActual" />
                    @php($porTipo = collect($inasistencias ?? [])->groupBy(fn ($i) => $i->tipo?->nombre ?? 'Inasistencia'))
                    @if ($porTipo->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            @foreach ($porTipo as $tipo => $items)
                                <span class="rounded-full bg-black/[0.04] px-2.5 py-1 dark:bg-white/5">{{ $tipo }}: <strong>{{ $items->count() }}</strong></span>
                            @endforeach
                        </div>
                    @endif
                    <ul class="mt-2 divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                        @forelse ($inasistencias ?? [] as $ina)
                            <li wire:key="ina-{{ $ina->id }}" class="flex justify-between gap-2 py-2">
                                <span class="text-ipv-ink dark:text-ipv-ink-dark">{{ $ina->tipo?->nombre ?? 'Inasistencia' }}</span>
                                <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $ina->fecha->format('d/m/Y') }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin registros en {{ $mesActual->year }}.</li>
                        @endforelse
                    </ul>
                    @break

                @case('archivos')
                    <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                        @forelse ($ficha['archivos'] as $archivo)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $archivo->nombre }}</p>
                                    <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                                        {{ $archivo->descripcion }} @if ($archivo->tamanio) · {{ number_format($archivo->tamanio / 1024, 0, ',', '.') }} KB @endif
                                    </p>
                                </div>
                                <a href="{{ route('rrhh.archivo', [$e->id, $archivo->id]) }}" class="shrink-0 rounded-lg border border-ipv-blue/20 px-3 py-1.5 text-xs font-medium text-ipv-blue hover:bg-ipv-blue/5 dark:border-white/15 dark:text-sky-300">Descargar</a>
                            </li>
                        @empty
                            <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin archivos adjuntos.</li>
                        @endforelse
                    </ul>
                    @break

                @default
                    <x-rrhh.datos-empleado :empleado="$e" :nivel="$ficha['nivel']" />
            @endswitch
        </section>
    @endif
</div>
