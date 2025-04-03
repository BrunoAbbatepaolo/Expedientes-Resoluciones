<div class="p-8 bg-white dark:bg-gray-800 rounded-xl">
    <!-- Header con búsqueda y acciones -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <!-- Barra de búsqueda mejorada -->
        <div class="relative w-full sm:w-1/3">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-blue-500">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <x-input type="text" wire:model.live="search" placeholder="Buscar expediente..."
                class="w-full pl-10 pr-4 py-2 border-0 ring-1 ring-blue-100 dark:ring-blue-900 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 transition-all duration-200" />
        </div>

        <!-- Grupo de botones -->
        <div class="flex items-center gap-3">
            <button type="button" wire:click="$set('modalFiltro', true)"
                class="flex items-center gap-2 px-4 py-2 bg-blue-800 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span>Filtros</span>
            </button>

            <button type="button" wire:click="$set('modalExp', true)"
                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 dark:from-sky-600 dark:to-sky-700 dark:hover:from-sky-700 dark:hover:to-sky-800 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Expediente</span>
            </button>

        </div>
    </div>

    <!-- Container de la tabla con overflow visible para los menús -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md">
        <table class="w-full">
            <thead>
                <tr class="bg-gradient-to-r from-sky-800 to-indigo-800 dark:from-sky-900 dark:to-sky-900 text-white">
                    <th class="px-6 py-4 text-left font-medium">Número</th>
                    <th class="px-6 py-4 text-left font-medium">Fojas</th>
                    <th class="px-6 py-4 text-left font-medium">Ingreso</th>
                    <th class="px-6 py-4 text-left font-medium">Causante</th>
                    <th class="px-6 py-4 text-left font-medium">Asunto</th>
                    <th class="px-6 py-4 text-left font-medium">Área Salida</th>
                    <th class="px-6 py-4 text-left font-medium">Oficina Salida</th>
                    <th class="px-6 py-4 text-left font-medium">Salida</th>
                    <th class="px-6 py-4 text-center font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($expedientes as $expediente)
                <tr class="group hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors duration-200">
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 cursor-pointer"
                        wire:click="verDetalle({{ $expediente->id }})">
                        {{ $expediente->num_exp }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 text-center">
                        {{ $expediente->folio }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        {{ $this->obtenerDMY($expediente->fecha_ingreso) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        {{ $expediente->causante }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        <div class="max-w-xs truncate">{{ $expediente->asunto }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        {{ $expediente->oficina->area->nombre ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        {{ $expediente->oficina->nombre ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                        {{ $this->obtenerDMY($expediente->fecha_salida) }}
                    </td>
                    <td class="px-6 py-4 text-right relative">
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
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">No se encontraron
                        expedientes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación mejorada -->
    <div class="mt-6">
        {{ $expedientes->links() }}
    </div>

    <!-- Modales -->
    @include('livewire.modal-buscar-expediente')
    @include('livewire.modal-editar-expediente')
    @include('livewire.modal-filtro-expediente')
    <script>
        document.addEventListener('livewire:init', () => {
            // Success alert
            Livewire.on('swal:success', (data) => {
                Swal.fire({
                    icon: 'success',
                    title: data[0].title || 'Éxito',
                    text: data[0].text || '',
                    position: data[0].position || 'center',
                    showConfirmButton: data[0].showConfirmButton || true,
                    timer: data[0].timer || null
                });
            });

            // Error alert
            Livewire.on('swal:error', (data) => {
                Swal.fire({
                    icon: 'error',
                    title: data[0].title || 'Error',
                    text: data[0].text || '',
                    position: data[0].position || 'center',
                    showConfirmButton: data[0].showConfirmButton || true,
                    timer: data[0].timer || null
                });
            });

            // Info alert
            Livewire.on('swal:info', (data) => {
                Swal.fire({
                    icon: 'info',
                    title: data[0].title || 'Información',
                    text: data[0].text || '',
                    position: data[0].position || 'center',
                    showConfirmButton: data[0].showConfirmButton || true,
                    timer: data[0].timer || null
                });
            });

            // Warning alert
            Livewire.on('swal:warning', (data) => {
                Swal.fire({
                    icon: 'warning',
                    title: data[0].title || 'Advertencia',
                    text: data[0].text || '',
                    position: data[0].position || 'center',
                    showConfirmButton: data[0].showConfirmButton || true,
                    timer: data[0].timer || null
                });
            });

            // Confirmation alert
            Livewire.on('swal:confirm', (data) => {
                Swal.fire({
                    title: data[0].title || '¿Está seguro?',
                    text: data[0].text || '¿Desea continuar con esta acción?',
                    icon: data[0].icon || 'warning',
                    showCancelButton: data[0].showCancelButton || true,
                    confirmButtonColor: data[0].confirmButtonColor || '#3085d6',
                    cancelButtonColor: data[0].cancelButtonColor || '#d33',
                    confirmButtonText: data[0].confirmButtonText || 'Sí',
                    cancelButtonText: data[0].cancelButtonText || 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disparar el evento onConfirmed si está configurado
                        if (data[0].onConfirmed) {
                            Livewire.dispatch(data[0].onConfirmed);
                        }
                    } else {
                        // Disparar el evento onDismissed si está configurado
                        if (data[0].onDismissed) {
                            Livewire.dispatch(data[0].onDismissed);
                        }
                    }
                });
            });
        });
    </script>
</div>