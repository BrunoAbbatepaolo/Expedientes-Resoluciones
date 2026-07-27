<div class="mx-auto max-w-6xl p-6">
    <div class="mb-4">
        <button type="button" onclick="window.location.href='/resoluciones'"
            class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </button>
    </div>
    <div class="mb-8">
        <h1 class="mb-2 text-3xl font-bold text-ipv-ink dark:text-ipv-ink-dark">Crear nueva resolución</h1>
        <p class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">Selecciona el tipo de resolución que deseas crear</p>
    </div>

    <div class="mb-6">
        <input
            type="text"
            wire:model.live="busqueda"
            placeholder="Buscar tipo de resolución..."
            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-4 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($this->tiposFiltrados as $tipo)
            <div class="sirex-glass-card group cursor-pointer overflow-hidden rounded-[14px] transition-all duration-200 hover:shadow-xl"
                 wire:click="seleccionar('{{ $tipo['nombre'] }}')">

                <!-- Vista previa del documento -->
                <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-ipv-blue/5 dark:bg-ipv-blue-light/[0.06]">
                    <!-- Simulación de documento -->
                    <div class="flex h-32 w-24 flex-col rounded-sm bg-white shadow-lg dark:bg-slate-200">
                        <div class="h-4 rounded-t-sm bg-ipv-blue"></div>
                        <div class="flex-1 space-y-1 p-2">
                            <div class="h-1 w-full rounded bg-gray-300"></div>
                            <div class="h-1 w-3/4 rounded bg-gray-300"></div>
                            <div class="h-1 w-1/2 rounded bg-gray-300"></div>
                            <div class="h-1 w-5/6 rounded bg-gray-300"></div>
                            <div class="h-1 w-2/3 rounded bg-gray-300"></div>
                            <div class="h-1 w-4/5 rounded bg-gray-300"></div>
                        </div>
                    </div>

                    <!-- Overlay hover -->
                    <div class="absolute inset-0 bg-ipv-blue opacity-0 transition-opacity duration-200 group-hover:opacity-10"></div>
                </div>

                <!-- Información del tipo -->
                <div class="p-4">
                    <h3 class="text-lg font-bold leading-tight text-ipv-ink dark:text-ipv-ink-dark">
                        {{ $tipo['display'] ?? $tipo['nombre'] }}
                    </h3>
                    @unless ($tipo['plantillaDisponible'])
                        <span class="mt-1 inline-block rounded bg-ipv-gold/20 px-2 py-0.5 text-xs font-medium text-[#7a5200] dark:text-ipv-gold-light">
                            Sin plantilla completa · usá "Personalizado"
                        </span>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</div>