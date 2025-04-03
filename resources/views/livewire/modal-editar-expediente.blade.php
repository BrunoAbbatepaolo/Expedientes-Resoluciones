<x-dialog-modal wire:model="modalEdit">
    <x-slot:title>
        <div class="bg-sky-900 text-white text-center py-4 -mt-4 -mx-6 rounded-t-lg">
            <h2 class="text-lg font-bold">Editar Expediente</h2>
        </div>        
    </x-slot:title>

    <x-slot:content class="justify-center">
        <!-- Campos del formulario -->
        <div class="mb-4">
            <x-label value="Número de Expediente" />
            <x-input wire:model="expedienteForm.num_exp" placeholder="Ingrese número de expediente" class="w-full" />
            <x-input-error for="expedienteForm.num_exp" />
        </div>

        <div class="mb-4">
            <x-label value="Folio" />
            <x-input wire:model="expedienteForm.folio" placeholder="Ingrese el folio" class="w-full" />
            <x-input-error for="expedienteForm.folio" />
        </div>

        <div class="mb-4">
            <x-label value="Causante" />
            <x-input wire:model="expedienteForm.causante" placeholder="Ingrese el causante" class="w-full" />
            <x-input-error for="expedienteForm.causante" />
        </div>

        <div class="mb-4">
            <x-label value="Asunto" />
            <x-input wire:model="expedienteForm.asunto" placeholder="Ingrese el asunto" class="w-full" />
            <x-input-error for="expedienteForm.asunto" />
        </div>
        
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <x-label value="Fecha de Ingreso" />
                <x-input wire:model="expedienteForm.fecha_ingreso" type="date" class="w-full" />
                <x-input-error for="expedienteForm.fecha_ingreso" />
            </div>
        
            <div class="flex-1">
                <x-label value="Fecha de Salida" />
                <x-input wire:model="expedienteForm.fecha_salida" type="date" class="w-full" />
                @error('expedienteForm.fecha_salida')
                 <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
                <x-input-error for="expedienteForm.fecha_salida" />
            </div>
        </div>

        <div class="mb-4 relative">
            <x-label value="Oficina de Salida" />
            <input
                type="text"
                wire:model.live="query"
                class="w-full border-gray-300 rounded-md shadow-sm"
                placeholder="Ingrese oficina de salida"
                autocomplete="off"
            />
        
            @if(!empty($oficinas))
                <ul class="absolute bg-white border border-gray-300 rounded-md shadow-lg w-full z-10 max-h-40 overflow-y-auto mt-1">
                    @foreach($oficinas as $oficina)
                        <li
                            class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                            wire:click="selectOficina({{ $oficina->id }})"
                        >
                            {{ $oficina->nombre }} ({{ $oficina->codigo }})
                        </li>
                    @endforeach
                </ul>
            @endif
            
            <!-- Campo oculto para mantener el ID de la oficina -->
            <input type="hidden" wire:model="expedienteForm.ofi_salida">
            
            <x-input-error for="expedienteForm.ofi_salida" />
        </div>
    </x-slot:content>

    <x-slot:footer>
        <div class="flex justify-end space-x-4">
            <x-button-blue wire:click="actualizar">Guardar Cambios</x-button-blue>
            <x-button wire:click="$set('modalEdit', false)">Cerrar</x-button>
        </div>
    </x-slot:footer>
</x-dialog-modal>
