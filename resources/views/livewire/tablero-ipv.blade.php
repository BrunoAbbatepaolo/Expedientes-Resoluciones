<div class="grid gap-4 lg:grid-cols-3">
    <section class="space-y-4 lg:col-span-2" aria-labelledby="tablero-ipv">
        <div class="flex items-end justify-between gap-2">
            <div>
                <h2 id="tablero-ipv" class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mi oficina en el IPV</h2>
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $oficinaIpv ?? 'Oficina no vinculada' }} · según el sistema de expedientes</p>
            </div>
            @if ($oficinaIpvId)
                <a href="{{ route('expedientes.ipv', ['oficina' => $oficinaIpvId]) }}" wire:navigate.hover
                    class="text-sm font-medium text-ipv-blue hover:underline dark:text-ipv-blue-light">Ver todos →</a>
            @endif
        </div>

        @if ($error)
            <div class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
                No se pudo consultar el sistema de expedientes del IPV. Probá de nuevo en unos minutos.
            </div>
        @elseif (! $resumen)
            <div class="sirex-glass-card rounded-[14px] p-5 text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                Tu oficina todavía no está vinculada con el sistema de expedientes del IPV, por eso no hay métricas.
                @if (auth()->user()->permiso('lista_usuario_editar'))
                    <a href="{{ route('oficinas') }}" wire:navigate class="font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">Vincularla en Oficinas</a>.
                @else
                    Pedíselo a un administrador.
                @endif
            </div>
        @else
            <div class="grid grid-cols-3 gap-3">
                @foreach ([
                    ['Están en mi oficina', $resumen['en_oficina'], 'text-ipv-blue dark:text-ipv-blue-light'],
                    ['Demorados (+'.$diasDemora.' días)', $resumen['demorados'], $resumen['demorados'] ? 'text-ipv-magenta dark:text-ipv-magenta-light' : 'text-ipv-ink dark:text-ipv-ink-dark'],
                    ['Recibidos (30 días)', $resumen['recibidos_30d'], 'text-ipv-ink dark:text-ipv-ink-dark'],
                ] as [$etiqueta, $valor, $color])
                    <div wire:key="kpi-{{ $loop->index }}"
                        class="sirex-glass-card group rounded-[14px] p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-xs font-medium text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $etiqueta }}</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums {{ $color }}">{{ number_format($valor, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                <div class="flex items-center justify-between border-b border-ipv-blue/10 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">Los más demorados en mi oficina</h3>
                </div>
                <ul class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($demorados as $exp)
                        <li wire:key="dem-{{ $exp->id }}">
                            <a href="{{ route('expedientes.ipv.ficha', $exp->id) }}" wire:navigate.hover
                                class="group flex items-center justify-between gap-3 px-4 py-2.5 transition-colors hover:bg-ipv-blue/[0.04] dark:hover:bg-white/[0.04]">
                                <span class="min-w-0">
                                    <span class="block font-mono text-sm font-semibold text-ipv-blue group-hover:underline dark:text-ipv-blue-light">{{ $exp->numero }}</span>
                                    <span class="block truncate text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $exp->causante }}</span>
                                </span>
                                <x-dias-badge :dias="$exp->dias_en_ubicacion" />
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">Nada demorado. 🎉</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </section>

    {{-- Seguimientos --}}
    <section class="sirex-glass-card overflow-hidden rounded-[14px]" aria-labelledby="seguimientos">
        <div class="border-b border-ipv-blue/10 px-4 py-3 dark:border-white/10">
            <h2 id="seguimientos" class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">Expedientes que sigo</h2>
        </div>
        <ul class="divide-y divide-ipv-blue/10 dark:divide-white/10">
            @forelse ($seguimientos as $s)
                <li wire:key="seg-{{ $s->id }}" class="px-4 py-2.5 {{ $s->se_movio ? 'bg-ipv-gold/10' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            @if ($s->ipv)
                                <a href="{{ route('expedientes.ipv.ficha', $s->ipv->id) }}" wire:navigate.hover
                                    class="font-mono text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">{{ $s->numero_exp }}</a>
                                <p class="truncate text-xs text-ipv-ink/65 dark:text-ipv-ink-dark/65">{{ $s->ipv->ubicacion ?? 'Sin pases' }}</p>
                            @else
                                <span class="font-mono text-sm font-semibold text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $s->numero_exp }}</span>
                            @endif
                        </div>
                        @if ($s->se_movio)
                            <button type="button" wire:click="marcarVisto({{ $s->id }})" title="Marcar como visto"
                                class="shrink-0 rounded-full bg-ipv-gold/30 px-2 py-0.5 text-[11px] font-bold uppercase text-ipv-gold-ink transition hover:bg-ipv-gold/50 dark:text-ipv-gold-light">
                                Se movió
                            </button>
                        @else
                            <button type="button" wire:click="dejarDeSeguir({{ $s->id }})" aria-label="Dejar de seguir {{ $s->numero_exp }}"
                                class="shrink-0 text-ipv-ink/35 transition hover:text-ipv-magenta dark:text-ipv-ink-dark/35">&times;</button>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                    Tocá <span class="font-semibold">Seguir</span> en la ficha de un expediente para ver acá cuando se mueva.
                </li>
            @endforelse
        </ul>
    </section>
</div>
