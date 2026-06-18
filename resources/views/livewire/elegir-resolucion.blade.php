<div class="max-w-6xl mx-auto p-6">
    <div class="mb-4">
        <x-button onclick="window.location.href='/resoluciones'" size="sm" icon="arrow-uturn-left">
            Volver
        </x-button>
    </div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2 dark:text-gray-200">Crear nueva resolución</h1>
        <p class="text-gray-600 dark:text-gray-100">Selecciona el tipo de resolución que deseas crear</p>
    </div>

    <div class="mb-6">
        <input
            type="text"
            wire:model.live="busqueda"
            placeholder="Buscar tipo de resolución..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
        />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($this->tiposFiltrados as $tipo)
            <div class="group bg-white border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-xl transition-all duration-200 cursor-pointer overflow-hidden dark:bg-zinc-700 dark:border-zinc-600"
                 wire:click="seleccionar('{{ $tipo['nombre'] }}')">
                
                <!-- Vista previa del documento -->
                <div class="aspect-[4/3] bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center relative overflow-hidden">
                    <!-- Simulación de documento -->
                    <div class="w-24 h-32 bg-white shadow-lg rounded-sm flex flex-col">
                        <div class="h-4 bg-blue-500 rounded-t-sm"></div>
                        <div class="flex-1 p-2 space-y-1">
                            <div class="h-1 bg-gray-300 rounded w-full"></div>
                            <div class="h-1 bg-gray-300 rounded w-3/4"></div>
                            <div class="h-1 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-1 bg-gray-300 rounded w-5/6"></div>
                            <div class="h-1 bg-gray-300 rounded w-2/3"></div>
                            <div class="h-1 bg-gray-300 rounded w-4/5"></div>
                        </div>
                    </div>
                    
                    <!-- Overlay hover -->
                    <div class="absolute inset-0 bg-blue-500 opacity-0 group-hover:opacity-10 transition-opacity duration-200"></div>
                </div>
                
                <!-- Información del tipo -->
                <div class="p-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
{{ $tipo['display'] ?? $tipo['nombre'] }}
                    </h3>
                </div>
            </div>
        @endforeach
    </div>
</div>