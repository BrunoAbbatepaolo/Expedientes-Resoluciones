<div class="space-y-4">
    <!-- Título principal -->
    <div class="text-3xl font-bold text-center p-4 dark:bg-zinc-900 dark:bg-black dark:text-white rounded-lg dark:shadow-md">
        Sistema de Gestión de Oficinas
    </div>

    <!-- Barra de búsqueda y botones -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <!-- Barra de búsqueda -->
        <div class="relative w-full sm:w-1/3">
            <x-input
                type="text"
                wire:model.live="inputBusqueda"
                placeholder="Buscar por nombre..."
                class="w-full pl-10 pr-4" />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 size-5 text-gray-500"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Botón de nueva oficina (si lo necesitás) -->
        {{-- <x-button wire:click="$set('modalNuevaOficina', true)" class="bg-sky-600 hover:bg-sky-700">
            Nueva Oficina
        </x-button> --}}
    </div>

    <!-- Tabla de resultados -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <x-th>Nombre</x-th>
                    {{-- Podés agregar más columnas acá si hace falta --}}
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($oficinas as $oficina)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <x-td>{{ $oficina->nombre }}</x-td>
                </tr>
                @empty
                <tr>
                    <td colspan="1" class="px-6 py-4 text-center text-gray-500">No se encontraron oficinas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $oficinas->links() }}
    </div>
</div>