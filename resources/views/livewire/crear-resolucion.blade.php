<div class="p-6 text-ipv-ink dark:text-ipv-ink-dark">
    <div class="max-w-7xl mx-auto">
        <h1 class="mb-6 text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark md:text-3xl">
            Crear resolución: <span class="text-ipv-blue dark:text-ipv-blue-light">{{ $this->displayName }}</span>
        </h1>

        @if ($modo === '')
        <div class="mb-4">
            <button type="button" onclick="window.location.href='/resoluciones/elegir'"
                class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </button>
        </div>
        <div class="sirex-glass-card mb-6 rounded-[14px] p-6">
            <h2 class="mb-4 text-xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Seleccione el modo de creación</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="button" wire:click="usarCompleto"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-ipv-blue px-4 py-3 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Usar modelo completo
                </button>
                <button type="button" wire:click="verPlantillaCompleta"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-ipv-blue/20 bg-white/70 px-4 py-3 font-medium text-ipv-ink/80 transition-colors hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Plantilla completa
                </button>
            </div>
        </div>

        @elseif ($modo === 'completo')
            <div class="mb-4">
                <button type="button" wire:click="$set('modo', '')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </button>
            </div>
            @switch($tipo)
                @case('Cancelaciones')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Barrio</label>
                                        <input type="text" wire:model.live="datos.nombre_barrio"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Cancelación</label>
                                            <input type="date" wire:model.live="datos.fecha_cancelacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de escritura</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Escritura</label>
                                                <input type="text" wire:model.live="datos.num_escritura"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Escritura</label>
                                                <input type="date" wire:model.live="datos.fecha_escritura"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre de Escribano</label>
                                                <input type="text" wire:model.live="datos.nombre_escribano"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('Resciciones')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Casa/Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Emprendimiento</label>
                                        <input type="text" wire:model.live="datos.nombre_emprendimiento"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Departamento</label>
                                        <input type="text" wire:model.live="datos.departamento"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Dictamen</label>
                                            <input type="date" wire:model.live="datos.fecha_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de deuda</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Monto de Deuda</label>
                                                <input type="number" wire:model.live="datos.monto_deuda" step="0.01"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Deuda</label>
                                                <input type="date" wire:model.live="datos.fecha_deuda"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Resolución de Adjudicación</h3>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución de Adjudicación</label>
                                            <input type="text" wire:model.live="datos.num_res_adjudicacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('Transferencias')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Casa/Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Plan</label>
                                        <input type="text" wire:model.live="datos.nombre_plan"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución de Adjudicación</label>
                                        <input type="text" wire:model.live="datos.num_res_adjudicacion"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Boleto</label>
                                            <input type="text" wire:model.live="datos.num_foja_boleto"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Plan</label>
                                            <input type="text" wire:model.live="datos.num_foja_plan"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Recursos</label>
                                            <input type="text" wire:model.live="datos.num_foja_recursos"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Promoción Social</label>
                                            <input type="text" wire:model.live="datos.num_foja_promocion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Ratificación</label>
                                            <input type="text" wire:model.live="datos.num_foja_ratificacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Regularización</label>
                                            <input type="text" wire:model.live="datos.num_foja_regularizacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Dictamen</label>
                                            <input type="date" wire:model.live="datos.fecha_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Titulares anteriores</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular Anterior</label>
                                                <input type="text" wire:model.live="datos.nombre_titular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular Anterior</label>
                                                <input type="text" wire:model.live="datos.dni_titular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular Anterior</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular Anterior</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Nuevos titulares</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Nuevo Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Nuevo Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Nacimiento</label>
                                                <input type="date" wire:model.live="datos.fecha_nacimiento_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Nuevo Cotitular</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Nuevo Cotitular</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Nacimiento</label>
                                                <input type="date" wire:model.live="datos.fecha_nacimiento_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del instrumento</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha del Instrumento</label>
                                                <input type="date" wire:model.live="datos.fecha_instrumento"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad de Cuotas</label>
                                                <input type="number" wire:model.live="datos.num_cuotas"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Monto de Cuota</label>
                                                <input type="number" wire:model.live="datos.monto_cuota" step="0.01"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Año de las Cuotas</label>
                                                <input type="number" wire:model.live="datos.año_cuotas" placeholder="Ej: 2026"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Primer Pago</label>
                                                <input type="date" wire:model.live="datos.fecha_primer_pago"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('Transferencia-Cancelacion')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Identificación de la unidad</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                                <input type="text" wire:model.live="datos.manzana"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Lote</label>
                                                <input type="text" wire:model.live="datos.lote"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Unidad</label>
                                                <input type="text" wire:model.live="datos.unidad"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Sector</label>
                                                <input type="text" wire:model.live="datos.sector"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="space-y-1.5 mt-4">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Plan</label>
                                            <input type="text" wire:model.live="datos.nombre_plan"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Instrumento Privado</label>
                                            <input type="text" wire:model.live="datos.num_foja_boleto"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Cancelación</label>
                                            <input type="date" wire:model.live="datos.fecha_cancelacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Promoción Social</label>
                                            <input type="text" wire:model.live="datos.num_foja_promocion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Resolución de Adjudicación</label>
                                        <input type="text" wire:model.live="datos.num_res_adjudicacion"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Res. Reglamentación Cancelación</label>
                                            <input type="text" wire:model.live="datos.num_res_reglamentacion" placeholder="185/2017"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Res. Modificatoria</label>
                                            <input type="text" wire:model.live="datos.num_res_modificatoria" placeholder="779/2017"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Titulares anteriores</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular Anterior</label>
                                                <input type="text" wire:model.live="datos.nombre_titular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular Anterior</label>
                                                <input type="text" wire:model.live="datos.dni_titular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular Anterior</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular Anterior</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular_anterior"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Nuevos titulares</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Nuevo Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Nuevo Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Estado Civil</label>
                                                <input type="text" wire:model.live="datos.estado_civil_titular_nuevo" placeholder="divorciado, soltero, etc."
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Nuevo Cotitular</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Nuevo Cotitular</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular_nuevo"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Estado Civil</label>
                                                <input type="text" wire:model.live="datos.estado_civil_cotitular_nuevo" placeholder="divorciada, soltera, etc."
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del instrumento</h3>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha del Instrumento Privado</label>
                                            <input type="date" wire:model.live="datos.fecha_instrumento"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('Rectificacion')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Casa/Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Plan</label>
                                        <input type="text" wire:model.live="datos.nombre_plan"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Res. Reglamentación</label>
                                            <input type="text" wire:model.live="datos.res_reglamentacion" placeholder="185/2017"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Res. Modificatoria</label>
                                            <input type="text" wire:model.live="datos.res_modificatoria" placeholder="779/2017"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Rectificación de Resolución</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">N° Resolución a Rectificar</label>
                                                <input type="text" wire:model.live="datos.num_res_adjudicacion"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Resolución</label>
                                                <input type="date" wire:model.live="datos.fecha_res_adjudicacion"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Orden</label>
                                                <input type="text" wire:model.live="datos.orden" placeholder="Ej: 8"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DNI</label>
                                            <input type="text" wire:model.live="datos.num_foja_dni"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Resolución Original</label>
                                            <input type="text" wire:model.live="datos.num_foja_resolucion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Informe Recursos</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Cancelación</label>
                                            <input type="date" wire:model.live="datos.fecha_cancelacion"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD Conf.</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd_conf"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del "DONDE DICE"</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad Viviendas</label>
                                                <input type="text" wire:model.live="datos.cantidad_viviendas" placeholder="Ej: 45"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad en Letras</label>
                                                <input type="text" wire:model.live="datos.cantidad_letras" placeholder="Ej: Cuarenta y Cinco"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="space-y-1.5 mt-2">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Texto DONDE DICE (opcional)</label>
                                            <input type="text" wire:model.live="datos.texto_donde_dice" placeholder="Texto alternativo completo"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento (2 páginas)
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('AplicarPagos')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Código de Pago</label>
                                            <input type="text" wire:model.live="datos.codigo_pago"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Barrio</label>
                                        <input type="text" wire:model.live="datos.nombre_barrio"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad de Cuotas</label>
                                                <input type="number" wire:model.live="datos.num_cuotas"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Monto de Cuota</label>
                                                <input type="number" wire:model.live="datos.monto_cuota" step="0.01"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Año de las Cuotas</label>
                                                <input type="number" wire:model.live="datos.año_cuotas" placeholder="Ej: 2026"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Primer Pago</label>
                                                <input type="date" wire:model.live="datos.fecha_primer_pago"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4 lg:col-span-8">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('ReconocimientoCuotaPagadaDosVeces')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Código de Pago</label>
                                            <input type="text" wire:model.live="datos.codigo_pago"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Barrio</label>
                                        <input type="text" wire:model.live="datos.nombre_barrio"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad de Cuotas</label>
                                                <input type="number" wire:model.live="datos.num_cuotas"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Monto de Cuota</label>
                                                <input type="number" wire:model.live="datos.monto_cuota" step="0.01"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Año de las Cuotas</label>
                                                <input type="number" wire:model.live="datos.año_cuotas" placeholder="Ej: 2026"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Primer Pago</label>
                                                <input type="date" wire:model.live="datos.fecha_primer_pago"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4 lg:col-span-8">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('ReconocimientoCuotaPagadaNoCargada')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 sirex-glass-card overflow-hidden rounded-[14px]">
                                <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                    <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                        <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Resolución</label>
                                            <input type="date" wire:model.live="datos.fecha_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Expediente</label>
                                            <input type="text" wire:model.live="datos.num_exp"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número de Resolución</label>
                                            <input type="text" wire:model.live="datos.num_res"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Manzana</label>
                                            <input type="text" wire:model.live="datos.manzana"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Lote</label>
                                            <input type="text" wire:model.live="datos.lote"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Código de Pago</label>
                                            <input type="text" wire:model.live="datos.codigo_pago"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre del Barrio</label>
                                        <input type="text" wire:model.live="datos.nombre_barrio"
                                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Solicitud</label>
                                            <input type="text" wire:model.live="datos.num_foja_solicitud"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja de Informe</label>
                                            <input type="text" wire:model.live="datos.num_foja_informe"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja DARRD</label>
                                            <input type="text" wire:model.live="datos.num_foja_darrd"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Foja Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_foja_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Número Dictamen</label>
                                            <input type="text" wire:model.live="datos.num_dictamen"
                                                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Titular</label>
                                                <input type="text" wire:model.live="datos.nombre_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Titular</label>
                                                <input type="text" wire:model.live="datos.dni_titular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Nombre Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.nombre_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">DNI Cotitular (opcional)</label>
                                                <input type="text" wire:model.live="datos.dni_cotitular"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10">
                                        <h3 class="mb-3 font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Cantidad de Cuotas</label>
                                                <input type="number" wire:model.live="datos.num_cuotas"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Monto de Cuota</label>
                                                <input type="number" wire:model.live="datos.monto_cuota" step="0.01"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Año de las Cuotas</label>
                                                <input type="number" wire:model.live="datos.año_cuotas" placeholder="Ej: 2026"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha Primer Pago</label>
                                                <input type="date" wire:model.live="datos.fecha_primer_pago"
                                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="submit"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-ipv-blue px-5 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar resolución
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4 lg:col-span-8">
                                <div class="sirex-glass-card h-full overflow-hidden rounded-[14px]">
                                    <div class="border-b border-ipv-blue/10 px-6 py-4 dark:border-white/10">
                                        <h2 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                            <svg class="mr-2 inline size-4 text-ipv-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                    <div class="rounded-lg bg-ipv-magenta/10 p-6 text-ipv-magenta dark:bg-ipv-magenta/20 dark:text-ipv-magenta-light">
                        El tipo <strong>{{ $tipo }}</strong> no tiene plantilla de personalización disponible.
                        <button type="button" wire:click="$set('modo', '')"
                        class="mt-4 rounded-lg border border-ipv-blue/20 bg-white/70 px-4 py-2 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                        Volver
                    </button>
                    </div>
                @endswitch

        @elseif ($modo === 'personalizado')
        <div class="min-h-screen bg-black/[0.02] dark:bg-white/[0.02] -mx-6 -my-6 p-6">
            <div class="sirex-glass-card mb-6 -mx-6 flex items-center justify-between rounded-lg px-6 py-3">
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="usarCompleto"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver al formulario
                    </button>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        Editá el documento como si fuera un Word
                    </span>
                </div>
                <button type="button" wire:click="guardarPersonalizado"
                    class="rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    Guardar resolución
                </button>
            </div>

            <div class="sirex-glass-card mx-auto max-w-3xl overflow-hidden rounded-[14px]">
                <div wire:ignore
                     x-data="quillInit()"
                     x-init="init()"
                     x-ref="quill-1"
                     class="[&_.ql-toolbar.ql-snow]:!border-zinc-200 dark:[&_.ql-toolbar.ql-snow]:!border-zinc-700 dark:[&_.ql-snow_.ql-toolbar]:!bg-zinc-800 dark:[&_.ql-snow_.ql-stroke]:!stroke-zinc-400 dark:[&_.ql-snow_.ql-fill]:!fill-zinc-400 dark:[&_.ql-snow_.ql-picker]:!text-zinc-400 dark:[&_.ql-snow_.ql-picker-options]:!bg-zinc-700 [&_.ql-editor]:!font-serif [&_.ql-editor]:!text-base [&_.ql-editor]:!leading-relaxed [&_.ql-editor]:!p-12 [&_.ql-editor]:!text-justify [&_.ql-container]:!border-none">
                    <div data-quill-editor></div>
                </div>
            </div>

            <input type="hidden" name="plantilla" id="plantilla-input" wire:model.live="plantilla" />
        </div>

        @elseif ($modo === 'plantilla')
        <div class="min-h-screen bg-black/[0.02] dark:bg-white/[0.02] -mx-6 -my-6 p-6" x-data="{ mostrarModalPdf: false, pdfIndex: null }">
            <div class="sirex-glass-card mb-6 -mx-6 flex items-center justify-between rounded-lg px-6 py-3">
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="$set('modo', '')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </button>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        Editá el documento haciendo clic en el texto
                    </span>
                </div>
                <button type="button" onclick="@this.guardarPlantilla()"
                    class="rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    Guardar resolución
                </button>
            </div>

