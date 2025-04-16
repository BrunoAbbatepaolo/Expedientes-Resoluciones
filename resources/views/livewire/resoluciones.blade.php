<div class="space-y-4">
    <div class="text-3xl font-bold text-center p-4 dark:bg-zinc-900 dark:bg-black dark:text-white rounded-lg dark:shadow-md">
        Sistema de Carga de Resoluciones
    </div>

    <div class="flex items-center gap-2 p-4">
        <input type="text"
            placeholder="Búsqueda de resoluciones"
            class="px-4 py-2 w-full border rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-400 
               dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:ring-blue-500">

        <button wire:click="abrirModal"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 cursor-pointer transition">
            Cargar Resolución
        </button>
    </div>

    <div class="p-4">
        <table class="w-full border-collapse border border-gray-600 shadow-lg rounded-lg">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700 dark:text-white">
                    <th class="p-4 border border-gray-600">Nº de Expediente</th>
                    <th class="p-4 border border-gray-600">Nº de Resolucion</th>
                    <th class="p-4 border border-gray-600">Fecha de Resolucion</th>
                    <th class="p-4 border border-gray-600">Barrio</th>
                    <th class="p-4 border border-gray-600">Casa</th>
                    <th class="p-4 border border-gray-600">Archivo PDF</th>
                    <th class="p-4 border border-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resolucionesConExpediente as $resolucion)
                <tr class="bg-white dark:bg-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <td class="p-4 border border-gray-600 text-center">
                        @if($resolucion->expediente)
                        {{ $resolucion->expediente->numero }}
                        @else
                        No hay expediente asociado.
                        @endif
                    </td>
                    <td class="p-4 border border-gray-600 text-center">{{ $resolucion->numero_resolucion }}</td>
                    <td class="p-4 border border-gray-600 text-center">{{ $resolucion->fecha }}</td>
                    <td class="p-4 border border-gray-600 text-center">{{ $resolucion->cod_barrio }}</td>
                    <td class="p-4 border border-gray-600 text-center">{{ $resolucion->cod_casa }}</td>
                    <td class="p-4 border border-gray-600 text-center text-red-400"><a href="{{ $resolucion->pdf }}" target="_blank">Ver PDF</a></td>

                    <td class="p-4 border border-gray-600 flex justify-center gap-2">
                        <button wire:click=""
                            class="px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 cursor-pointer transition">
                            Modificar
                        </button>
                        <button wire:click=""
                            class="px-4 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 cursor-pointer transition">
                            Borrar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal con Alpine.js -->
    <div x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Overlay de fondo oscuro -->
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <!-- Contenido del modal -->
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 z-10"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">

                <!-- Encabezado del modal -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Agregar Resolución
                    </h3>
                    <button @click="show = false" wire:click="cerrarModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="guardar">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Nº de Expediente</label>
                            <input type="text" wire:model="numero_exp" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('numero_exp') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Nº de Resolución</label>
                            <input type="text" wire:model="numero_resolucion" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('numero_resolucion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Fecha de Resolución</label>
                            <input type="date" wire:model="fecha" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('fecha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Barrio</label>
                            <input type="text" wire:model="cod_barrio" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('cod_barrio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Casa</label>
                            <input type="text" wire:model="cod_casa" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('cod_casa') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">PDF</label>
                            <input type="file" wire:model="pdf" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                            @error('pdf') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>