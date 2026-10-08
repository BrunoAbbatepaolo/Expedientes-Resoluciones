<div class="space-y-5" @if ($vista === 'hoy') wire:poll.60s @endif>
    <x-rrhh.encabezado titulo="Mi equipo" subtitulo="Asistencia y licencias de tu oficina y de las oficinas que dependen de ella.">
        <label class="block @if ($vista === 'pedidos') hidden @endif">
            <span class="sr-only">Oficina</span>
            <select wire:model.live="oficinaId"
                class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-[#141c26] dark:text-ipv-ink-dark">
                @if ($vista !== 'mes')
                    <option value="">Todas mis oficinas ({{ $oficinas->count() }})</option>
                @endif
                @foreach ($oficinas as $ofi)
                    <option value="{{ $ofi->id }}">{{ $ofi->nombre }}</option>
                @endforeach
            </select>
        </label>
    </x-rrhh.encabezado>

    <nav class="flex gap-1" aria-label="Vistas">
        @foreach ($vistas as $clave => $etiqueta)
            <button type="button" wire:click="$set('vista', '{{ $clave }}')"
                @if ($vista === $clave) aria-current="page" @endif
                class="rounded-lg px-3 py-1.5 text-sm transition {{ $vista === $clave ? 'bg-ipv-blue font-semibold text-white' : 'text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5' }}">
                {{ $etiqueta }}
                @if ($clave === 'pedidos' && $pedidosPendientes > 0)
                    <span class="ml-1 rounded-full bg-ipv-gold px-1.5 text-xs font-semibold text-[#5a3d00]">{{ $pedidosPendientes }}</span>
                @endif
            </button>
        @endforeach
    </nav>

    <x-rrhh.aviso-error :mostrar="$error" />

    @unless ($error)
        <section class="sirex-glass-card rounded-[14px] p-4" wire:loading.class="opacity-60">
            @if ($vista === 'mes')
                <div class="mb-3 flex justify-end"><x-rrhh.selector-mes :mes="$mesActual" /></div>
                <x-rrhh.grilla :grilla="$grilla ?? ['dias' => [], 'filas' => []]" />
            @elseif ($vista === 'pedidos')
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($pedidosAprobar ?? [] as $p)
                        <li wire:key="aprobar-{{ $p->id }}" class="space-y-2 py-3">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $p->user?->name }}
                                    <span class="font-normal text-ipv-ink/60 dark:text-ipv-ink-dark/60">· {{ $p->tipo_nombre }}</span>
                                </span>
                                <span class="text-ipv-ink/65 dark:text-ipv-ink-dark/65">{{ $p->desde->format('d/m/Y') }} – {{ $p->hasta->format('d/m/Y') }} · {{ $p->dias_habiles }} hábiles</span>
                            </div>
                            @if ($p->observacion)<p class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">“{{ $p->observacion }}”</p>@endif
                            @if ($p->adjunto_nombre)
                                <a href="{{ route('rrhh.pedido.adjunto', $p->id) }}" target="_blank" class="text-xs text-ipv-blue hover:underline dark:text-sky-300">Ver certificado ({{ $p->adjunto_nombre }})</a>
                            @endif
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="aprobarPedido({{ $p->id }})" class="sirex-btn rounded-lg bg-emerald-600 px-3 py-1 text-xs font-medium text-white">Aprobar</button>
                                <input type="text" wire:model="motivos.{{ $p->id }}" placeholder="Motivo del rechazo" maxlength="500"
                                    class="min-w-48 flex-1 rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-1 text-xs text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                                <button type="button" wire:click="rechazarPedido({{ $p->id }})" class="rounded-lg border border-ipv-magenta/40 px-3 py-1 text-xs font-medium text-ipv-magenta">Rechazar</button>
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay pedidos esperando tu aprobación.</li>
                    @endforelse
                </ul>
            @elseif ($vista === 'gente')
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($gente ?? [] as $e)
                        <li wire:key="gente-{{ $e->id }}">
                            <a href="{{ route('rrhh.ficha', $e->id) }}" wire:navigate class="flex flex-wrap items-baseline justify-between gap-2 py-2.5 hover:text-ipv-blue">
                                <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $e->apellido }}, {{ $e->nombre }}</span>
                                <span class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Legajo {{ $e->legajo }} · {{ $e->oficinaRrhh?->nombre }} @if ($e->funcion) · {{ $e->funcion }} @endif</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados activos.</li>
                    @endforelse
                </ul>
            @else
                <p class="mb-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Se actualiza solo cada minuto. "Sin fichar" todavía no es una falta: RRHH la registra después.</p>
                <x-rrhh.hoy :filas="$hoy ?? collect()" :agrupar="$oficinaId === null" />
            @endif
        </section>
    @endunless
</div>
