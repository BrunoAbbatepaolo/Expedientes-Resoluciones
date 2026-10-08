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
                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Vacaciones pendientes (período {{ $vacaciones->periodo }})</p>
                <p class="mt-1 text-2xl font-semibold {{ $vacaciones->pendientes < 0 ? 'text-ipv-magenta' : 'text-ipv-ink dark:text-ipv-ink-dark' }}">{{ $vacaciones->pendientes }} <span class="text-sm font-normal">días hábiles</span></p>
                <p class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                    de {{ $vacaciones->corresponden }} · tomaste {{ $vacaciones->tomadas + $vacaciones->anticipadas }}
                </p>
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

        {{-- Pedir licencia --}}
        @if ($conPedidos)
            <section class="sirex-glass-card space-y-4 rounded-[14px] p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mis pedidos de licencia</h2>
                    @if ($tiposPedibles->isNotEmpty())
                        <button type="button" wire:click="$toggle('pidiendo')"
                            class="sirex-btn rounded-lg bg-ipv-blue px-4 py-1.5 text-sm font-medium text-white">
                            {{ $pidiendo ? 'Cerrar' : 'Pedir licencia' }}
                        </button>
                    @endif
                </div>

                @if ($pidiendo)
                    <form wire:submit="pedirLicencia" class="space-y-3 rounded-[12px] border border-ipv-blue/15 p-4 dark:border-white/10">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60 sm:col-span-3">Tipo de licencia
                                <select wire:model.live="pedido.tipo_ausencia_id"
                                    class="mt-1 w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-[#141c26] dark:text-ipv-ink-dark">
                                    <option value="">Elegí un tipo…</option>
                                    @foreach ($tiposPedibles as $t)
                                        <option value="{{ $t->tipo_ausencia_id }}">{{ $t->nombre }}{{ $t->requiere_adjunto ? ' (con certificado)' : '' }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">Desde
                                <input type="date" wire:model.live="pedido.desde" class="mt-1 w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                            </label>
                            <label class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">Hasta
                                <input type="date" wire:model.live="pedido.hasta" class="mt-1 w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                            </label>
                            <label class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">Certificado (PDF o imagen, opcional)
                                <input type="file" wire:model="adjunto" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 w-full text-sm">
                            </label>
                        </div>
                        @foreach (['pedido.tipo_ausencia_id', 'pedido.desde', 'pedido.hasta', 'pedido.observacion', 'adjunto'] as $campo)
                            @error($campo) <p class="text-xs text-ipv-magenta">{{ $message }}</p> @enderror
                        @endforeach

                        @if ($vistaPedido && $vistaPedido['dias'] > 0)
                            <p class="rounded-lg bg-ipv-blue/5 px-3 py-2 text-sm text-ipv-ink dark:bg-white/5 dark:text-ipv-ink-dark">
                                Son <strong>{{ $vistaPedido['dias'] }}</strong> {{ $vistaPedido['dias'] === 1 ? 'día hábil' : 'días hábiles' }}.
                                @if ($vistaPedido['vacaciones'])
                                    Te quedan <strong class="{{ $vistaPedido['despues'] < 0 ? 'text-ipv-magenta' : '' }}">{{ $vistaPedido['despues'] }}</strong> de vacaciones
                                    (ahora {{ $vistaPedido['pendientes'] }}, contando pedidos en trámite).
                                @endif
                            </p>
                        @endif

                        <label class="block text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">Observación (opcional)
                            <input type="text" wire:model="pedido.observacion" maxlength="500" class="mt-1 w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                        </label>
                        <button type="submit" wire:loading.attr="disabled" class="sirex-btn rounded-lg bg-ipv-blue px-4 py-1.5 text-sm font-medium text-white">Enviar pedido</button>
                    </form>
                @endif

                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($misPedidos as $p)
                        <li wire:key="pedido-{{ $p->id }}" class="space-y-1 py-2.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $p->tipo_nombre }}
                                    <span class="font-normal text-ipv-ink/60 dark:text-ipv-ink-dark/60">· {{ $p->desde->format('d/m/Y') }} – {{ $p->hasta->format('d/m/Y') }} · {{ $p->dias_habiles }} hábiles</span>
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $p->estado->clases() }}">{{ $p->estado->etiqueta() }}</span>
                                    @if ($p->estado === \App\Enums\EstadoPedido::PendienteJefe)
                                        <button type="button" wire:click="cancelarPedido({{ $p->id }})" wire:confirm="¿Cancelar este pedido?" class="text-xs text-ipv-magenta hover:underline">Cancelar</button>
                                    @endif
                                </span>
                            </div>
                            @if ($p->jefe_motivo && $p->estado === \App\Enums\EstadoPedido::RechazadoJefe)
                                <p class="text-xs text-ipv-magenta">Motivo del jefe: {{ $p->jefe_motivo }}</p>
                            @endif
                            @if ($p->rrhh_motivo)
                                <p class="text-xs text-ipv-magenta">Motivo de RRHH: {{ $p->rrhh_motivo }}</p>
                            @endif
                            @if ($p->estado === \App\Enums\EstadoPedido::Confirmado)
                                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Confirmado por RRHH el {{ $p->rrhh_at?->format('d/m/Y') }}.</p>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                            {{ $tiposPedibles->isEmpty() ? 'Todavía no hay tipos de licencia habilitados para pedir. Consultá con Recursos Humanos.' : 'No hiciste pedidos todavía.' }}
                        </li>
                    @endforelse
                </ul>
            </section>
        @endif

        {{-- Mis datos --}}
        <section class="sirex-glass-card rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Mis datos</h2>
            <x-rrhh.datos-empleado :empleado="$empleado" :nivel="\App\Enums\NivelDatoRrhh::Sensible" class="mt-3" />
            <p class="mt-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">¿Algún dato está mal? Avisale a Recursos Humanos.</p>
        </section>
    @endif
</div>
