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
                        <th class="px-4 py-2 text-left text-sm font-medium">Oficina</th>
                        @if (auth()->user()->permiso('lista_usuario_editar'))
                            <th class="px-4 py-2 text-left text-sm font-medium">Permisos</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Acciones</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="bg-blue-50 dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                            <td class="px-4 py-2 truncate">{{ $usuario->nombre }}</td>
                            <td class="px-4 py-2 truncate">{{ $usuario->apellido }}</td>
                            <td class="px-4 py-2 truncate">{{ $usuario->email }}</td>

                            {{-- Columna Oficina --}}
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <x-badge>
                                        {{ $oficinaPorUsuario[$usuario->id] ?? 'Sin asignar' }}
                                    </x-badge>

                                    @if (auth()->user()->permiso('lista_usuario_editar'))
                                        <flux:modal.trigger name="modal-oficina">
                                            <button type="button" wire:click="editarOficina({{ $usuario->id }})"
                                                class="inline-flex items-center gap-1 rounded-md bg-indigo-500 px-3 py-1 text-white text-xs hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                                Editar oficina
                                            </button>
                                        </flux:modal.trigger>
                                    @endif
                                </div>
                            </td>

                            @if (auth()->user()->permiso('lista_usuario_editar'))
                                {{-- Columna Permisos (abre tu modal de permisos existente) --}}
                                <td class="px-4 py-2">
                                    <flux:modal.trigger name="modal-permisos">
                                        <button type="button" wire:click="seleccionarUsuario({{ $usuario->id }})"
                                            class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-xs">
                                            Asignar Permisos
                                        </button>
                                    </flux:modal.trigger>
                                </td>

                                {{-- Columna Acciones (otros) --}}
                                <td class="px-4 py-2">
                                    <button type="button" wire:click="seleccionarUsuario({{ $usuario->id }})"
                                        class="bg-slate-500 hover:bg-slate-600 text-white px-3 py-1 rounded text-xs">
                                        Editar
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal para editar oficina --}}
        <flux:modal name="modal-oficina" size="md" persistent>
            <div class="p-4 space-y-4">
                <h3 class="text-lg font-semibold">Asignar Oficina</h3>

                <div class="space-y-2">
                    <label for="oficina_id" class="text-sm font-medium">Oficina</label>
                    <select id="oficina_id" wire:model="oficina_id"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">Seleccione oficina…</option>
                        @foreach ($oficinas as $ofi)
                            <option value="{{ $ofi['id'] }}">{{ $ofi['nombre'] }}</option>
                        @endforeach
                    </select>
                    @error('oficina_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <button type="button"
                            class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-sm">
                            Cancelar
                        </button>
                    </flux:modal.close>

                    <button type="button" wire:click="guardarOficina" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60">
                        <span wire:loading.remove>Guardar</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                </div>
            </div>
        </flux:modal>



        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
        @include('livewire.modal-permisos')
    @endif
</div>
