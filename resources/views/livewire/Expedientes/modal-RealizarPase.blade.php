<flux:modal name="modal-realizarPase" class="md:w-[26rem]">
    <div class="space-y-6 p-5 rounded-lg">
        <div>
            <flux:heading size="lg" class="text-2xl text-center font-bold text-gray-800 dark:text-white">Realizar Pase</flux:heading>
            <flux:text class="mt-2 text-gray-600 dark:text-gray-300">Elegí la oficina de destino. El expediente queda pendiente de aceptación hasta que esa oficina lo confirme.</flux:text>
        </div>

        <div class="mb-4 relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Oficina de Destino</label>
            <div class="relative">
                <input
                    type="text"
                    wire:model.live="query"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2
              bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100"
                    placeholder="Buscar oficina de destino"
                    autocomplete="off" />

                @if(!empty($query))
                <button type="button"
                    wire:click="$set('query', '')"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
                @endif
            </div>

            @if(!empty($oficinas))
            <ul class="absolute bg-white dark:bg-zinc-700 border border-gray-200 dark:border-gray-700
           rounded-md shadow-lg w-full z-10 max-h-48 overflow-y-auto mt-1
           scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                @foreach($oficinas as $oficina)
                <li wire:key="oficina-pase-{{ $oficina->id }}"
                    class="px-4 py-2.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700
               text-gray-800 dark:text-gray-200 transition-colors duration-150"
                    wire:click="selectOficina({{ $oficina->id }})">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">{{ $oficina->nombre }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                            {{ $oficina->codigo }}
                        </span>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif

            <!-- Campo oculto para mantener el ID de la oficina -->
            <input type="hidden" wire:model="expedienteForm.ofi_salida">
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button
                wire:click="confirmarPase"
                class="bg-sky-600 hover:bg-sky-700 text-white font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition-all duration-200 dark:bg-sky-500 dark:hover:bg-sky-600 transform hover:scale-105">
                Confirmar Pase
            </x-button>
            <x-button
                wire:click="cancelarPase"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-5 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transform hover:scale-105">
                Cerrar
            </x-button>
        </div>
    </div>
</flux:modal>
