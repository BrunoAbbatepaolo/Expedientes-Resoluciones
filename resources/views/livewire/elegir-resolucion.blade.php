<div class="sirex-elegir mx-auto max-w-5xl p-4 sm:p-6">
    <a wire:navigate href="{{ route('resoluciones') }}"
        class="mb-4 inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 transition hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Volver
    </a>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ipv-ink sm:text-3xl dark:text-ipv-ink-dark">¿Qué resolución querés hacer?</h1>
            <p class="mt-1 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">Tocá una opción y completás los datos en el mismo documento.</p>
        </div>
        <div class="relative w-full sm:w-72">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" /></svg>
            <input type="search" wire:model.live.debounce.200ms="busqueda" placeholder="Buscar: cancelación, pago, oficio…" aria-label="Buscar tipo de resolución"
                class="w-full rounded-xl border border-ipv-blue/20 bg-white/80 py-2.5 pl-9 pr-3 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </div>
    </div>

    @php($n = 0)
    @forelse ($this->gruposFiltrados as $grupo)
        <section class="mb-7" wire:key="grupo-{{ $grupo['titulo'] }}">
            <h2 class="mb-2.5 text-xs font-semibold uppercase tracking-wider text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $grupo['titulo'] }}</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($grupo['tipos'] as $tipo)
                    <a wire:key="tipo-{{ $tipo['nombre'] }}" wire:navigate href="{{ route('resoluciones.crear', ['tipo' => $tipo['nombre']]) }}"
                        class="sirex-elegir-card sirex-glass-card group flex items-start gap-3 rounded-2xl p-4" data-acento="{{ $tipo['acento'] }}" style="--i: {{ $n++ }}">
                        <span class="sirex-elegir-icono grid size-11 shrink-0 place-items-center rounded-xl">
                            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $tipo['icono'] }}" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[0.95rem] font-semibold leading-snug text-ipv-ink dark:text-ipv-ink-dark">{{ $tipo['display'] }}</span>
                            @if ($tipo['ayuda'])
                                <span class="mt-0.5 block text-[0.8rem] leading-snug text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $tipo['ayuda'] }}</span>
                            @endif
                            @unless ($tipo['plantillaDisponible'])
                                <span class="mt-1.5 inline-block rounded-full bg-ipv-gold/20 px-2 py-0.5 text-[0.7rem] font-medium text-[#7a5200] dark:text-ipv-gold-light">Solo texto libre</span>
                            @endunless
                        </span>
                        <svg class="sirex-elegir-flecha mt-1 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
                    </a>
                @endforeach
            </div>
        </section>
    @empty
        <div class="sirex-glass-card rounded-2xl p-10 text-center">
            <svg class="mx-auto size-10 text-ipv-ink/30 dark:text-ipv-ink-dark/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" /></svg>
            <p class="mt-2 text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">No encontramos «{{ $busqueda }}»</p>
            <button type="button" wire:click="$set('busqueda', '')" class="mt-1 text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">Ver todas</button>
        </div>
    @endforelse

    {{-- Para una resolución que no es de ningún tipo: el documento en blanco, todo editable. --}}
    @if ($busqueda === '' || str_contains('otros en blanco texto libre personalizado', mb_strtolower(trim($busqueda))))
        <a wire:navigate href="{{ route('resoluciones.crear', ['tipo' => 'Otros']) }}"
            class="sirex-elegir-card group flex items-center gap-3 rounded-2xl border-2 border-dashed border-ipv-blue/25 p-4 hover:border-ipv-blue/50 dark:border-white/20" data-acento="gris" style="--i: {{ $n }}">
            <span class="sirex-elegir-icono grid size-11 shrink-0 place-items-center rounded-xl">
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-[0.95rem] font-semibold text-ipv-ink dark:text-ipv-ink-dark">Otra resolución</span>
                <span class="block text-[0.8rem] text-ipv-ink/60 dark:text-ipv-ink-dark/60">No es ninguna de las anteriores: escribila con texto libre.</span>
            </span>
            <svg class="sirex-elegir-flecha size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
        </a>
    @endif
</div>
