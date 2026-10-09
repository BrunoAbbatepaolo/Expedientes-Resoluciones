<div x-show="showBorrar" x-cloak x-on:keydown.escape.window="showBorrar = false"
    class="sirex-modal-fondo" x-on:click.self="showBorrar = false">
    <div class="sirex-modal sirex-modal-sm sirex-modal-peligro" role="alertdialog" aria-modal="true" aria-labelledby="titulo-borrar-expediente">
        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono sirex-modal-icono-peligro">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div>
                <h3 id="titulo-borrar-expediente" class="sirex-modal-titulo">¿Desea borrar el expediente?</h3>
                <p class="sirex-modal-subtitulo">Estás a punto de eliminarlo. Esta acción no se puede revertir.</p>
            </div>
        </div>
        <div class="sirex-modal-pie">
            <button type="button" @click="showBorrar = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>
            <x-boton-animado accion="$wire.eliminarExpediente()" />
        </div>
    </div>
</div>
