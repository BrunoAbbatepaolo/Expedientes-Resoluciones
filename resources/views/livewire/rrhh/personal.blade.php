<div class="space-y-4">
    <x-rrhh.encabezado titulo="Personal" subtitulo="Legajos del sistema de personal del IPV (solo consulta)." />

    <div class="sirex-glass-card grid gap-3 rounded-[14px] p-3 sm:grid-cols-2 xl:grid-cols-[2fr_1.5fr_1fr_1fr_1fr_auto]">
        <label class="relative block sm:col-span-2 xl:col-span-1">
            <span class="sr-only">Buscar</span>
            <input type="search" wire:model.live.debounce.400ms="texto" autofocus
                placeholder="Apellido, nombre, legajo o DNI"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:border-ipv-blue focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </label>
        <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaId" vacio="Todas las oficinas" :opciones="$oficinas->pluck('nombre', 'id')" />
        <x-rrhh.select etiqueta="Situación laboral" wire:model.live="situacionId" vacio="Toda situación" :opciones="$situaciones" />
        <x-rrhh.select etiqueta="Categoría" wire:model.live="categoriaId" vacio="Toda categoría" :opciones="$categorias" />
        <x-rrhh.select etiqueta="Estado" wire:model.live="estado" :opciones="['activos' => 'Activos', 'pasivos' => 'Pasivos', 'todos' => 'Todos']" />
        <button type="button" wire:click="limpiar" class="rounded-lg border border-ipv-blue/20 px-3 py-2 text-sm text-ipv-ink/70 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/70">Limpiar</button>
    </div>

    <x-rrhh.aviso-error :mostrar="$error" />

    @if ($empleados)
        <div class="flex items-center justify-between text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
            <span>{{ number_format($empleados->total(), 0, ',', '.') }} empleados</span>
            <span wire:loading.delay class="flex items-center gap-1.5"><span class="size-1.5 animate-pulse rounded-full bg-ipv-blue"></span> Buscando…</span>
        </div>

        <div class="sirex-glass-card overflow-hidden rounded-[14px]">
            {{-- Tabla en escritorio, tarjetas en celular --}}
            <table class="hidden w-full text-sm md:table">
                <thead class="bg-black/[0.02] text-left text-xs text-ipv-ink/55 dark:bg-white/[0.03] dark:text-ipv-ink-dark/55">
                    <tr>
                        <th class="px-4 py-2 font-medium">Legajo</th>
                        <th class="px-4 py-2 font-medium">Apellido y nombre</th>
                        <th class="px-4 py-2 font-medium">Oficina</th>
                        <th class="px-4 py-2 font-medium">Situación</th>
                        <th class="px-4 py-2 font-medium">Categoría</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($empleados as $e)
                        <tr wire:key="emp-{{ $e->id }}" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-2 text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $e->legajo }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('rrhh.ficha', $e->id) }}" wire:navigate.hover class="font-medium text-ipv-ink hover:text-ipv-blue hover:underline dark:text-ipv-ink-dark">{{ $e->apellido }}, {{ $e->nombre }}</a>
                                @if ($e->espasivo) <span class="ml-1 text-xs text-ipv-magenta">pasivo</span> @endif
                            </td>
                            <td class="px-4 py-2 text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $e->oficinaRrhh?->nombre }}</td>
                            <td class="px-4 py-2 text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $e->situacionLaboral?->nombre }}</td>
                            <td class="px-4 py-2 text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $e->categoriaTitular?->nombre }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <ul class="divide-y divide-ipv-blue/10 md:hidden dark:divide-white/10">
                @forelse ($empleados as $e)
                    <li wire:key="emp-m-{{ $e->id }}">
                        <a href="{{ route('rrhh.ficha', $e->id) }}" wire:navigate class="block px-4 py-3">
                            <p class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $e->apellido }}, {{ $e->nombre }}</p>
                            <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Legajo {{ $e->legajo }} · {{ $e->oficinaRrhh?->nombre }}</p>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados con esos filtros.</li>
                @endforelse
            </ul>
        </div>

        {{ $empleados->links() }}
    @endif
</div>
