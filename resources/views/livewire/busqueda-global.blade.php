<div class="relative mb-3.5"
    x-data="{
        abierto: false,
        activo: -1,
        items() { return [...$el.querySelectorAll('[data-resultado]')] },
        mover(delta) {
            const lista = this.items();
            if (!lista.length) return;
            this.activo = (this.activo + delta + lista.length) % lista.length;
            lista[this.activo].focus();
        },
    }"
    x-on:keydown.window.prevent.ctrl.k="$refs.input.focus(); abierto = true"
    x-on:keydown.window="if ($event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) { $event.preventDefault(); $refs.input.focus(); abierto = true }"
    x-on:click.outside="abierto = false"
    x-on:keydown.escape="abierto = false; $refs.input.blur()">
    <label for="busqueda-global" class="sr-only">Buscar expedientes y resoluciones</label>
    <svg class="pointer-events-none absolute left-[11px] top-[18px] size-3.5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3m2-5.2a7.2 7.2 0 1 1-14.4 0 7.2 7.2 0 0 1 14.4 0Z" />
    </svg>
    <input id="busqueda-global" x-ref="input" type="search" autocomplete="off"
        wire:model.live.debounce.350ms="q"
        x-on:focus="abierto = true" x-on:input="abierto = true; activo = -1"
        x-on:keydown.down.prevent="mover(1)"
        placeholder="Buscar…  (Ctrl+K)"
        class="w-full rounded-full border border-ipv-blue/15 bg-white/60 py-2 pl-[30px] pr-2.5 text-[12.5px] text-ipv-ink placeholder-ipv-ink/40 transition focus:border-ipv-blue/40 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ipv-blue/25 dark:border-white/15 dark:bg-white/[0.04] dark:text-ipv-ink-dark dark:placeholder-ipv-ink-dark/35 dark:focus:bg-white/[0.08]" />

    @if (mb_strlen(trim($q)) >= 3)
        <div x-show="abierto" x-cloak x-transition.opacity.duration.150ms
            x-on:keydown.down.prevent="mover(1)" x-on:keydown.up.prevent="mover(-1)"
            class="absolute left-0 right-0 top-full z-50 mt-1.5 max-h-[70vh] overflow-y-auto rounded-xl border border-ipv-blue/15 bg-white p-1.5 shadow-2xl dark:border-white/10 dark:bg-[#1a2332]">
            <div wire:loading.delay wire:target="q" class="space-y-1.5 p-1.5">
                <div class="sirex-skeleton h-9 rounded-lg"></div>
                <div class="sirex-skeleton h-9 rounded-lg"></div>
                <div class="sirex-skeleton h-9 rounded-lg"></div>
            </div>
            <div wire:loading.remove wire:target="q">
                @forelse ($grupos as $titulo => $items)
                    <p class="px-2 pb-1 pt-2 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/40 dark:text-ipv-ink-dark/40">{{ $titulo }}</p>
                    @foreach ($items as $item)
                        <a href="{{ $item['url'] }}" wire:navigate data-resultado wire:key="bg-{{ $loop->parent->index }}-{{ $loop->index }}"
                            x-on:click="abierto = false"
                            class="block rounded-lg px-2 py-1.5 outline-none transition-colors hover:bg-ipv-blue/[0.07] focus:bg-ipv-blue focus:text-white dark:hover:bg-white/[0.06]">
                            <span class="block truncate font-mono text-[12.5px] font-semibold">{{ $item['titulo'] }}</span>
                            @if ($item['detalle'])
                                <span class="block truncate text-[11px] opacity-70">{{ $item['detalle'] }}</span>
                            @endif
                        </a>
                    @endforeach
                @empty
                    <p class="px-2 py-3 text-center text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin resultados para “{{ $q }}”.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
