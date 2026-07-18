<div x-show="showBorrar" x-cloak x-on:keydown.escape.window="showBorrar = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
    x-on:click.self="showBorrar = false">
    <div class="w-full min-w-[22rem] max-w-sm rounded-2xl border border-ipv-blue/20 bg-white/95 p-5 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">¿Desea Borrar el Expediente?</h3>
                <p class="mt-2 text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                    Estás a punto de eliminar este expediente.<br>
                    Esta acción no se puede revertir.
                </p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showBorrar = false"
                    class="rounded-lg border border-ipv-blue/20 px-4 py-2 text-sm font-medium text-ipv-ink/80 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                    Cancelar
                </button>
                <button type="button" wire:click="eliminarExpediente"
                    class="rounded-lg bg-ipv-magenta px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