@if($tipo === 'AplicarPagos' || $tipo === 'ReconocimientoCuotaPagadaDosVeces' || $tipo === 'ReconocimientoCuotaPagadaNoCargada')
            <div class="sirex-glass-card mx-auto min-w-[1200px] overflow-auto rounded-[14px]">
                <div class="p-2">
                    {!! $this->getVistaPrevia() !!}
                </div>
            </div>

            <!-- Sección de PDFs -->
            <div class="mt-6 px-4">
                <div class="flex items-center gap-4 mb-4">
                    <label class="flex items-center gap-2 rounded-lg bg-ipv-blue px-4 py-2 text-white transition-colors hover:bg-ipv-blue-dark cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar PDF
                        <input type="file" wire:model="tempArchivos" multiple accept=".pdf" class="hidden" />
                    </label>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        {{ count($archivosPDF) }} archivo(s) cargado(s)
                    </span>
                </div>

                @if(count($archivosPDF) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($archivosPDF as $index => $archivo)
                            <div @click="mostrarModalPdf = true; pdfIndex = {{ $index }}" class="sirex-glass-card group flex cursor-pointer items-center justify-between rounded-[14px] p-4 transition-all hover:border-ipv-blue dark:hover:border-ipv-blue-light">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="bg-red-100 dark:bg-red-900 rounded p-2 flex-shrink-0 group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="truncate text-sm text-ipv-ink/80 dark:text-ipv-ink-dark/85" title="{{ $archivo->getClientOriginalName() }}">
                                        {{ $archivo->getClientOriginalName() }}
                                    </span>
                                </div>
                                <button type="button" wire:click.stop="removeArchivo({{ $index }})" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Modal para ver PDF -->
            <div x-show="mostrarModalPdf" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                 style="display: none;">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black/50" @click="mostrarModalPdf = false"></div>
                
                <!-- Modal content -->
                <div class="relative flex h-[80vh] w-full max-w-4xl flex-col rounded-[14px] bg-white shadow-xl dark:bg-[#141c26]">
                    <div class="flex items-center justify-between border-b border-ipv-blue/10 p-4 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                            Documento PDF
                        </h3>
                        <button @click="mostrarModalPdf = false" class="text-ipv-ink/60 hover:text-ipv-ink dark:text-ipv-ink-dark/60 dark:hover:text-ipv-ink-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden rounded-b-lg bg-black/[0.03] dark:bg-white/[0.03]">
                        <iframe 
                            :src="'/resoluciones/descargar-pdf/' + pdfIndex" 
                            class="w-full h-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
            </div>
        @else
            <div class="sirex-glass-card mx-auto max-w-4xl overflow-hidden rounded-[14px]">
                <div class="p-6">
                    {!! $this->plantilla !!}
                </div>
            </div>
        @endif
        </div>
    @endif
    </div>
</div>