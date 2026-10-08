<div x-show="showNuevo" x-cloak x-on:keydown.escape.window="showNuevo = false"
    class="sirex-modal-fondo" x-on:click.self="showNuevo = false">
    <div class="sirex-modal sirex-modal-md" role="dialog" aria-modal="true" aria-labelledby="titulo-nuevo-expediente">
        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h3 id="titulo-nuevo-expediente" class="sirex-modal-titulo">Carga de nuevo expediente</h3>
                <p class="sirex-modal-subtitulo">Ingresá el número del expediente para continuar.</p>
            </div>
            <button type="button" wire:click="cerrar" @click="showNuevo = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="sirex-modal-cuerpo">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-ipv-ink/40 dark:text-ipv-ink-dark/45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" wire:model="busquedaExp" wire:keydown.enter="buscar" placeholder="Buscar expediente..."
                        class="sirex-campo pl-9" />
                </div>
                <button type="button" wire:click="buscar" class="sirex-boton sirex-boton-primario">Buscar</button>
            </div>

            @if ($expedienteEncontrado)
                <div class="rounded-xl border border-ipv-blue/15 bg-ipv-blue/[0.03] p-4 dark:border-white/10 dark:bg-white/[0.03]">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="sirex-etiqueta">Nº expediente</label>
                            <input type="text" value="{{ $expedienteEncontrado['numero'] }}" readonly disabled class="sirex-campo font-mono font-semibold" />
                        </div>
                        <div>
                            <label class="sirex-etiqueta">Nº fojas</label>
                            <input type="text" value="{{ $expedienteEncontrado['folio'] }}" readonly disabled class="sirex-campo" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="sirex-etiqueta">Asunto</label>
                            <input type="text" value="{{ $asunto }}" readonly disabled class="sirex-campo" />
                        </div>
                        <div>
                            <label class="sirex-etiqueta">Causante</label>
                            <input type="text" value="{{ $causante }}" readonly disabled class="sirex-campo" />
                        </div>
                        <div>
                            <label class="sirex-etiqueta">Fecha de creación</label>
                            <input type="text" value="{{ $expedienteEncontrado['fecha'] }}" readonly disabled class="sirex-campo" />
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="sirex-modal-pie">
            <button type="button" wire:click="cerrar" @click="showNuevo = false" class="sirex-boton sirex-boton-secundario">Cerrar</button>
            @if ($expedienteEncontrado)
                <button type="button" wire:click="guardar" class="sirex-boton sirex-boton-primario">Guardar</button>
            @endif
        </div>
    </div>
</div>
