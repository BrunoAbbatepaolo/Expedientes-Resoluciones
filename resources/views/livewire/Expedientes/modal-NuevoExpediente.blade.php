<div x-show="showNuevo" x-cloak x-on:keydown.escape.window="showNuevo = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
    x-on:click.self="showNuevo = false">
    <div class="w-full max-w-[35rem] rounded-2xl border border-ipv-blue/20 bg-white/95 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
    <div class="space-y-6 p-2">

        <div class="flex flex-col items-center justify-center space-y-3 text-center">
            <div class="rounded-full bg-ipv-blue/10 p-3 text-ipv-blue dark:bg-ipv-blue-light/15 dark:text-ipv-blue-light">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark">
                    Carga de Nuevo Expediente
                </h3>
                <p class="mt-1 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                    Ingrese el número del expediente para continuar
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-ipv-ink/40 dark:text-ipv-ink-dark/45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model="busquedaExp" wire:keydown.enter="buscar"
                    placeholder="Buscar expediente..."
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2.5 pl-10 pr-4 text-sm text-ipv-ink shadow-sm transition-colors placeholder-ipv-ink/40 focus:border-ipv-blue focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark dark:placeholder-ipv-ink-dark/40" />
            </div>

            <button type="button" wire:click="buscar"
                class="flex cursor-pointer items-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark disabled:cursor-not-allowed disabled:opacity-50">
                <span>Buscar</span>
            </button>
        </div>

        @if ($expedienteEncontrado)
            <div class="rounded-xl border border-ipv-blue/15 p-4 dark:border-white/10">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                            Nº Expediente
                        </label>
                        <input type="text" value="{{ $expedienteEncontrado['numero'] }}" readonly disabled
                            class="w-full rounded-lg border border-ipv-blue/10 bg-black/[0.03] px-3 py-2 font-mono font-semibold text-ipv-ink dark:border-white/5 dark:bg-white/5 dark:text-ipv-ink-dark" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                            Nº Fojas
                        </label>
                        <input type="text" value="{{ $expedienteEncontrado['folio'] }}" readonly disabled
                            class="w-full rounded-lg border border-ipv-blue/10 bg-black/[0.03] px-3 py-2 text-ipv-ink dark:border-white/5 dark:bg-white/5 dark:text-ipv-ink-dark" />
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                            Asunto
                        </label>
                        <input type="text" value="{{ $asunto }}" readonly disabled
                            class="w-full rounded-lg border border-ipv-blue/10 bg-black/[0.03] px-3 py-2 text-ipv-ink dark:border-white/5 dark:bg-white/5 dark:text-ipv-ink-dark" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                            Causante
                        </label>
                        <input type="text" value="{{ $causante }}" readonly disabled
                            class="w-full rounded-lg border border-ipv-blue/10 bg-black/[0.03] px-3 py-2 text-ipv-ink dark:border-white/5 dark:bg-white/5 dark:text-ipv-ink-dark" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                            Fecha de Creación
                        </label>
                        <input type="text" value="{{ $expedienteEncontrado['fecha'] }}" readonly disabled
                            class="w-full rounded-lg border border-ipv-blue/10 bg-black/[0.03] px-3 py-2 text-ipv-ink dark:border-white/5 dark:bg-white/5 dark:text-ipv-ink-dark" />
                    </div>

                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3 border-t border-ipv-blue/10 pt-4 dark:border-white/10">
            <button type="button" wire:click="cerrar" @click="showNuevo = false"
                class="cursor-pointer rounded-lg border border-ipv-blue/20 bg-white/70 px-4 py-2 font-medium text-ipv-ink/80 transition-colors hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                Cerrar
            </button>

            @if ($expedienteEncontrado)
                <button type="button" wire:click="guardar"
                    class="cursor-pointer rounded-lg bg-ipv-blue px-5 py-2 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    Guardar
                </button>
            @endif
        </div>

    </div>
    </div>
</div>
