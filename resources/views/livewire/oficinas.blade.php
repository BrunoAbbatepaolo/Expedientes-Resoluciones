<div class="space-y-4">
    <div>
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Directorio de oficinas</h1>
        <p class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">Personal activo de cada oficina y su vínculo con el sistema de expedientes del IPV.</p>
    </div>

    <label class="relative block w-full sm:w-1/3">
        <span class="sr-only">Buscar oficina</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="search" wire:model.live.debounce.300ms="inputBusqueda" placeholder="Buscar por nombre..."
            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2 pl-10 pr-4 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
    </label>

    <div class="space-y-2">
        @forelse ($oficinas as $oficina)
            <article wire:key="ofi-{{ $oficina->id }}"
                class="sirex-glass-card overflow-hidden rounded-[14px] transition-shadow duration-200 hover:shadow-md">
                <div class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="truncate font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $oficina->nombre }}</h2>
                        <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                            @if ($oficina->mitiv_oficina_id)
                                En el IPV: <span class="font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/75">{{ $nombresIpv[$oficina->mitiv_oficina_id] ?? 'oficina #'.$oficina->mitiv_oficina_id }}</span>
                            @else
                                Sin vincular con el sistema del IPV
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        @if ($oficina->mitiv_oficina_id)
                            <a href="{{ route('expedientes.ipv', ['oficina' => $oficina->mitiv_oficina_id]) }}" wire:navigate.hover
                                class="sirex-btn rounded-lg border border-ipv-blue/20 px-3 py-1.5 text-xs font-medium text-ipv-ink/75 transition hover:bg-white dark:border-white/15 dark:text-ipv-ink-dark/80 dark:hover:bg-white/[0.08]">
                                Expedientes ahí
                            </a>
                            <button type="button" wire:click="alternarPersonal({{ $oficina->id }})" aria-expanded="{{ $expandida === $oficina->id ? 'true' : 'false' }}"
                                class="sirex-btn rounded-lg border border-ipv-blue/20 px-3 py-1.5 text-xs font-medium text-ipv-ink/75 transition hover:bg-white dark:border-white/15 dark:text-ipv-ink-dark/80 dark:hover:bg-white/[0.08]">
                                {{ $expandida === $oficina->id ? 'Ocultar personal' : 'Ver personal' }}
                            </button>
                        @endif
                        @if ($puedeEditar)
                            <button type="button" wire:click="vincular({{ $oficina->id }})"
                                class="sirex-btn rounded-lg bg-ipv-blue px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-ipv-blue-dark">
                                {{ $oficina->mitiv_oficina_id ? 'Cambiar vínculo' : 'Vincular con IPV' }}
                            </button>
                        @endif
                    </div>
                </div>

                @if ($vinculando === $oficina->id)
                    <div class="border-t border-ipv-blue/10 bg-ipv-blue/[0.03] p-4 dark:border-white/10 dark:bg-white/[0.02]">
                        <label class="mb-1 block text-xs font-medium text-ipv-ink/70 dark:text-ipv-ink-dark/70" for="buscarIpv-{{ $oficina->id }}">Buscá la oficina en el sistema del IPV</label>
                        <input id="buscarIpv-{{ $oficina->id }}" type="search" wire:model.live.debounce.250ms="buscarIpv" autofocus
                            class="w-full rounded-lg border border-ipv-blue/20 bg-white px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($sugerencias as $s)
                                <button type="button" wire:click="guardarVinculo({{ $s->id }})" wire:key="sug-{{ $s->id }}"
                                    class="rounded-full border border-ipv-blue/25 px-3 py-1 text-xs font-medium text-ipv-blue transition hover:-translate-y-px hover:bg-ipv-blue hover:text-white dark:text-ipv-blue-light">
                                    {{ $s->nombre }}
                                </button>
                            @empty
                                <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin coincidencias. Probá con otra palabra (mínimo 3 letras).</p>
                            @endforelse
                        </div>
                        <div class="mt-3 flex gap-3 text-xs">
                            <button type="button" wire:click="cancelarVinculo" class="text-ipv-ink/60 hover:underline dark:text-ipv-ink-dark/60">Cancelar</button>
                            @if ($oficina->mitiv_oficina_id)
                                <button type="button" wire:click="guardarVinculo(null)" class="text-ipv-magenta hover:underline">Quitar vínculo</button>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($expandida === $oficina->id)
                    <div class="border-t border-ipv-blue/10 p-4 dark:border-white/10">
                        <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @forelse ($personal as $emp)
                                <li wire:key="per-{{ $emp->id }}" class="flex items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-black/[0.03] dark:hover:bg-white/[0.04]">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-ipv-gold/25 text-xs font-bold text-ipv-gold-ink dark:text-ipv-gold-light">
                                        {{ mb_substr($emp->nombre, 0, 1) }}{{ mb_substr($emp->apellido, 0, 1) }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $emp->nombre_completo }}</span>
                                        @if ($emp->email)
                                            <a href="mailto:{{ $emp->email }}" class="block truncate text-xs text-ipv-blue hover:underline dark:text-ipv-blue-light">{{ $emp->email }}</a>
                                        @endif
                                    </span>
                                </li>
                            @empty
                                <li class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay personal activo cargado para esta oficina en el IPV.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </article>
        @empty
            <p class="sirex-glass-card rounded-[14px] px-6 py-8 text-center text-sm text-ipv-ink/50 dark:text-ipv-ink-dark/50">No se encontraron oficinas.</p>
        @endforelse
    </div>

    <div>{{ $oficinas->links() }}</div>
</div>
