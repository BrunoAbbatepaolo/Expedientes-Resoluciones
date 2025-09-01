<flux:modal name="modal-exp" class="md:w-[35rem]">
    <div
        class="space-y-2 p-2 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-gray-800 dark:to-gray-900 shadow-2xl border border-blue-100 dark:border-gray-700">

        <!-- Header Section with Icon and Title -->
        <div class="relative">
            <!-- Header Content -->
            <div class="flex items-center justify-center space-x-4 py-2">
                <div
                    class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="text-center">
                    <flux:heading size="lg"
                        class="text-3xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">
                        Carga de Nuevo Expediente
                    </flux:heading>
                    <flux:text class="mt-2 text-gray-600 dark:text-gray-300 font-medium">
                        Ingrese el número del expediente para continuar
                    </flux:text>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="rounded-xl p-3">
            <div class="flex justify-center gap-3 items-center">
                <div class="relative flex-1">
                    <x-input type="text" wire:model="busquedaExp" wire:keydown.enter="buscar"
                        placeholder="Buscar expediente..."
                        class="w-full pl-12 pr-4 py-4 bg-white dark:bg-gray-700 border-2 border-blue-200 dark:border-gray-600 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 font-medium shadow-sm" />
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-500 dark:text-blue-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button wire:click="buscar"
                    class="px-6 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 cursor-pointer flex items-center space-x-2 group">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Buscar</span>
                </button>
            </div>
        </div>

        <!-- Expediente Found Section -->
        @if ($expedienteEncontrado)
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Expediente Details -->
                <div class="p-3 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Número Expediente -->
                        <div class="space-y-3">
                            <label
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="p-1 bg-blue-100 dark:bg-blue-900/30 rounded-md">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </div>
                                <span>Nº Expediente</span>
                            </label>
                            <x-input type="text" value="{{ $expedienteEncontrado['numero'] }}"
                                class="w-full px-2 py-1 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-mono font-bold text-lg shadow-inner"
                                readonly />
                        </div>

                        <!-- Número Fojas -->
                        <div class="space-y-3">
                            <label
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="p-1 bg-green-100 dark:bg-green-900/30 rounded-md">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span>Nº Fojas</span>
                            </label>
                            <x-input type="text" value="{{ $expedienteEncontrado['folio'] }}"
                                class="w-full px-2 py-1 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-700 border border-green-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold shadow-inner"
                                readonly />
                        </div>

                        <!-- Asunto -->
                        <div class="space-y-2 md:col-span-2">
                            <label
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="p-1 bg-purple-100 dark:bg-purple-900/30 rounded-md">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span>Asunto</span>
                            </label>
                            <x-input type="text" value="{{ $asunto }}"
                                class="w-full px-2 py-1 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-700 dark:to-gray-700 border border-purple-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white shadow-inner"
                                readonly />
                        </div>

                        <!-- Causante -->
                        <div class="space-y-2">
                            <label
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="p-1 bg-orange-100 dark:bg-orange-900/30 rounded-md">
                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span>Causante</span>
                            </label>
                            <x-input type="text" value="{{ $causante }}"
                                class="w-full px-2 py-1 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-gray-700 dark:to-gray-700 border border-orange-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white shadow-inner"
                                readonly />
                        </div>

                        <!-- Fecha de Creación -->
                        <div class="space-y-2">
                            <label
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="p-1 bg-red-100 dark:bg-red-900/30 rounded-md">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span>Fecha de Creación</span>
                            </label>
                            <x-input type="text" value="{{ $expedienteEncontrado['fecha'] }}"
                                class="w-full px-2 py-1 bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-700 border border-red-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white shadow-inner"
                                readonly />
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-end gap-4 pt-3 border-t border-gray-200 dark:border-gray-700">
            @if ($expedienteEncontrado)
                <x-button wire:click="guardar"
                    class="bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/30 cursor-pointer focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 flex items-center space-x-2 group">
                    <span>Guardar</span>
                </x-button>
            @endif

            <x-button wire:click="cerrar"
                class="bg-gradient-to-r from-gray-200 via-gray-300 to-gray-400 hover:from-gray-300 hover:via-gray-400 hover:to-gray-500 text-gray-700 dark:from-gray-700 dark:via-gray-800 dark:to-gray-900 dark:hover:from-gray-600 dark:hover:via-gray-700 dark:hover:to-gray-800 dark:text-gray-200 font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-gray-400/30 cursor-pointer focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 flex items-center space-x-2 group">
                <span>Cerrar</span>
            </x-button>
        </div>
    </div>
</flux:modal>
