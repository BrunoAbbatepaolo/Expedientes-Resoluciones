<div x-show="showEditar" x-cloak x-on:keydown.escape.window="showEditar = false"
    class="sirex-modal-fondo" x-on:click.self="showEditar = false">
    <div class="sirex-modal sirex-modal-md" role="dialog" aria-modal="true" aria-labelledby="titulo-editar-expediente">
        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono sirex-modal-icono-oro">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h3 id="titulo-editar-expediente" class="sirex-modal-titulo">Editar expediente</h3>
                <p class="sirex-modal-subtitulo">Modificá los datos del expediente.</p>
            </div>
            <button type="button" wire:click="cancelarModal" @click="showEditar = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="sirex-modal-cuerpo">
            <div>
                <label class="sirex-etiqueta">Nº expediente</label>
                <input wire:model="expedienteForm.num_exp" placeholder="Ingrese número de expediente" class="sirex-campo" />
                <x-input-error for="expedienteForm.num_exp" />
            </div>

            <div>
                <label class="sirex-etiqueta">Folio</label>
                <input wire:model="expedienteForm.folio" placeholder="Ingrese el folio" class="sirex-campo" />
                <x-input-error for="expedienteForm.folio" />
            </div>

            <div>
                <label class="sirex-etiqueta">Causante</label>
                <input wire:model="expedienteForm.causante" placeholder="Ingrese el causante" class="sirex-campo" />
                <x-input-error for="expedienteForm.causante" />
            </div>

            <div>
                <label class="sirex-etiqueta">Asunto</label>
                <input wire:model="expedienteForm.asunto" placeholder="Ingrese el asunto" class="sirex-campo" />
                <x-input-error for="expedienteForm.asunto" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="sirex-etiqueta">Fecha de ingreso</label>
                    <input wire:model="expedienteForm.fecha_ingreso" type="date" class="sirex-campo" />
                    <x-input-error for="expedienteForm.fecha_ingreso" />
                </div>

                <div>
                    <label class="sirex-etiqueta">Fecha de salida</label>
                    <input wire:model="expedienteForm.fecha_salida" type="date" class="sirex-campo" />
                    <x-input-error for="expedienteForm.fecha_salida" />
                </div>
            </div>
        </div>

        <div class="sirex-modal-pie">
            <button type="button" wire:click="cancelarModal" @click="showEditar = false" class="sirex-boton sirex-boton-secundario">Cerrar</button>
            <button type="button" wire:click="actualizar" class="sirex-boton sirex-boton-primario">Guardar</button>
        </div>
    </div>
</div>
