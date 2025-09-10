<div class="space-y-4">
    @if (auth()->user()->permiso('resolucion_ver'))
        <div class="text-3xl font-bold text-center p-4 dark:bg-zinc-900 dark:text-white rounded-lg dark:shadow-md">
            Sistema de Carga de Resoluciones
        </div>

        <div class="flex items-center gap-2 p-4">
            <input type="text" placeholder="Búsqueda de resoluciones"
                class="px-4 py-2 w-full border rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-400
               dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:ring-blue-500">
            @if (auth()->user()->permiso('resolucion_editar'))
                <button wire:click="abrirModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer transition">
                    Cargar Resolución
                </button>
                <x-button wire:navigate href="{{ route('resoluciones.elegir') }}" icon="plus" color="primary">
                    Generar nueva resolución
                </x-button>
            @endif
        </div>


        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-blue-300 dark:bg-gray-800 sticky top-0 z-10 rounded-t-lg">
                    <tr>
                        <x-th class="w-[140px] first:rounded-tl-lg last:rounded-tr-lg">Nº de Expediente</x-th>
                        <x-th class="w-[140px] first:rounded-tl-lg last:rounded-tr-lg">Nº de Resolución</x-th>
                        <x-th class="w-[120px] first:rounded-tl-lg last:rounded-tr-lg">Fecha de Resolución</x-th>
                        <x-th class="w-[100px] first:rounded-tl-lg last:rounded-tr-lg">Barrio</x-th>
                        <x-th class="w-[80px]  first:rounded-tl-lg last:rounded-tr-lg">Casa</x-th>
                        <x-th class="w-[100px] first:rounded-tl-lg last:rounded-tr-lg">Archivo PDF</x-th>
                        @if (auth()->user()->permiso('resolucion_editar'))
                            <x-th class="text-center w-[120px] first:rounded-tl-lg last:rounded-tr-lg">Acciones</x-th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-blue-50 dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @php $hasActions = auth()->user()->permiso('resolucion_editar'); @endphp
                    @forelse ($resolucionesConExpediente as $resolucion)
                        <tr class="hover:bg-violet-50 dark:hover:bg-gray-800/50 last:border-b-0">
                            <x-td class="text-center truncate @if ($loop->last) rounded-bl-lg @endif">
                                @if ($resolucion->expediente)
                                    {{ $resolucion->expediente->numero }}
                                @else
                                    <span class="text-gray-400 italic">Sin expediente</span>
                                @endif
                            </x-td>
                            <x-td class="truncate text-center">
                                {{ $resolucion->numero_resolucion }}
                            </x-td>
                            <x-td class="truncate text-center">
                                {{ $resolucion->fecha }}
                            </x-td>
                            <x-td class="truncate text-center">
                                {{ $resolucion->cod_barrio }}
                            </x-td>
                            <x-td class="truncate text-center">
                                {{ $resolucion->cod_casa }}
                            </x-td>
                            <x-td class="text-center truncate @if ($loop->last && !$hasActions) rounded-br-lg @endif">
                                <a href="{{ $resolucion->pdf }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-red-500 hover:text-red-600 transition-colors duration-200">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Ver PDF
                                </a>
                            </x-td>

                            @if (auth()->user()->permiso('resolucion_editar'))
                                <x-td class="text-center @if ($loop->last && $hasActions) rounded-br-lg @endif">
                                    <div class="relative inline-block" x-data="{ open: false }"
                                        @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer">
                                            <svg class="size-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M12 3c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 14c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 z-50 w-48 mt-2 rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden origin-top-right">
                                            <flux:modal.trigger name="edit-profile">
                                                <button wire:click="cargarResolucion({{ $resolucion->id }})"
                                                    @click="open = false"
                                                    class="w-full px-4 py-3 text-sm text-left text-gray-700 dark:text-gray-200 hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-600 hover:text-white dark:hover:from-blue-600 dark:hover:to-blue-700 transition-all duration-200 flex items-center gap-2 cursor-pointer">
                                                    <svg class="size-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Modificar resolución
                                                </button>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-profile">
                                                <button @click="open = false"
                                                    class="w-full px-4 py-3 text-sm text-left text-red-600 dark:text-red-400 hover:bg-gradient-to-r hover:from-red-500 hover:to-red-600 hover:text-white dark:hover:from-red-600 dark:hover:to-red-700 transition-all duration-200 flex items-center gap-2 cursor-pointer">
                                                    <svg class="size-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Eliminar resolución
                                                </button>
                                            </flux:modal.trigger>
                                        </div>
                                    </div>
                                </x-td>
                            @endif
                        </tr>
                    @empty
                        <tr class="last:rounded-bl-lg last:rounded-br-lg">
                            <td colspan="7"
                                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 rounded-b-lg">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="size-12 text-gray-300 dark:text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron resoluciones</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- Modal con Alpine.js -->
        <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Overlay de fondo oscuro -->
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <!-- Contenido del modal -->
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 z-10"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">

                    <!-- Encabezado del modal -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Agregar Resolución
                        </h3>
                        <button @click="show = false" wire:click="cerrarModal"
                            class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Formulario -->
                    <form wire:submit.prevent="guardar">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">Nº de Expediente</label>
                                <input type="text" wire:model="numero_exp"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('numero_exp')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">Nº de Resolución</label>
                                <input type="text" wire:model="numero_resolucion"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('numero_resolucion')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">Fecha de Resolución</label>
                                <input type="date" wire:model="fecha"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('fecha')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">Barrio</label>
                                <input type="text" wire:model="cod_barrio"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('cod_barrio')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">Casa</label>
                                <input type="text" wire:model="cod_casa"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('cod_casa')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300">PDF</label>
                                <input type="file" wire:model="pdf"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md">
                                @error('pdf')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" wire:click="cerrarModal"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- modal para editar resoluciones -->
        <flux:modal name="edit-profile" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Modificar Resolucion</flux:heading>
                    <flux:text class="mt-2">Modifique los cambios en la resolucion</flux:text>
                </div>

                <flux:input wire:model="resolucionForm.numero_exp" label="Nº de Expediente"
                    placeholder="Ingrese el numero de Expediente" />

                <flux:input wire:model="resolucionForm.numero_resolucion" label="Nº de Resolución"
                    placeholder="Ingrese el Nº de Resolución" />

                <flux:input type="date" wire:model="resolucionForm.fecha" label="Fecha"
                    placeholder="Ingrese la fecha" />

                <flux:input wire:model="resolucionForm.cod_barrio" label="Barrio" placeholder="Ingrese el Barrio" />

                <flux:input wire:model="resolucionForm.cod_casa" label="Casa"
                    placeholder="Ingrese el Nº de Casa" />

                <div class="flex">
                    <flux:spacer />

                    <flux:button type="submit" variant="primary" class="cursor-pointer">Guardar Cambios
                    </flux:button>
                </div>
            </div>
        </flux:modal>
        <flux:modal name="delete-profile" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Borrar resolucion?</flux:heading>

                    <flux:text class="mt-2">
                        <p>Estas seguro que quieres borrarla?</p>

                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">Cancelar</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="danger" class="cursor-pointer">Borrar</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
