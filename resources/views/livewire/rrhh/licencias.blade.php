<div class="space-y-5">
    <x-rrhh.encabezado titulo="Licencias" subtitulo="Licencias y saldo de vacaciones cargados en el sistema de personal." />

    <nav class="flex flex-wrap gap-1" aria-label="Vistas">
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

    <div class="sirex-glass-card grid gap-3 rounded-[14px] p-3 sm:grid-cols-2 {{ $vista === 'listado' ? 'xl:grid-cols-5' : '' }} {{ in_array($vista, ['pedidos', 'tipos']) ? 'hidden' : '' }}">
        <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaId" vacio="Todas las oficinas" :opciones="$oficinas->pluck('nombre', 'id')" />
        @if ($vista === 'listado')
            <input type="search" wire:model.live.debounce.400ms="texto" placeholder="Apellido o legajo"
                class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
            <x-rrhh.select etiqueta="Tipo" wire:model.live="tipoId" vacio="Todos los tipos" :opciones="$tipos" />
            <input type="date" wire:model.live="desde" title="Desde (por defecto, este mes)" class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
            <input type="date" wire:model.live="hasta" title="Hasta" class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        @endif
    </div>

    <x-rrhh.aviso-error :mostrar="$error" />

    @unless ($error)
        <section class="sirex-glass-card rounded-[14px] p-4" wire:loading.class="opacity-60">
            @if ($vista === 'pedidos')
                <div class="space-y-6">
                    <div>
                        <h2 class="mb-2 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Por confirmar</h2>
                        <p class="mb-2 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Al confirmar, cargá la licencia en el sistema viejo (todavía no se escribe ahí desde SiRex).</p>
                        <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                            @forelse ($porConfirmar ?? [] as $p)
                                <li wire:key="conf-{{ $p->id }}" class="space-y-2 py-3">
                                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                                        <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $p->user?->name }}
                                            <span class="font-normal text-ipv-ink/60 dark:text-ipv-ink-dark/60">· {{ $p->tipo_nombre }}</span>
                                        </span>
                                        <span class="text-ipv-ink/65 dark:text-ipv-ink-dark/65">{{ $p->desde->format('d/m/Y') }} – {{ $p->hasta->format('d/m/Y') }} · {{ $p->dias_habiles }} hábiles</span>
                                    </div>
                                    <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                                        {{ $p->jefe ? 'Aprobó '.$p->jefe->name.' el '.$p->jefe_at?->format('d/m/Y') : $p->jefe_motivo }}
                                        @if ($p->observacion) · “{{ $p->observacion }}” @endif
                                    </p>
                                    @if ($p->adjunto_nombre)
                                        <a href="{{ route('rrhh.pedido.adjunto', $p->id) }}" target="_blank" class="text-xs text-ipv-blue hover:underline dark:text-sky-300">Ver certificado ({{ $p->adjunto_nombre }})</a>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" wire:click="confirmarPedido({{ $p->id }})" class="sirex-btn rounded-lg bg-emerald-600 px-3 py-1 text-xs font-medium text-white">Confirmar</button>
                                        <input type="text" wire:model="motivos.{{ $p->id }}" placeholder="Motivo del rechazo" maxlength="500"
                                            class="min-w-48 flex-1 rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-1 text-xs text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                                        <button type="button" wire:click="rechazarPedido({{ $p->id }})" class="rounded-lg border border-ipv-magenta/40 px-3 py-1 text-xs font-medium text-ipv-magenta">Rechazar</button>
                                    </div>
                                </li>
                            @empty
                                <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay pedidos por confirmar.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h2 class="mb-2 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Últimos resueltos</h2>
                        <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                            @forelse ($historial ?? [] as $p)
                                <li wire:key="hist-{{ $p->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2">
                                    <span class="text-ipv-ink dark:text-ipv-ink-dark">{{ $p->user?->name }} · {{ $p->tipo_nombre }}
                                        <span class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $p->desde->format('d/m') }} – {{ $p->hasta->format('d/m/Y') }}</span>
                                    </span>
                                    <x-rrhh.estado :estado="$p->estado" />
                                </li>
                            @empty
                                <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Todavía no hay pedidos resueltos.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @elseif ($vista === 'tipos')
                <p class="mb-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Tipos de licencia que los empleados pueden pedir desde Mi RRHH. Los demás los carga RRHH directamente.</p>
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($tiposPedibles ?? [] as $t)
                        <li wire:key="tipo-{{ $t->id }}" class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span class="text-ipv-ink dark:text-ipv-ink-dark">{{ $t->nombre }}
                                @if ($t->permite_retroactivo)<span class="ml-1 rounded bg-black/[0.05] px-1.5 text-xs dark:bg-white/10">retroactivo</span>@endif
                                @if ($t->requiere_adjunto)<span class="ml-1 rounded bg-black/[0.05] px-1.5 text-xs dark:bg-white/10">pide certificado</span>@endif
                            </span>
                            <button type="button" wire:click="quitarTipo({{ $t->id }})" wire:confirm="¿Quitar este tipo?" class="text-xs text-ipv-magenta hover:underline">Quitar</button>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay tipos habilitados: los empleados todavía no pueden pedir licencias.</li>
                    @endforelse
                </ul>
                <form wire:submit="guardarTipo" class="mt-4 flex flex-wrap items-end gap-3 border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                    <x-rrhh.select etiqueta="Tipo de licencia" wire:model="tipoPedibleId" vacio="Elegí un tipo…" :opciones="$tipos" class="sm:w-72" />
                    <label class="flex items-center gap-1.5 text-sm text-ipv-ink dark:text-ipv-ink-dark"><input type="checkbox" wire:model="tipoRetroactivo" class="rounded"> Permite fecha pasada</label>
                    <label class="flex items-center gap-1.5 text-sm text-ipv-ink dark:text-ipv-ink-dark"><input type="checkbox" wire:model="tipoAdjunto" class="rounded"> Exige certificado</label>
                    <button type="submit" class="sirex-btn rounded-lg bg-ipv-blue px-4 py-1.5 text-sm font-medium text-white">Agregar</button>
                </form>
            @elseif ($vista === 'adeudadas')
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($adeudadas ?? [] as $fila)
                        <li wire:key="adeu-{{ $fila['empleado']->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2.5">
                            <a href="{{ route('rrhh.ficha', $fila['empleado']->id) }}" wire:navigate class="font-medium text-ipv-ink hover:text-ipv-blue hover:underline dark:text-ipv-ink-dark">
                                {{ $fila['empleado']->apellido }}, {{ $fila['empleado']->nombre }}
                                <span class="text-xs font-normal text-ipv-ink/50 dark:text-ipv-ink-dark/50">· {{ $fila['empleado']->oficinaRrhh?->nombre }}</span>
                            </a>
                            <span class="text-ipv-ink/65 dark:text-ipv-ink-dark/65">
                                <strong class="text-ipv-ink dark:text-ipv-ink-dark">{{ $fila['vacaciones']->pendientes }} días</strong>
                                de {{ $fila['vacaciones']->corresponden }} ({{ $fila['vacaciones']->tomadas + $fila['vacaciones']->anticipadas }} tomados)
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">Nadie tiene vacaciones adeudadas.</li>
                    @endforelse
                </ul>
            @elseif (isset($licencias))
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($licencias as $lic)
                        <li wire:key="lic-{{ $lic->id }}" class="flex flex-wrap items-baseline justify-between gap-2 py-2.5">
                            <span>
                                @if ($lic->empleado)
                                    <a href="{{ route('rrhh.ficha', $lic->empleado->id) }}" wire:navigate class="font-medium text-ipv-ink hover:text-ipv-blue hover:underline dark:text-ipv-ink-dark">{{ $lic->empleado->apellido }}, {{ $lic->empleado->nombre }}</a>
                                @endif
                                <span class="text-ipv-ink/65 dark:text-ipv-ink-dark/65">· {{ $lic->tipo?->descripcion ?? 'Licencia' }}</span>
                            </span>
                            <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $lic->desde->format('d/m/Y') }} – {{ $lic->hasta->format('d/m/Y') }} · {{ $lic->desde->diffInDays($lic->hasta) + 1 }} días</span>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay licencias con esos filtros.</li>
                    @endforelse
                </ul>
                <div class="mt-3">{{ $licencias->links() }}</div>
            @endif
        </section>
    @endunless
</div>
