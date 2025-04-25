<div class="space-y-4">
    <div class="text-3xl font-bold text-center p-4 bg-blue-200 dark:bg-zinc-900 dark:text-white rounded-xl dark:shadow-md">
        Sistema de Carga de Expedientes
    </div>
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
            <flux:modal.trigger name="modal-filtro">
                <flux:button class="cursor-pointer">Filtrar</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="modal-exp">
                <flux:button class="cursor-pointer">Nuevo Expediente</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Tabla simplificada -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-blue-300 dark:bg-gray-800 sticky top-0 z-10">
                <tr>
                    <x-th class="w-[110px]">Número</x-th>
                    <x-th class="hidden 2xl:table-cell w-[80px]">Fojas</x-th>
                    <x-th class="w-[100px]">Ingreso</x-th>
                    <x-th class="w-[180px]">Causante</x-th>
                    <x-th class="hidden 2xl:table-cell w-[200px]">Asunto</x-th>
                    <x-th class="w-[180px]">Oficina Salida</x-th>
                    <x-th class="w-[100px]">Salida</x-th>
                    <x-th class="text-center w-[80px]">Acciones</x-th>
                </tr>
            </thead>
            <tbody class="bg-blue-50 dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($expedientes as $expediente)
                <tr class="hover:bg-violet-50 dark:hover:bg-gray-800/50">
                    <x-td click="verDetalle({{ $expediente->id }})" class="cursor-pointer text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $expediente->num_exp }}
                    </x-td>
                    <x-td class="hidden 2xl:table-cell text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $expediente->folio }}
                    </x-td>
                    <x-td class="text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $this->obtenerDMY($expediente->fecha_ingreso) }}
                    </x-td>
                    <x-td class="text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $this->formatearCausante($expediente->causante) }}
                    </x-td>
                    <x-td class="hidden 2xl:table-cell text-center">
                        <div class="overflow-hidden text-ellipsis whitespace-nowrap">{{ $expediente->asunto }}</div>
                    </x-td>
                    <x-td class="text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $expediente->oficina->nombre ?? '-' }}
                    </x-td>
                    <x-td class="text-center overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $this->obtenerDMY($expediente->fecha_salida)}}
                    </x-td>
                    <x-td class="text-center">
                        <!-- Contenedor del menú -->
                        <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open"
                                class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors cursor-pointer duration-200">
                                <svg class="size-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 3c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 14c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>

                            <!-- Menú desplegable sin transiciones -->
                            <div x-show="open" x-cloak
                                class="absolute right-0 z-50 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden"
                                x-transition:enter="transition-none"
                                x-transition:leave="transition-none">
                                <!-- Contenido del menú -->
                                <flux:modal.trigger name="modal-editarExpediente">
                                    <button wire:click="editar({{ $expediente->id }})"
                                        class="w-full px-4 py-3 text-sm text-left text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-600 hover:text-white dark:hover:from-blue-600 dark:hover:to-blue-700 transition-all duration-200 flex items-center gap-2">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar expediente
                                    </button>
                                </flux:modal.trigger>
                                <flux:modal.trigger name="modal-ConfirmarBorrado">
                                    <button wire:click="confirmarBorrado({{ $expediente->id }})"
                                        class="w-full px-4 py-3 text-sm text-left text-red-600 dark:text-red-400 hover:bg-gradient-to-r hover:from-red-500 hover:to-red-600 hover:text-white dark:hover:from-red-600 dark:hover:to-red-700 transition-all duration-200 flex items-center gap-2">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Eliminar expediente
                                    </button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    </x-td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron expedientes.</td>
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
    @include('livewire.modal-editar-expediente')
    @include('livewire.modal-filtros')
    @include('livewire.modal-NuevoExpediente')
    @include('livewire.modal-EditarExpediente')
    @include('livewire.modal-ConfirmarBorrado')
</div>