<flux:modal name="modal-exp" class="md:w-[30rem]">
    <div class="space-y-6 p-5 rounded-lg">
        <div>
            <flux:heading size="lg" class="text-2xl text-center font-bold text-gray-800 dark:text-white">Carga de Nuevo Expediente</flux:heading>
        </div>

        <!-- Buscador de expediente -->
        <div class="flex justify-center gap-2 items-center my-4">
            <div class="relative">
                <x-input
                    type="text"
                    wire:model="busquedaExp"
                    wire:keydown.enter="buscar"
                    placeholder="Buscar expediente..."
                    class="w-full pl-10 pr-4" />
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 size-5 text-gray-500"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <button wire:click="buscar"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-all duration-200 dark:bg-blue-500 dark:hover:bg-blue-600">
                Buscar
            </button>
        </div>


        <!-- Mostrar el expediente encontrado si existe -->
        @if ($expedienteEncontrado)
        <div class="space-y-4 my-4 p-4 rounded-lg shadow-inner">
            <div class="grid grid-cols-1 gap-3">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Numº Expediente</label>
                    <x-input type="text" value="{{ $expedienteEncontrado['numero'] }}"
                        class="w-full" readonly />
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                    <x-input type="text" value="{{ $asunto }}"
                        class="w-full" readonly />
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Causante</label>
                    <x-input type="text" value="{{ $causante }}"
                        class="w-full" readonly />
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Creación</label>
                    <x-input type="text" value="{{ $expedienteEncontrado['fecha'] }}"
                        class="w-full" readonly />
                </div>
            </div>

            <!-- Campos adicionales -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                <div class="space-y-1">
                    <label value="Ofi-Salida" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ofi de Salida</label>
                    <x-input wire:model="expedienteForm.ofi_salida" class="w-full" />

                </div>
                <div class="space-y-1">
                    <label value="Fecha-Salida" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Salida</label>
                    <x-input wire:model="expedienteForm.fecha_salida" class="w-full" />

                </div>
            </div>
        </div>
        @endif

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            @if ($expedienteEncontrado)
            <x-button
                wire:click="guardar"
                class="bg-sky-600 hover:bg-sky-700 text-white font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition-all duration-200 dark:bg-sky-500 dark:hover:bg-sky-600 transform hover:scale-105">
                Guardar
            </x-button>
            @endif
            <x-button
                wire:click="cerrar"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transform hover:scale-105">
                Cerrar
            </x-button>
        </div>
    </div>
</flux:modal>