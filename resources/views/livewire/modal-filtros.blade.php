<div x-show="showFiltro" x-cloak x-on:keydown.escape.window="showFiltro = false"
    class="sirex-modal-fondo" x-on:click.self="showFiltro = false">
    <div class="sirex-modal sirex-modal-sm" role="dialog" aria-modal="true" aria-labelledby="titulo-filtro">
        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8.5V19l-4 2v-8.5L3 4z" />
                </svg>
            </div>
            <div>
                <h3 id="titulo-filtro" class="sirex-modal-titulo">Filtro avanzado</h3>
                <p class="sirex-modal-subtitulo">Elegí el rango de fechas.</p>
            </div>
            <button type="button" @click="showFiltro = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="sirex-modal-cuerpo">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="sirex-etiqueta">Desde</label>
                    <input type="date" wire:model="filtro.fechaDesde" class="sirex-campo">
                </div>
                <div>
                    <label class="sirex-etiqueta">Hasta</label>
                    <input type="date" wire:model="filtro.fechaHasta" class="sirex-campo">
                </div>
            </div>
        </div>

        <div class="sirex-modal-pie">
            <button type="button" wire:click="limpiarFiltros" class="sirex-boton sirex-boton-secundario">Limpiar filtro</button>
            <button type="button" wire:click="aplicarFiltros" class="sirex-boton sirex-boton-primario">Filtrar</button>
        </div>
    </div>
</div>
