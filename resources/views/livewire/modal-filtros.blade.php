<div x-show="showFiltro" x-cloak x-on:keydown.escape.window="showFiltro = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
    x-on:click.self="showFiltro = false">
    <div class="w-full max-w-sm rounded-2xl border border-ipv-blue/20 bg-white/95 p-5 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
        <div class="space-y-6">
            <div>
                <h3 class="text-center text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Filtro Avanzado</h3>
                <p class="mt-2 text-center text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">Seleccione las Fechas Desde y Hasta.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Fecha Desde</label>
                <input type="date" wire:model="filtro.fechaDesde"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Fecha Hasta</label>
                <input type="date" wire:model="filtro.fechaHasta"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="aplicarFiltros"
                    class="rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-md hover:bg-ipv-blue-dark">
                    Filtrar
                </button>
                <button type="button" wire:click="limpiarFiltros"
                    class="rounded-lg border border-ipv-blue/20 px-4 py-2 text-sm font-medium text-ipv-ink/80 shadow-sm hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                    Limpiar Filtro
                </button>
            </div>
        </div>
    </div>
</div>
