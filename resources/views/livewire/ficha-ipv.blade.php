<div class="space-y-5">
    <a href="{{ route('expedientes.ipv') }}" wire:navigate.hover
        class="group inline-flex items-center gap-1.5 text-sm font-medium text-ipv-ink/60 transition hover:text-ipv-blue dark:text-ipv-ink-dark/60 dark:hover:text-ipv-blue-light">
        <svg class="size-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Expedientes IPV
    </a>

    @if ($error)
        <div class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
            No se pudo consultar el sistema de expedientes del IPV. Probá de nuevo en unos minutos.
        </div>
    @else
        @php
            $exp = $ficha['expediente'];
            $ubicacion = $ficha['ubicacion'];
        @endphp

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Expediente</p>
                <h1 class="font-mono text-3xl font-bold tracking-tight text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->numero }}</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <livewire:seguir-expediente :numero="$exp->numero" :key="'seguir-'.$exp->id" />
                <a href="{{ route('expedientes.ipv.imprimir', $exp->id) }}" target="_blank" rel="noopener"
                    class="sirex-btn inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3.5 py-2 text-sm font-medium text-ipv-ink/80 transition hover:-translate-y-px hover:bg-white hover:shadow-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M7 14h10v7H7z" />
                    </svg>
                    Imprimir carátula e historial
                </a>
                @if (auth()->user()->permiso('expediente_editar') && $registros->where('oficina_id', auth()->user()->oficinaAsignadaId())->isEmpty())
                    <a href="{{ route('expedientes', ['registrar' => $exp->numero]) }}" wire:navigate
                        class="sirex-btn inline-flex items-center gap-1.5 rounded-lg bg-ipv-blue px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-px hover:bg-ipv-blue-dark hover:shadow">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Registrar en mi oficina
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Carátula --}}
            <section class="sirex-glass-card rounded-[14px] p-5 lg:col-span-2" aria-labelledby="caratula">
                <h2 id="caratula" class="mb-3 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Carátula</h2>
                <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Causante</dt>
                        <dd class="font-medium text-ipv-ink dark:text-ipv-ink-dark">
                            {{ $exp->causante?->nombre ?? '—' }}
                            @if ($exp->causante?->tipo)
                                <span class="ml-1 rounded-full bg-ipv-blue/10 px-2 py-0.5 text-[11px] font-semibold text-ipv-blue dark:text-ipv-blue-light">{{ $exp->causante->tipo->nombre }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Asunto</dt>
                        <dd class="text-ipv-ink dark:text-ipv-ink-dark">
                            @if ($exp->tipoAsunto)
                                <span class="font-medium">{{ $exp->tipoAsunto->nombre }}.</span>
                            @endif
                            {{ $exp->asunto ?: ($exp->tipoAsunto ? '' : '—') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Iniciado</dt>
                        <dd class="text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->fecha?->format('d/m/Y') ?? '—' }} {{ $exp->hora ? substr($exp->hora, 0, 5) : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Folios / tomo</dt>
                        <dd class="text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->folio ?? '—' }} / {{ $exp->tomo ?? '—' }}</dd>
                    </div>
                    @if ($exp->resolucion)
                        <div>
                            <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Resolución</dt>
                            <dd class="text-ipv-ink dark:text-ipv-ink-dark">{{ $exp->resolucion }}</dd>
                        </div>
                    @endif
                    @if ($exp->observacion)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Observación</dt>
                            <dd class="whitespace-pre-line text-ipv-ink/85 dark:text-ipv-ink-dark/85">{{ $exp->observacion }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Ubicación actual --}}
            <section class="sirex-glass-card flex flex-col justify-between rounded-[14px] p-5" aria-labelledby="ubicacion">
                <div>
                    <h2 id="ubicacion" class="text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Está en</h2>
                    <p class="mt-2 text-lg font-semibold leading-snug text-ipv-ink dark:text-ipv-ink-dark">{{ $ubicacion->oficina ?? 'Sin pases registrados' }}</p>
                    @if ($ubicacion)
                        <p class="mt-1 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">desde el {{ $ubicacion->fecha_hora?->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                @if ($ubicacion)
                    <div class="mt-4 flex items-center justify-between">
                        <x-dias-badge :dias="$ubicacion->dias" class="px-3 py-1 text-sm" />
                        <span class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $ficha['pases']->count() }} pases en total</span>
                    </div>
                @endif
            </section>
        </div>

        {{-- Línea de tiempo --}}
        <section class="sirex-glass-card rounded-[14px] p-5" aria-labelledby="recorrido">
            <h2 id="recorrido" class="mb-4 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Recorrido</h2>

            @if ($ficha['pases']->isEmpty())
                <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">Este expediente no tiene pases registrados.</p>
            @else
                <ol class="relative ml-2 border-l-2 border-ipv-blue/15 dark:border-white/10">
                    @foreach ($ficha['pases']->reverse() as $pase)
                        <li wire:key="pase-{{ $loop->index }}" class="group relative pb-5 pl-6 last:pb-0">
                            <span class="absolute -left-[7px] top-1.5 size-3 rounded-full ring-4 transition-transform group-hover:scale-125
                                {{ $pase->actual ? 'bg-ipv-gold ring-ipv-gold/25' : 'bg-ipv-blue/60 ring-white dark:bg-ipv-blue-light/60 dark:ring-[#0f1720]' }}"></span>
                            <div class="flex flex-col gap-0.5 rounded-lg px-3 py-2 transition-colors group-hover:bg-ipv-blue/[0.04] dark:group-hover:bg-white/[0.03] sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        {{ $pase->oficina }}
                                        @if ($pase->actual)
                                            <span class="ml-1 rounded-full bg-ipv-gold/25 px-2 py-0.5 text-[11px] font-bold uppercase text-ipv-gold-ink dark:text-ipv-gold-light">Actual</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                                        {{ $pase->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                        @if ($pase->empleado) · pasó {{ $pase->empleado }} @endif
                                        @if ($pase->folio) · {{ $pase->folio }} folios @endif
                                    </p>
                                    @if ($pase->observacion)
                                        <p class="mt-1 text-sm text-ipv-ink/75 dark:text-ipv-ink-dark/75">{{ $pase->observacion }}</p>
                                    @endif
                                </div>
                                <x-dias-badge :dias="$pase->dias" class="self-start" />
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Relacionados --}}
            <section class="sirex-glass-card rounded-[14px] p-5" aria-labelledby="relacionados">
                <h2 id="relacionados" class="mb-3 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Expedientes relacionados</h2>
                @forelse ($ficha['relacionados'] as $rel)
                    <a href="{{ route('expedientes.ipv.ficha', $rel->id) }}" wire:navigate.hover wire:key="rel-{{ $rel->id }}"
                        class="mb-2 mr-2 inline-flex items-center rounded-full border border-ipv-blue/20 px-3 py-1 font-mono text-xs font-semibold text-ipv-blue transition hover:-translate-y-px hover:bg-ipv-blue hover:text-white dark:text-ipv-blue-light">
                        {{ $rel->numero }}
                    </a>
                @empty
                    <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin expedientes relacionados.</p>
                @endforelse
            </section>

            {{-- Agregados (adjuntos) y cabeceras --}}
            @if ($ficha['agregados']->isNotEmpty() || $ficha['cabeceras']->isNotEmpty())
                <section class="sirex-glass-card rounded-[14px] p-5 lg:col-span-2" aria-labelledby="adjuntos">
                    <h2 id="adjuntos" class="mb-3 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Expedientes agregados</h2>
                    @foreach (['cabeceras' => 'Está agregado a', 'agregados' => 'Tiene agregados'] as $clave => $titulo)
                        @if ($ficha[$clave]->isNotEmpty())
                            <div class="mb-3 last:mb-0">
                                <div class="mb-1.5 text-xs font-medium text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $titulo }}</div>
                                <ul class="space-y-1.5 text-sm">
                                    @foreach ($ficha[$clave] as $adj)
                                        <li wire:key="adj-{{ $clave }}-{{ $adj->id }}" class="flex flex-wrap items-baseline gap-x-2" style="padding-left: {{ ($adj->nivel - 1) * 1.25 }}rem">
                                            <a href="{{ route('expedientes.ipv.ficha', $adj->id) }}" wire:navigate.hover class="font-mono text-xs font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">{{ $adj->numero }}</a>
                                            <span class="text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $adj->causante }} · {{ \Illuminate\Support\Str::limit($adj->asunto, 90) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </section>
            @endif

            {{-- Lo que SiRex sabe del expediente --}}
            <section class="sirex-glass-card rounded-[14px] p-5" aria-labelledby="sirex">
                <h2 id="sirex" class="mb-3 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">En SiRex</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($registros as $reg)
                        <li wire:key="reg-{{ $reg->id }}" class="flex items-center justify-between gap-2">
                            <span class="text-ipv-ink/80 dark:text-ipv-ink-dark/80">Registrado en {{ $reg->oficinaById->nombre ?? 'otra oficina' }}</span>
                            <span class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ \Carbon\Carbon::parse($reg->fecha_ingreso)->format('d/m/Y') }}</span>
                        </li>
                    @empty
                        <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Ninguna oficina lo registró en SiRex.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Resoluciones vinculadas a este expediente (se enlazan por su número) --}}
            @if (auth()->user()->permiso('resolucion_ver'))
                @php
                    $oficinaPropia = auth()->user()->oficinaAsignadaId();
                @endphp
                <section class="sirex-glass-card rounded-[14px] p-5" aria-labelledby="resoluciones-vinculadas">
                    <h2 id="resoluciones-vinculadas" class="mb-3 text-sm font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Resoluciones</h2>
                    <ul class="-mx-2 space-y-1 text-sm">
                        @forelse ($resoluciones as $res)
                            @php
                                // Los PDF se sirven solo a la oficina que cargó la resolución (o si es una resolución vieja sin oficina).
                                $accesible = $res->oficina_id === null || (int) $res->oficina_id === (int) $oficinaPropia;
                                $urlPdf = $accesible ? $res->urlPdf() : null;
                                $tipoRes = $res->tipo ? \App\Services\Resoluciones\Tipos::nombre($res->tipo) : null;
                            @endphp
                            <li wire:key="res-{{ $res->id }}">
                                {{-- Toda la fila abre el PDF en otra pestaña. --}}
                                <a @if ($urlPdf) href="{{ $urlPdf }}" target="_blank" rel="noopener" title="Abrir el PDF en otra pestaña" @endif
                                    class="group flex items-center gap-3 rounded-lg px-2 py-2 {{ $urlPdf ? 'transition-colors hover:bg-ipv-blue/[0.07] dark:hover:bg-white/[0.06]' : 'cursor-default' }}">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-ipv-magenta/10 text-ipv-magenta dark:bg-ipv-magenta/20 dark:text-ipv-magenta-light" aria-hidden="true">
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-medium text-ipv-ink dark:text-ipv-ink-dark">Resolución {{ $res->numero_resolucion ?: 's/n' }}</span>
                                        <span class="block truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $res->fecha ? \Carbon\Carbon::parse($res->fecha)->format('d/m/Y') : 'Sin fecha' }}{{ $tipoRes ? ' · '.$tipoRes : '' }}</span>
                                    </span>
                                    @if ($urlPdf)
                                        <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-ipv-blue dark:text-ipv-blue-light">
                                            Ver PDF
                                            <svg class="size-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8" /></svg>
                                        </span>
                                    @else
                                        <span class="shrink-0 text-xs text-ipv-ink/45 dark:text-ipv-ink-dark/45">{{ $accesible ? 'Sin PDF' : 'De otra oficina' }}</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Todavía no tiene resoluciones.</li>
                        @endforelse
                    </ul>
                </section>
            @endif
        </div>
    @endif
</div>
