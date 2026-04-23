<flux:modal name="modal-exp" class="md:w-[35rem]">
    <div class="p-2 space-y-6">

        <div class="flex flex-col items-center justify-center text-center space-y-3">
            <div class="p-3 bg-blue-50 dark:bg-blue-500/10 rounded-full text-blue-600 dark:text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <flux:heading size="lg" class="text-2xl font-bold text-gray-900 dark:text-white">
                    Carga de Nuevo Expediente
                </flux:heading>
                <flux:text class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Ingrese el número del expediente para continuar
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <x-input type="text" wire:model="busquedaExp" wire:keydown.enter="buscar"
                    placeholder="Buscar expediente..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 shadow-sm" />
            </div>

            <button wire:click="buscar"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <span>Buscar</span>
            </button>
        </div>

        @if ($expedienteEncontrado)
            <div class="rounded-xl p-4 border border-gray-200 dark:border-white/10 bg-transparent">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nº Expediente
                        </label>
                        <x-input type="text" value="{{ $expedienteEncontrado['numero'] }}"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-lg text-gray-900 dark:text-gray-200 font-mono font-semibold"
                            readonly disabled />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nº Fojas
                        </label>
                        <x-input type="text" value="{{ $expedienteEncontrado['folio'] }}"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-lg text-gray-900 dark:text-gray-200"
                            readonly disabled />
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Asunto
                        </label>
                        <x-input type="text" value="{{ $asunto }}"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-lg text-gray-900 dark:text-gray-200"
                            readonly disabled />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Causante
                        </label>
                        <x-input type="text" value="{{ $causante }}"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-lg text-gray-900 dark:text-gray-200"
                            readonly disabled />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Creación
                        </label>
                        <x-input type="text" value="{{ $expedienteEncontrado['fecha'] }}"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-lg text-gray-900 dark:text-gray-200"
                            readonly disabled />
                    </div>

                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
            <button wire:click="cerrar"
                class="px-4 py-2 bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 font-medium rounded-lg transition-colors cursor-pointer">
                Cerrar
            </button>

            @if ($expedienteEncontrado)
                <button wire:click="guardar"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                    Guardar
                </button>
            @endif
        </div>

    </div>
</flux:modal>
