@php
    $entrada = 'w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark';
    $etiquetas = [
        'created' => 'Alta', 'updated' => 'Modificación', 'deleted' => 'Baja',
        'login' => 'Ingreso', 'login_fallido' => 'Ingreso fallido',
        'permisos' => 'Permisos', 'oficina' => 'Cambio de oficina', 'usuario_creado' => 'Usuario creado',
        'email' => 'Cambio de email', 'clave_restablecida' => 'Clave restablecida', 'clave_inicial_cambiada' => 'Clave inicial cambiada',
    ];
    $tipo = fn ($tipo) => class_basename($tipo);
    $persona = fn ($u) => $u ? trim($u->nombre.' '.$u->apellido) : 'Sistema / sin sesión';
@endphp

<div class="sirex-admin flex min-w-0 flex-1 flex-col gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Administración</h1>
        <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">Auditoría, métricas, accesos, firmantes de resoluciones y estado del sistema.</p>
    </div>

    <nav class="flex flex-wrap gap-1.5" aria-label="Secciones de administración">
        @foreach (\App\Livewire\Admin\PanelAdmin::PESTANIAS as $clave => $titulo)
            <button type="button" wire:click="irA('{{ $clave }}')" @if ($tab === $clave) aria-current="page" @endif
                class="sirex-admin-tab {{ $tab === $clave ? 'is-on' : '' }}">{{ $titulo }}</button>
        @endforeach
    </nav>

    {{-- ── Resumen ── --}}
    @if ($tab === 'resumen')
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
            @foreach ($metricas as $i => [$etiqueta, $valor])
                <div class="sirex-glass-card sirex-admin-dato rounded-[14px] p-4" style="--i: {{ $i }}">
                    <div class="text-xs font-semibold uppercase tracking-wide text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $etiqueta }}</div>
                    <div class="mt-2 text-[26px] font-bold leading-none text-ipv-ink dark:text-ipv-ink-dark">{{ number_format($valor, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <section class="sirex-glass-card rounded-[14px] p-4 lg:col-span-2">
                <h2 class="mb-3 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Actividad de los últimos 14 días</h2>
                <div class="flex h-40 items-end gap-1.5" role="img" aria-label="Cambios registrados por día">
                    @foreach ($serie as $i => $d)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1" title="{{ $d['dia'] }}: {{ $d['total'] }}">
                            <span class="text-[10px] text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $d['total'] ?: '' }}</span>
                            <div class="sirex-admin-barra w-full rounded-t bg-ipv-blue dark:bg-ipv-blue-light"
                                style="height: {{ max(2, round($d['total'] / $maximo * 100)) }}%; --i: {{ $i }}"></div>
                            <span class="text-[10px] text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ substr($d['dia'], 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="sirex-glass-card rounded-[14px] p-4">
                <h2 class="mb-3 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Usuarios más activos (7 días)</h2>
                <ol class="space-y-2 text-sm">
                    @forelse ($masActivos as $a)
                        <li class="flex items-center justify-between gap-2">
                            <span class="truncate text-ipv-ink dark:text-ipv-ink-dark">{{ $a['nombre'] }}</span>
                            <span class="rounded-full bg-ipv-blue/12 px-2 py-0.5 text-xs font-semibold text-ipv-blue dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light">{{ $a['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin actividad todavía.</li>
                    @endforelse
                </ol>

                <h2 class="mb-3 mt-5 font-semibold text-ipv-ink dark:text-ipv-ink-dark">Por tipo (30 días)</h2>
                <ul class="space-y-1.5 text-sm">
                    @forelse ($porTipo as $evento => $total)
                        <li class="flex items-center justify-between gap-2">
                            <span class="text-ipv-ink/80 dark:text-ipv-ink-dark/80">{{ $etiquetas[$evento] ?? $evento }}</span>
                            <span class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $total }}</span>
                        </li>
                    @empty
                        <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin registros.</li>
                    @endforelse
                </ul>
            </section>
        </div>

    {{-- ── Auditoría y Accesos ── --}}
    @elseif ($tab === 'auditoria' || $tab === 'accesos')
        <div class="sirex-glass-card grid gap-3 rounded-[14px] p-4 sm:grid-cols-2 lg:grid-cols-5">
            <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="{{ $soloAccesos ? 'Email, usuario o IP' : 'Usuario, IP o dato' }}" class="{{ $entrada }} lg:col-span-2" />
            <select wire:model.live="evento" class="{{ $entrada }}">
                <option value="">Todos los eventos</option>
                @foreach ($eventos as $e)
                    <option value="{{ $e }}">{{ $etiquetas[$e] ?? $e }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="desde" aria-label="Desde" class="{{ $entrada }}" />
            <input type="date" wire:model.live="hasta" aria-label="Hasta" class="{{ $entrada }}" />
            @if ($buscar !== '' || $evento !== '' || $desde !== '' || $hasta !== '')
                <button type="button" wire:click="limpiar" class="text-left text-sm text-ipv-blue hover:underline dark:text-ipv-blue-light">Limpiar filtros</button>
            @endif
        </div>

        <div class="sirex-glass-card overflow-x-auto rounded-[14px]">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">{{ $soloAccesos ? 'Email' : 'Sobre' }}</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">IP</th>
                        @unless ($soloAccesos) <th class="px-4 py-3"><span class="sr-only">Detalle</span></th> @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($registros as $r)
                        <tr wire:key="a{{ $r->id }}" class="sirex-admin-fila text-ipv-ink dark:text-ipv-ink-dark">
                            <td class="whitespace-nowrap px-4 py-2.5">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2.5">
                                <span class="sirex-admin-chip" data-evento="{{ $r->event }}">{{ $etiquetas[$r->event] ?? $r->event }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if ($soloAccesos)
                                    {{ $r->new_values['email'] ?? '—' }}
                                @else
                                    {{ $tipo($r->auditable_type) }} #{{ $r->auditable_id }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5">{{ $persona($r->user) }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5">{{ $r->ip_address ?? '—' }}</td>
                            @unless ($soloAccesos)
                                <td class="px-4 py-2.5 text-right">
                                    <button type="button" wire:click="alternar({{ $r->id }})" class="text-xs font-medium text-ipv-blue hover:underline dark:text-ipv-blue-light">
                                        {{ $abierto === $r->id ? 'Ocultar' : 'Detalle' }}
                                    </button>
                                </td>
                            @endunless
                        </tr>
                        @if (! $soloAccesos && $abierto === $r->id)
                            <tr wire:key="d{{ $r->id }}" class="sirex-admin-fila bg-ipv-blue/[0.04] dark:bg-white/[0.03]">
                                <td colspan="6" class="px-4 py-3">
                                    <div class="grid gap-3 text-xs md:grid-cols-2">
                                        @foreach (['Antes' => $r->old_values, 'Después' => $r->new_values] as $titulo => $valores)
                                            <div>
                                                <div class="mb-1 font-semibold text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $titulo }}</div>
                                                <pre class="max-h-56 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/5 p-2 dark:bg-white/5">{{ $valores ? json_encode(\Illuminate\Support\Arr::except($valores, ['password', 'remember_token']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay registros con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $registros->links() }}

    {{-- ── Firmantes de resoluciones ── --}}
    @elseif ($tab === 'firmantes')
        <livewire:admin.firmantes />

    {{-- ── Sistema ── --}}
    @else
        <dl class="sirex-glass-card divide-y divide-ipv-blue/10 rounded-[14px] dark:divide-white/10">
            @foreach ($sistema as $etiqueta => $valor)
                <div class="flex flex-wrap items-baseline justify-between gap-2 px-4 py-3 text-sm">
                    <dt class="font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $etiqueta }}</dt>
                    <dd class="text-ipv-ink dark:text-ipv-ink-dark {{ str_contains($valor, 'Sin conexión') || str_contains($valor, 'DEBUG') ? '!text-ipv-magenta font-semibold' : '' }}">{{ $valor }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>
