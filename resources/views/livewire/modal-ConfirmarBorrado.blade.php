<flux:modal name="modal-ConfirmarBorrado" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Desea Borrar el Expediente?</flux:heading>
            <flux:text class="mt-2">
                <p>Estás a punto de eliminar este expediente.</p>
                <p>Esta acción no se puede revertir.</p>
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <x-button wire:click="eliminarExpediente">
                Eliminar
            </x-button>
        </div>
    </div>
</flux:modal>