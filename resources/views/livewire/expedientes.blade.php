<div class="space-y-4">
    <!-- Barra de búsqueda y botones -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <!-- Barra de búsqueda simplificada -->
        <div class="relative w-full sm:w-1/3">
            <x-input type="text" wire:model.live="search" placeholder="Buscar expediente..."
                class="w-full pl-10 pr-4" />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 size-5 text-gray-500"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Grupo de botones simplificado -->
        <div class="flex gap-2">
            <x-button wire:click="$set('modalFiltro', true)" class="bg-blue-600 hover:bg-blue-700">
                Filtros
            </x-button>
            <x-button wire:click="$set('modalExp', true)" class="bg-sky-600 hover:bg-sky-700">
                Nuevo Expediente
            </x-button>
        </div>
    </div>

    <!-- Tabla simplificada -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <x-th>Número</x-th>
                    <x-th>Fojas</x-th>
                    <x-th>Ingreso</x-th>
                    <x-th>Causante</x-th>
                    <x-th>Asunto</x-th>
                    <x-th>Área Salida</x-th>
                    <x-th>Oficina Salida</x-th>
                    <x-th>Salida</x-th>
                    <x-th class="text-center">Acciones</x-th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($expedientes as $expediente)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <x-td click="verDetalle({{ $expediente->id }})" class="cursor-pointer">
                        {{ $expediente->num_exp }}
                    </x-td>
                    <x-td class="text-center">
                        {{ $expediente->folio }}
                    </x-td>
                    <x-td>
                        {{ $this->obtenerDMY($expediente->fecha_ingreso) }}
                    </x-td>
                    <x-td>
                        {{ $expediente->causante }}
                    </x-td>
                    <x-td>
                        <div class="max-w-xs truncate">{{ $expediente->asunto }}</div>
                    </x-td>
                    <x-td>
                        {{ $expediente->oficina->area->nombre ?? '-' }}
                    </x-td>
                    <x-td>
                        {{ $expediente->oficina->nombre ?? '-' }}
                    </x-td>
                    <x-td>
                        {{ $this->obtenerDMY($expediente->fecha_salida) }}
                    </x-td>
                    <x-td class="text-right">
                        <!-- Contenedor del menú -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open"
                                class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200">
                                <svg class="size-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 3c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 14c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>

                            <!-- Menú desplegable sin transiciones -->
                            <div x-show="open" x-cloak
                                class="absolute top-0 right-6 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 z-50 overflow-hidden"
                                style="transform: translateX(-15%);" x-transition:enter="transition-none"
                                x-transition:leave="transition-none">
                                <!-- Contenido del menú -->
                                <button wire:click="editar({{ $expediente->id }})"
                                    class="w-full px-4 py-3 text-sm text-left text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-600 hover:text-white dark:hover:from-blue-600 dark:hover:to-blue-700 transition-all duration-200 flex items-center gap-2">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar expediente
                                </button>
                                <button wire:click="confirmarBorrado({{ $expediente->id }})"
                                    class="w-full px-4 py-3 text-sm text-left text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-red-500 hover:to-red-600 hover:text-white dark:hover:from-red-600 dark:hover:to-red-700 transition-all duration-200 flex items-center gap-2">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar expediente
                                </button>
                            </div>
                        </div>
                    </x-td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">No se encontraron expedientes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $expedientes->links() }}
    </div>

    <!-- Modales -->
    @include('livewire.modal-buscar-expediente')
    @include('livewire.modal-editar-expediente')
    @include('livewire.modal-filtro-expediente')
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Manejo simplificado de alertas
        ['success', 'error', 'info', 'warning'].forEach(type => {
            Livewire.on(`swal:${type}`, (data) => {
                Swal.fire({
                    icon: type,
                    title: data[0]?.title || type.charAt(0).toUpperCase() + type.slice(1),
                    text: data[0]?.text || '',
                    showConfirmButton: data[0]?.showConfirmButton ?? true,
                    timer: data[0]?.timer || null
                });
            });
        });

        Livewire.on('swal:confirm', (data) => {
            Swal.fire({
                title: data[0]?.title || '¿Está seguro?',
                text: data[0]?.text || '¿Desea continuar con esta acción?',
                icon: data[0]?.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: data[0]?.confirmButtonText || 'Sí',
                cancelButtonText: data[0]?.cancelButtonText || 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && data[0]?.onConfirmed) {
                    Livewire.dispatch(data[0].onConfirmed);
                } else if (!result.isConfirmed && data[0]?.onDismissed) {
                    Livewire.dispatch(data[0].onDismissed);
                }
            });
        });
    });
</script>
@endpush