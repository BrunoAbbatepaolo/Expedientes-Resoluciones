<x-dialog-modal wire:model="modalFiltro">
    <!-- Título del modal -->
    <x-slot:title>
        <div class="bg-gradient-to-r from-sky-700 to-sky-900 text-white text-center py-5 -mt-4 -mx-6 rounded-t-lg shadow-md">
            <h2 class="text-2xl font-semibold">Filtro Avanzado</h2>
        </div>
    </x-slot:title>

    <!-- Contenido del modal -->
    <x-slot:content>
        <div class="space-y-6 px-6 py-4">
            <!-- Campo de búsqueda -->
            <div>
                <label for="fechaDesde" class="block text-sm font-medium text-gray-600">Fecha Desde:</label>
                <input 
                    type="date" 
                    id="fechaDesde" 
                    wire:model="filtro.fechaDesde" 
                    class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-sky-500 focus:border-sky-500">
            </div>
            <div>
                <label for="fechaHasta" class="block text-sm font-medium text-gray-600">Fecha Hasta:</label>
                <input 
                    type="date" 
                    id="fechaHasta" 
                    wire:model="filtro.fechaHasta" 
                    class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-sky-500 focus:border-sky-500">
            </div>
        </div>
    </x-slot:content>

    <!-- Footer del modal -->
    <x-slot:footer>
        <div class="flex gap-4 justify-end px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <x-button-blue 
                wire:click="aplicarFiltros"
                class="bg-sky-600 hover:bg-sky-700 text-white font-medium px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                Filtrar
            </x-button-blue>

            <x-button 
                wire:click="limpiarFiltros" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400">
                Limpiar Filtro
            </x-button>

            <x-button 
                wire:click="$set('modalFiltro', false)" 
                class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-400">
                Cerrar
            </x-button>
        </div>
    </x-slot:footer>
</x-dialog-modal>