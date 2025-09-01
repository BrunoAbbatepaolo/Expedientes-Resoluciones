<div class="p-6 dark:bg-gray-800 dark:text-gray-100">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl md:text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">
            Crear resolución: <span class="text-blue-600 dark:text-blue-400">{{ $tipo }}</span>
        </h1>

        @if ($modo === '')
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Seleccione el modo de creación</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <x-button
                    wire:click="usarCompleto"
                    class="flex-1 py-3 justify-center"
                    icon="document-text">
                    Usar modelo completo
                </x-button>
                <x-button
                    wire:click="usarPersonalizado"
                    color="secondary"
                    class="flex-1 py-3 justify-center"
                    icon="pencil">
                    Editar personalizado
                </x-button>
            </div>
        </div>

        @elseif ($modo === 'completo')
            @switch($tipo)
                @case('Cancelaciones')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Formulario -->
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-gray-50 dark:bg-gray-600 px-6 py-4 border-b dark:border-gray-600">
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                        <x-input label="Manzana" wire:model.live="datos.manzana" />
                                        <x-input label="Lote" wire:model.live="datos.lote" />
                                    </div>

                                    <x-input label="Nombre del Barrio" wire:model.live="datos.nombre_barrio" />

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Fecha de Cancelación" wire:model.live="datos.fecha_cancelacion" type="date" />
                                        <x-input label="Número foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Foja dictamen" wire:model.live="datos.num_foja_dictamen" />
                                    </div>

                                    <div class="border-t dark:border-gray-600 pt-4">
                                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                            <x-input label="Nombre Cotitular" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>

                                    <div class="border-t dark:border-gray-600 pt-4">
                                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Datos de escritura</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Número Escritura" wire:model.live="datos.num_escritura" />
                                            <x-input label="Fecha de Escritura" wire:model.live="datos.fecha_escritura" type="date" />
                                            <x-input label="Nombre de Escribano" wire:model.live="datos.nombre_escribano" />
                                        </div>
                                    </div>

                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista previa -->
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-gray-50 dark:bg-gray-600 px-6 py-4 border-b dark:border-gray-600">
                                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>
                                            Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                    @endswitch

        @elseif ($modo === 'personalizado')
        <form wire:submit.prevent="guardarPersonalizado">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Editor -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-600 px-6 py-4 border-b dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <i class="fas fa-code mr-2 text-blue-500"></i>
                                Editor de resolución
                            </h2>
                            <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                                Usa [CAMPO] para variables
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <textarea
                            wire:model.live.debounce.300ms="plantilla"
                            rows="20"
                            class="w-full border dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg p-4 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                            placeholder="Escribe aquí el contenido de la resolución..."></textarea>

                        <div class="mt-6 space-y-4">
                            <div class="bg-gray-50 dark:bg-gray-600 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-list-ul mr-2 text-blue-500"></i>
                                    Variables disponibles:
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[NÚMERO]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[FECHA]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[NOMBRE]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[APELLIDO]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[DNI]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[CASA]</span>
                                    <span class="text-xs font-mono bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">[BARRIO]</span>
                                </div>
                            </div>

                            <x-button type="submit" class="w-full">
                                <i class="fas fa-save mr-2"></i> Guardar resolución
                            </x-button>
                        </div>
                    </div>
                </div>

                <!-- Vista previa del editor -->
                <div class="lg:sticky lg:top-4">
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden h-full">
                        <div class="bg-gray-50 dark:bg-gray-600 px-6 py-4 border-b dark:border-gray-600">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <i class="fas fa-eye mr-2 text-blue-500"></i>
                                Vista previa personalizada
                            </h2>
                        </div>
                        <div class="p-6 max-h-[calc(100vh-150px)] overflow-y-auto prose dark:prose-invert">
                            @if($plantilla)
                            {!! nl2br(e($plantilla)) !!}
                            @else
                            <div class="text-center py-10 text-gray-400">
                                <i class="fas fa-file-alt text-4xl mb-2"></i>
                                <p>El contenido aparecerá aquí</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>