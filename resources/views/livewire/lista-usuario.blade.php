<div class="space-y-4">
    @if (auth()->user()->permiso('lista_usuario_ver'))
        <div
            class="text-3xl font-bold text-center p-4 bg-blue-200 dark:bg-zinc-900 dark:text-white rounded-xl dark:shadow-md">
            Carga y Configuración de Usuarios
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div class="relative w-full sm:w-1/3">
                <x-input type="text" wire:model.live="search" placeholder="Buscar usuarios..."
                    class="w-full pl-10 pr-4" />
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 size-5 text-gray-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <div class="flex gap-2">
                <flux:modal.trigger name="modal-filtro">
                    <flux:button class="cursor-pointer">Filtrar</flux:button>
                </flux:modal.trigger>
                @if (auth()->user()->permiso('lista_usuario_editar'))
                    <flux:modal.trigger name="modal-exp">
                        <flux:button class="cursor-pointer">Nuevo usuario</flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-blue-300 dark:bg-gray-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Nombre</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Apellido</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Email</th>
                        @if (auth()->user()->permiso('lista_usuario_editar'))
                            <th class="px-4 py-2 text-left text-sm font-medium">Permisos</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-blue-50 dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                            <td class="px-4 py-2">{{ $usuario->nombre }}</td>
                            <td class="px-4 py-2">{{ $usuario->apellido }}</td>
                            <td class="px-4 py-2">{{ $usuario->email }}</td>
                            @if (auth()->user()->permiso('lista_usuario_editar'))
                                <td class="px-4 py-2">
                                    <flux:modal.trigger name="modal-permisos">
                                        <button wire:click="seleccionarUsuario({{ $usuario->id }})"
                                            class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded cursor-pointer">
                                            Asignar Permisos
                                        </button>
                                    </flux:modal.trigger>
                                </td>
                                <td class="px-4 py-2">
                                    <button wire:click="seleccionarUsuario({{ $usuario->id }})"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded cursor-pointer">Editar</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
        @include('livewire.modal-permisos')
    @endif
</div>
