<flux:modal name="modal-filtro" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-2xl text-center">Filtro Avanzado</flux:heading>
            <flux:text class="mt-2">Seleccione las Fechas Desde y Hasta.</flux:text>
        </div>

        <!-- Fecha Desde -->
        <flux:input
            label="Fecha Desde"
            type="date"
            wire:model="filtro.fechaDesde"
            class="" />

        <!-- Fecha Hasta -->
        <flux:input
            label="Fecha Hasta"
            type="date"
            wire:model="filtro.fechaHasta"
            class="" />

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-2">
            <x-button
                wire:click="aplicarFiltros"
                class="bg-sky-600 hover:bg-sky-700 text-white font-medium px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                Filtrar
            </x-button>

            <x-button
                wire:click="limpiarFiltros"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400">
                Limpiar Filtro
            </x-button>
        </div>
    </div>
</flux:modal>