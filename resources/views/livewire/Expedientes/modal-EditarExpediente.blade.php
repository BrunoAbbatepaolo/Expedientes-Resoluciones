<flux:modal name="modal-editarExpediente" class="md:w-[30rem]">
    <div class="space-y-6 p-5 rounded-lg">
        <div>
            <flux:heading size="lg" class="text-2xl text-center font-bold text-gray-800 dark:text-white">Carga de Nuevo Expediente</flux:heading>
            <flux:text class="mt-2 text-gray-600 dark:text-gray-300">Ingrese el numero del Expediente.</flux:text>
        </div>

        <div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Numº Expediente</label>
                <x-input wire:model="expedienteForm.num_exp" placeholder="Ingrese número de expediente" class="w-full" />
                <x-input-error for="expedienteForm.num_exp" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio</label>
                <x-input wire:model="expedienteForm.folio" placeholder="Ingrese el folio" class="w-full" />
                <x-input-error for="expedienteForm.folio" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Causante</label>
                <x-input wire:model="expedienteForm.causante" placeholder="Ingrese el causante" class="w-full" />
                <x-input-error for="expedienteForm.causante" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                <x-input wire:model="expedienteForm.asunto" placeholder="Ingrese el asunto" class="w-full" />
                <x-input-error for="expedienteForm.asunto" />
            </div>

            <div class="flex gap-4 mb-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Ingreso</label>
                    <x-input wire:model="expedienteForm.fecha_ingreso" type="date" class="w-full" />
                    <x-input-error for="expedienteForm.fecha_ingreso" />
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Salida</label>
                    <x-input wire:model="expedienteForm.fecha_salida" type="date" class="w-full" />
                    @error('expedienteForm.fecha_salida')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    <x-input-error for="expedienteForm.fecha_salida" />
                </div>
            </div>
        </div>


        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button
                wire:click="actualizar"
                class="bg-sky-600 hover:bg-sky-700 text-white font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition-all duration-200 dark:bg-sky-500 dark:hover:bg-sky-600 transform hover:scale-105">
                Guardar
            </x-button>
            <x-button
                wire:click="cancelarModal"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transform hover:scale-105">
                Cerrar
            </x-button>
        </div>
    </div>
</flux:modal>