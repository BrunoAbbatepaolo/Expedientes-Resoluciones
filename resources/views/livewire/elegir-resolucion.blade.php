<div class="max-w-6xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2 dark:text-gray-200">Crear nueva resolución</h1>
        <p class="text-gray-600 dark:text-gray-100">Selecciona el tipo de resolución que deseas crear</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($tipos as $tipo)
            <div class="group bg-white border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-xl transition-all duration-200 cursor-pointer overflow-hidden"
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
                    <h3 class="font-semibold text-gray-900 mb-1">{{ $tipo['nombre'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $tipo['descripcion'] ?? 'Resoluciones ' . strtolower($tipo['nombre']) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>