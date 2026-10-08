<div class="space-y-5">
    <x-rrhh.encabezado titulo="Licencias" subtitulo="Licencias y saldo de vacaciones cargados en el sistema de personal." />

    <nav class="flex flex-wrap gap-1" aria-label="Vistas">
        @foreach (\App\Livewire\Rrhh\Licencias::VISTAS as $clave => $etiqueta)
            <button type="button" wire:click="$set('vista', '{{ $clave }}')"
                @if ($vista === $clave) aria-current="page" @endif
                class="rounded-lg px-3 py-1.5 text-sm transition {{ $vista === $clave ? 'bg-ipv-blue font-semibold text-white' : 'text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5' }}">
                {{ $etiqueta }}
            </button>
        @endforeach
    </nav>

    <div class="sirex-glass-card grid gap-3 rounded-[14px] p-3 sm:grid-cols-2 {{ $vista === 'listado' ? 'xl:grid-cols-5' : '' }}">
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
            @if ($vista === 'adeudadas')
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
