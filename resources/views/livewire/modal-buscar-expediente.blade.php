<x-dialog-modal wire:model="modalExp">
    <x-slot:title>
        <div class="bg-sky-900 dark:bg-sky-950 text-white text-center py-4 -mt-4 -mx-6 rounded-t-lg">
            <h2 class="text-lg font-bold">Búsqueda de Expedientes</h2>
        </div>
    </x-slot:title>

    <x-slot:content>
        <!-- Campo de búsqueda -->
        <div class="flex justify-center gap-4 items-center my-4">
            <input type="text"
                class="w-full max-w-md rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 dark:focus:border-blue-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                wire:model="busquedaExp" placeholder="Buscar expediente" wire:keydown.enter="buscar" />
            <button wire:click="buscar"
                class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition ease-in-out">
                Buscar
            </button>
        </div>

        <!-- Mostrar el expediente encontrado si existe -->
        @if ($expedienteEncontrado)
        <div class="space-y-4 my-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Numº Expediente</label>
                <input type="text" value="{{ $expedienteEncontrado['numero'] }}"
                    class="w-full rounded border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100" readonly />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                <input type="text" value="{{ $asunto }}"
                    class="w-full rounded border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100" readonly />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Causante</label>
                <input type="text" value="{{ $causante }}"
                    class="w-full rounded border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100" readonly />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Creación</label>
                <input type="text" value="{{ $expedienteEncontrado['fecha'] }}"
                    class="w-full rounded border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100" readonly />
            </div>

            <!-- Campos adicionales -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-label value="Ofi-Salida" />
                    <x-input wire:model="expedienteForm.ofi_salida" class="dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                    <x-input-error for="expedienteForm.ofi_salida" />
                </div>
                <div>
                    <x-label value="Fecha-Salida" />
                    <x-input wire:model="expedienteForm.fecha_salida" class="dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                    <x-input-error for="expedienteForm.fecha_salida" />
                </div>
            </div>
        </div>
        @endif
    </x-slot:content>

    <x-slot:footer>
        <div class="flex gap-4 justify-end dark:bg-gray-400">
            @if ($expedienteEncontrado)
            <x-button-blue wire:click="guardar">Guardar</x-button-blue>
            @endif
            <x-button wire:click="cerrar" class="dark:bg-gray-600 dark:hover:bg-gray-700">Cerrar</x-button>
        </div>
    </x-slot:footer>
</x-dialog-modal>