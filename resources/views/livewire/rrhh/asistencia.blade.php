<div class="space-y-5" @if ($vista === 'hoy') wire:poll.60s @endif>
    <x-rrhh.encabezado titulo="Asistencia" subtitulo="Fichadas de los relojes y estado de asistencia de todo el instituto.">
        @if ($vista !== 'fichadas')
            <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaId" :vacio="$vista === 'hoy' ? 'Todo el instituto' : null" :opciones="$oficinas->pluck('nombre', 'id')" class="sm:w-72" />
        @endif
    </x-rrhh.encabezado>

    @if ($lector)
        <div role="status" class="flex flex-wrap items-center justify-between gap-2 rounded-lg px-4 py-2.5 text-sm {{ $lector['alerta'] ? 'border border-ipv-magenta/30 bg-ipv-magenta/10 text-ipv-magenta dark:text-ipv-magenta-light' : 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300' }}">
            <span>{{ $lector['alerta'] ? $lector['mensaje'] : 'Lector de relojes funcionando.' }}</span>
            <span class="text-xs opacity-80">
                Última fichada: {{ $lector['ultima_fichada']?->format('d/m H:i') ?? '—' }} · Último chequeo: {{ $lector['ultimo_chequeo']?->format('d/m H:i') ?? '—' }}
            </span>
            <span class="flex flex-wrap items-center gap-2">
                @if ($avisoSincronizacion)
                    <span class="text-xs {{ $avisoSincronizacion['error'] ? 'font-medium' : 'opacity-80' }}">{{ $avisoSincronizacion['texto'] }}</span>
                @endif
                <button type="button" wire:click="sincronizarAhora" wire:loading.attr="disabled" wire:target="sincronizarAhora"
                    class="rounded-lg border border-current/30 px-3 py-1 text-xs font-semibold transition hover:bg-black/[0.05] disabled:cursor-wait disabled:opacity-60 dark:hover:bg-white/10">
                    <span wire:loading.remove wire:target="sincronizarAhora">Sincronizar ahora</span>
                    <span wire:loading wire:target="sincronizarAhora">Sincronizando…</span>
                </button>
            </span>
        </div>

        @if (! empty($lector['relojes']))
            <ul class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3" aria-label="Estado de cada reloj">
                @foreach ($lector['relojes'] as $r)
                    <li wire:key="reloj-{{ $r['id'] }}" class="sirex-glass-card rounded-[12px] p-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex min-w-0 items-center gap-2 font-medium text-ipv-ink dark:text-ipv-ink-dark">
                                <span class="sirex-pulso {{ $r['estado'] === 'ok' ? '' : 'sirex-pulso-falla' }}" aria-hidden="true"></span>
                                <span class="truncate">{{ $r['nombre'] }}</span>
                            </span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $r['estado'] === 'ok' ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300' : 'bg-ipv-magenta/15 text-ipv-magenta dark:text-ipv-magenta-light' }}">
                                {{ ['ok' => 'Funciona', 'sin_contacto' => 'Sin contacto', 'hora_desviada' => 'Hora desviada'][$r['estado']] ?? $r['estado'] }}
                            </span>
                        </div>
                        <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $r['ip'] }} · {{ $r['tipo'] }}@if ($r['modelo']) · {{ $r['modelo'] }}@endif</p>
                        <dl class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                            <div><dt class="opacity-60">Último contacto</dt><dd>{{ $r['ultimo_contacto']?->format('d/m H:i') ?? 'Nunca' }}</dd></div>
                            <div><dt class="opacity-60">Última fichada</dt><dd>{{ $r['ultima_fichada']?->format('d/m H:i') ?? '—' }}</dd></div>
                            <div><dt class="opacity-60">Fichadas en el reloj</dt><dd>{{ $r['registros'] !== null ? number_format($r['registros'], 0, ',', '.') : '—' }}</dd></div>
                            <div><dt class="opacity-60">Hora del reloj</dt><dd>{{ $r['desvio_segundos'] === null ? '—' : (abs($r['desvio_segundos']) < 60 ? 'Correcta' : round(abs($r['desvio_segundos']) / 60).' min '.($r['desvio_segundos'] > 0 ? 'adelantada' : 'atrasada')) }}</dd></div>
                        </dl>
                        @if ($r['fallas_seguidas'] > 0 && $r['ultimo_error'])
                            <p class="mt-2 text-xs text-ipv-magenta">{{ $r['fallas_seguidas'] }} {{ $r['fallas_seguidas'] === 1 ? 'falla seguida' : 'fallas seguidas' }}: {{ $r['ultimo_error'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    <nav class="flex flex-wrap gap-1" aria-label="Vistas">
        @foreach (\App\Livewire\Rrhh\Asistencia::VISTAS as $clave => $etiqueta)
            <button type="button" wire:click="$set('vista', '{{ $clave }}')"
                @if ($vista === $clave) aria-current="page" @endif
                class="rounded-lg px-3 py-1.5 text-sm transition {{ $vista === $clave ? 'bg-ipv-blue font-semibold text-white' : 'text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5' }}">
                {{ $etiqueta }}
            </button>
        @endforeach
    </nav>

    <x-rrhh.aviso-error :mostrar="$error" />

    @unless ($error)
        <section class="sirex-glass-card rounded-[14px] p-4" wire:loading.class="opacity-60">
            @if ($vista === 'mes')
                <div class="mb-3 flex justify-end"><x-rrhh.selector-mes :mes="$mesActual" /></div>
                <x-rrhh.grilla :grilla="$grilla ?? ['dias' => [], 'filas' => []]" />
            @elseif ($vista === 'fichadas')
                <div class="mb-3 grid gap-2 sm:grid-cols-4">
                    <input type="search" wire:model.live.debounce.400ms="legajo" inputmode="numeric" placeholder="Legajo"
                        class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                    <x-rrhh.select etiqueta="Reloj" wire:model.live="reloj" vacio="Todos los relojes" :opciones="collect($relojes ?? [])->mapWithKeys(fn ($r) => [$r => $r])" />
                    <input type="date" wire:model.live="desde" title="Desde" class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                    <input type="date" wire:model.live="hasta" title="Hasta" class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                </div>
                <p class="mb-2 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Por defecto, los últimos 7 días. Como máximo se consultan {{ \App\Services\Rrhh\Contracts\Asistencias::MAX_DIAS_FICHADAS }} días.</p>
                @if (isset($fichadas))
                    <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                        @forelse ($fichadas as $f)
                            <li wire:key="fich-{{ $f->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2">
                                <span class="text-ipv-ink dark:text-ipv-ink-dark">
                                    @if ($f->empleado)
                                        <a href="{{ route('rrhh.ficha', $f->empleado->id) }}" wire:navigate class="hover:text-ipv-blue hover:underline">{{ $f->empleado->apellido }}, {{ $f->empleado->nombre }}</a>
                                    @else
                                        Tarjeta {{ $f->card_id }} <span class="text-xs text-ipv-ink/50">(sin empleado)</span>
                                    @endif
                                </span>
                                <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $f->fecha_hora->format('d/m/Y H:i') }} · {{ $f->nombre_reloj }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay fichadas con esos filtros.</li>
                        @endforelse
                    </ul>
                    <div class="mt-3">{{ $fichadas->links() }}</div>
                @endif
            @else
                <p class="mb-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Se actualiza solo cada minuto. "Sin fichar" todavía no es una falta: RRHH la registra después.</p>
                <x-rrhh.hoy :filas="$hoy ?? collect()" :agrupar="$oficinaId === null" />
            @endif
        </section>
    @endunless
</div>
