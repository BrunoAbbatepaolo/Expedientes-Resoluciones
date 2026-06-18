<div class="p-6 dark:bg-zinc-800 dark:text-zinc-100">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl md:text-3xl font-bold mb-6 text-zinc-800 dark:text-zinc-200">
            Crear resolución: <span class="text-blue-600 dark:text-blue-400">{{ $this->displayName }}</span>
        </h1>

        @if ($modo === '')
        <div class="mb-4">
            <x-button onclick="window.location.href='/resoluciones/elegir'" size="sm" icon="arrow-uturn-left">
                Volver
            </x-button>
        </div>
        <div class="bg-white dark:bg-zinc-700 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-zinc-700 dark:text-zinc-300">Seleccione el modo de creación</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <x-button wire:click="usarCompleto" class="flex-1 py-3 justify-center" icon="document-text">
                    Usar modelo completo
                </x-button>
                <x-button wire:click="verPlantillaCompleta" color="zinc" class="flex-1 py-3 justify-center" icon="document-duplicate">
                    Plantilla completa
                </x-button>
            </div>
        </div>

        @elseif ($modo === 'completo')
            <div class="mb-4">
                <x-button wire:click="$set('modo', '')" size="sm" icon="arrow-uturn-left">
                    Volver
                </x-button>
            </div>
            @switch($tipo)
                @case('Cancelaciones')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                            <x-input label="Nombre Cotitular" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de escritura</h3>
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
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                @case('Resciciones')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                                        <x-input label="Casa/Lote" wire:model.live="datos.lote" />
                                    </div>
                                    <x-input label="Nombre del Emprendimiento" wire:model.live="datos.nombre_emprendimiento" />
                                    <x-input label="Departamento" wire:model.live="datos.departamento" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Fecha Dictamen" wire:model.live="datos.fecha_dictamen" type="date" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de deuda</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Monto de Deuda" wire:model.live="datos.monto_deuda" type="number" step="0.01" />
                                            <x-input label="Fecha Deuda" wire:model.live="datos.fecha_deuda" type="date" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                            <x-input label="Nombre Cotitular" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Resolución de Adjudicación</h3>
                                        <x-input label="Número de Resolución de Adjudicación" wire:model.live="datos.num_res_adjudicacion" />
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                @case('Transferencias')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                                        <x-input label="Casa/Lote" wire:model.live="datos.lote" />
                                    </div>
                                    <x-input label="Nombre del Plan" wire:model.live="datos.nombre_plan" />
                                    <x-input label="Número de Resolución de Adjudicación" wire:model.live="datos.num_res_adjudicacion" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja Boleto" wire:model.live="datos.num_foja_boleto" />
                                        <x-input label="Foja Plan" wire:model.live="datos.num_foja_plan" />
                                        <x-input label="Foja Recursos" wire:model.live="datos.num_foja_recursos" />
                                        <x-input label="Foja Promoción Social" wire:model.live="datos.num_foja_promocion" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Foja Ratificación" wire:model.live="datos.num_foja_ratificacion" />
                                        <x-input label="Foja Regularización" wire:model.live="datos.num_foja_regularizacion" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                        <x-input label="Fecha Dictamen" wire:model.live="datos.fecha_dictamen" type="date" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Titulares anteriores</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular Anterior" wire:model.live="datos.nombre_titular_anterior" />
                                            <x-input label="DNI Titular Anterior" wire:model.live="datos.dni_titular_anterior" />
                                            <x-input label="Nombre Cotitular Anterior" wire:model.live="datos.nombre_cotitular_anterior" />
                                            <x-input label="DNI Cotitular Anterior" wire:model.live="datos.dni_cotitular_anterior" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Nuevos titulares</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Nuevo Titular" wire:model.live="datos.nombre_titular_nuevo" />
                                            <x-input label="DNI Nuevo Titular" wire:model.live="datos.dni_titular_nuevo" />
                                            <x-input label="Fecha de Nacimiento" wire:model.live="datos.fecha_nacimiento_titular" type="date" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <x-input label="Nombre Nuevo Cotitular" wire:model.live="datos.nombre_cotitular_nuevo" />
                                            <x-input label="DNI Nuevo Cotitular" wire:model.live="datos.dni_cotitular_nuevo" />
                                            <x-input label="Fecha de Nacimiento" wire:model.live="datos.fecha_nacimiento_cotitular" type="date" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del instrumento</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Fecha del Instrumento" wire:model.live="datos.fecha_instrumento" type="date" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Cantidad de Cuotas" wire:model.live="datos.num_cuotas" type="number" />
                                            <x-input label="Monto de Cuota" wire:model.live="datos.monto_cuota" type="number" step="0.01" />
                                            <x-input label="Año de las Cuotas" wire:model.live="datos.año_cuotas" type="number" placeholder="Ej: 2026" />
                                            <x-input label="Fecha Primer Pago" wire:model.live="datos.fecha_primer_pago" type="date" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                @case('Transferencia-Cancelacion')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Identificación de la unidad</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Manzana" wire:model.live="datos.manzana" />
                                            <x-input label="Lote" wire:model.live="datos.lote" />
                                            <x-input label="Unidad" wire:model.live="datos.unidad" />
                                            <x-input label="Sector" wire:model.live="datos.sector" />
                                        </div>
                                        <x-input label="Nombre del Plan" wire:model.live="datos.nombre_plan" class="mt-4" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja Instrumento Privado" wire:model.live="datos.num_foja_boleto" />
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Fecha Cancelación" wire:model.live="datos.fecha_cancelacion" type="date" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                        <x-input label="Foja Promoción Social" wire:model.live="datos.num_foja_promocion" />
                                    </div>
                                    <x-input label="Número Resolución de Adjudicación" wire:model.live="datos.num_res_adjudicacion" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Res. Reglamentación Cancelación" wire:model.live="datos.num_res_reglamentacion" placeholder="185/2017" />
                                        <x-input label="Res. Modificatoria" wire:model.live="datos.num_res_modificatoria" placeholder="779/2017" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Titulares anteriores</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular Anterior" wire:model.live="datos.nombre_titular_anterior" />
                                            <x-input label="DNI Titular Anterior" wire:model.live="datos.dni_titular_anterior" />
                                            <x-input label="Nombre Cotitular Anterior" wire:model.live="datos.nombre_cotitular_anterior" />
                                            <x-input label="DNI Cotitular Anterior" wire:model.live="datos.dni_cotitular_anterior" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Nuevos titulares</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Nuevo Titular" wire:model.live="datos.nombre_titular_nuevo" />
                                            <x-input label="DNI Nuevo Titular" wire:model.live="datos.dni_titular_nuevo" />
                                            <x-input label="Estado Civil" wire:model.live="datos.estado_civil_titular_nuevo" placeholder="divorciado, soltero, etc." />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <x-input label="Nombre Nuevo Cotitular" wire:model.live="datos.nombre_cotitular_nuevo" />
                                            <x-input label="DNI Nuevo Cotitular" wire:model.live="datos.dni_cotitular_nuevo" />
                                            <x-input label="Estado Civil" wire:model.live="datos.estado_civil_cotitular_nuevo" placeholder="divorciada, soltera, etc." />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del instrumento</h3>
                                        <x-input label="Fecha del Instrumento Privado" wire:model.live="datos.fecha_instrumento" type="date" />
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
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
                @case('Rectificacion')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Manzana" wire:model.live="datos.manzana" />
                                        <x-input label="Casa/Lote" wire:model.live="datos.lote" />
                                    </div>
                                    <x-input label="Nombre del Plan" wire:model.live="datos.nombre_plan" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Res. Reglamentación" wire:model.live="datos.res_reglamentacion" placeholder="185/2017" />
                                        <x-input label="Res. Modificatoria" wire:model.live="datos.res_modificatoria" placeholder="779/2017" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Rectificación de Resolución</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="N° Resolución a Rectificar" wire:model.live="datos.num_res_adjudicacion" />
                                            <x-input label="Fecha Resolución" wire:model.live="datos.fecha_res_adjudicacion" type="date" />
                                            <x-input label="Orden" wire:model.live="datos.orden" placeholder="Ej: 8" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja DNI" wire:model.live="datos.num_foja_dni" />
                                        <x-input label="Foja Resolución Original" wire:model.live="datos.num_foja_resolucion" />
                                        <x-input label="Foja Informe Recursos" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Fecha Cancelación" wire:model.live="datos.fecha_cancelacion" type="date" />
                                        <x-input label="Foja DARRD Conf." wire:model.live="datos.num_foja_darrd_conf" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del "DONDE DICE"</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Cantidad Viviendas" wire:model.live="datos.cantidad_viviendas" placeholder="Ej: 45" />
                                            <x-input label="Cantidad en Letras" wire:model.live="datos.cantidad_letras" placeholder="Ej: Cuarenta y Cinco" />
                                        </div>
                                        <x-input label="Texto DONDE DICE (opcional)" wire:model.live="datos.texto_donde_dice" placeholder="Texto alternativo completo" class="mt-2" />
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>
                                            Vista previa del documento (2 páginas)
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
                @case('AplicarPagos')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Manzana" wire:model.live="datos.manzana" />
                                        <x-input label="Lote" wire:model.live="datos.lote" />
                                        <x-input label="Código de Pago" wire:model.live="datos.codigo_pago" />
                                    </div>
                                    <x-input label="Nombre del Barrio" wire:model.live="datos.nombre_barrio" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <x-input label="Nombre Cotitular (opcional)" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular (opcional)" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Cantidad de Cuotas" wire:model.live="datos.num_cuotas" type="number" />
                                            <x-input label="Monto de Cuota" wire:model.live="datos.monto_cuota" type="number" step="0.01" />
                                            <x-input label="Año de las Cuotas" wire:model.live="datos.año_cuotas" type="number" placeholder="Ej: 2026" />
                                            <x-input label="Fecha Primer Pago" wire:model.live="datos.fecha_primer_pago" type="date" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-8 lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>
                                            Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('ReconocimientoCuotaPagadaDosVeces')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Manzana" wire:model.live="datos.manzana" />
                                        <x-input label="Lote" wire:model.live="datos.lote" />
                                        <x-input label="Código de Pago" wire:model.live="datos.codigo_pago" />
                                    </div>
                                    <x-input label="Nombre del Barrio" wire:model.live="datos.nombre_barrio" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <x-input label="Nombre Cotitular (opcional)" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular (opcional)" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Cantidad de Cuotas" wire:model.live="datos.num_cuotas" type="number" />
                                            <x-input label="Monto de Cuota" wire:model.live="datos.monto_cuota" type="number" step="0.01" />
                                            <x-input label="Año de las Cuotas" wire:model.live="datos.año_cuotas" type="number" placeholder="Ej: 2026" />
                                            <x-input label="Fecha Primer Pago" wire:model.live="datos.fecha_primer_pago" type="date" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-8 lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>
                                            Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                @case('ReconocimientoCuotaPagadaNoCargada')
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4 bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden">
                                <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i>
                                        Datos de la resolución
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Fecha de Resolución" wire:model.live="datos.fecha_res" type="date" />
                                        <x-input label="Número de Expediente" wire:model.live="datos.num_exp" />
                                        <x-input label="Número de Resolución" wire:model.live="datos.num_res" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input label="Manzana" wire:model.live="datos.manzana" />
                                        <x-input label="Lote" wire:model.live="datos.lote" />
                                        <x-input label="Código de Pago" wire:model.live="datos.codigo_pago" />
                                    </div>
                                    <x-input label="Nombre del Barrio" wire:model.live="datos.nombre_barrio" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                        <x-input label="Foja de Solicitud" wire:model.live="datos.num_foja_solicitud" />
                                        <x-input label="Foja de Informe" wire:model.live="datos.num_foja_informe" />
                                        <x-input label="Foja DARRD" wire:model.live="datos.num_foja_darrd" />
                                        <x-input label="Foja Dictamen" wire:model.live="datos.num_foja_dictamen" />
                                        <x-input label="Número Dictamen" wire:model.live="datos.num_dictamen" />
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos del titular</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Nombre Titular" wire:model.live="datos.nombre_titular" />
                                            <x-input label="DNI Titular" wire:model.live="datos.dni_titular" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <x-input label="Nombre Cotitular (opcional)" wire:model.live="datos.nombre_cotitular" />
                                            <x-input label="DNI Cotitular (opcional)" wire:model.live="datos.dni_cotitular" />
                                        </div>
                                    </div>
                                    <div class="border-t dark:border-zinc-600 pt-4">
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300 mb-3">Datos de las cuotas</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-input label="Cantidad de Cuotas" wire:model.live="datos.num_cuotas" type="number" />
                                            <x-input label="Monto de Cuota" wire:model.live="datos.monto_cuota" type="number" step="0.01" />
                                            <x-input label="Año de las Cuotas" wire:model.live="datos.año_cuotas" type="number" placeholder="Ej: 2026" />
                                            <x-input label="Fecha Primer Pago" wire:model.live="datos.fecha_primer_pago" type="date" />
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <x-button type="submit" class="w-full">
                                            <i class="fas fa-save mr-2"></i> Guardar resolución
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-8 lg:sticky lg:top-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow overflow-hidden h-full">
                                    <div class="bg-zinc-50 dark:bg-zinc-600 px-6 py-4 border-b dark:border-zinc-600">
                                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>
                                            Vista previa del documento
                                        </h2>
                                    </div>
                                    <div class="p-4 max-h-[calc(100vh-150px)] overflow-auto">
                                        {!! $this->getVistaPrevia() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @break
                    <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg p-6">
                        El tipo <strong>{{ $tipo }}</strong> no tiene plantilla de personalización disponible.
                        <x-button wire:click="$set('modo', '')" class="mt-4">Volver</x-button>
                    </div>
                @endswitch

        @elseif ($modo === 'personalizado')
        <div class="min-h-screen bg-zinc-100 dark:bg-zinc-900 -mx-6 -my-6 p-6">
            <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm mb-6 -mx-6 px-6 py-3 flex items-center justify-between rounded-lg">
                <div class="flex items-center gap-4">
                    <x-button wire:click="usarCompleto" size="sm" icon="arrow-uturn-left">
                        Volver al formulario
                    </x-button>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        Editá el documento como si fuera un Word
                    </span>
                </div>
                <x-button wire:click="guardarPersonalizado">
                    Guardar resolución
                </x-button>
            </div>

            <div class="max-w-3xl mx-auto bg-white dark:bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
                <div wire:ignore
                     x-data="quillInit()"
                     x-init="init()"
                     x-ref="quill-1"
                     class="[&_.ql-toolbar.ql-snow]:!border-zinc-200 dark:[&_.ql-toolbar.ql-snow]:!border-zinc-700 dark:[&_.ql-snow_.ql-toolbar]:!bg-zinc-800 dark:[&_.ql-snow_.ql-stroke]:!stroke-zinc-400 dark:[&_.ql-snow_.ql-fill]:!fill-zinc-400 dark:[&_.ql-snow_.ql-picker]:!text-zinc-400 dark:[&_.ql-snow_.ql-picker-options]:!bg-zinc-700 [&_.ql-editor]:!font-serif [&_.ql-editor]:!text-base [&_.ql-editor]:!leading-relaxed [&_.ql-editor]:!p-12 [&_.ql-editor]:!text-justify [&_.ql-container]:!border-none">
                    <div data-quill-editor></div>
                </div>
            </div>

            <input type="hidden" name="plantilla" id="plantilla-input" wire:model.live="plantilla" />
        </div>

        @elseif ($modo === 'plantilla')
        <div class="min-h-screen bg-zinc-100 dark:bg-zinc-900 -mx-6 -my-6 p-6" x-data="{ mostrarModalPdf: false, pdfIndex: null }">
            <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm mb-6 -mx-6 px-6 py-3 flex items-center justify-between rounded-lg">
                <div class="flex items-center gap-4">
                    <x-button wire:click="$set('modo', '')" size="sm" icon="arrow-uturn-left">
                        Volver
                    </x-button>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        Editá el documento haciendo clic en el texto
                    </span>
                </div>
                <x-button onclick="@this.guardarPlantilla()">
                    Guardar resolución
                </x-button>
            </div>

@if($tipo === 'AplicarPagos' || $tipo === 'ReconocimientoCuotaPagadaDosVeces' || $tipo === 'ReconocimientoCuotaPagadaNoCargada')
            <div class="min-w-[1200px] mx-auto bg-white dark:bg-zinc-700 rounded-lg shadow-lg overflow-auto">
                <div class="p-2">
                    {!! $this->getVistaPrevia() !!}
                </div>
            </div>

            <!-- Sección de PDFs -->
            <div class="mt-6 px-4">
                <div class="flex items-center gap-4 mb-4">
                    <label class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg cursor-pointer transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar PDF
                        <input type="file" wire:model="tempArchivos" multiple accept=".pdf" class="hidden" />
                    </label>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ count($archivosPDF) }} archivo(s) cargado(s)
                    </span>
                </div>

                @if(count($archivosPDF) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($archivosPDF as $index => $archivo)
                            <div @click="mostrarModalPdf = true; pdfIndex = {{ $index }}" class="bg-white dark:bg-zinc-700 rounded-lg shadow p-4 flex items-center justify-between border border-zinc-200 dark:border-zinc-600 hover:shadow-md hover:border-blue-500 dark:hover:border-blue-400 transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="bg-red-100 dark:bg-red-900 rounded p-2 flex-shrink-0 group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300 truncate" title="{{ $archivo->getClientOriginalName() }}">
                                        {{ $archivo->getClientOriginalName() }}
                                    </span>
                                </div>
                                <button type="button" wire:click.stop="removeArchivo({{ $index }})" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Modal para ver PDF -->
            <div x-show="mostrarModalPdf" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                 style="display: none;">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black/50" @click="mostrarModalPdf = false"></div>
                
                <!-- Modal content -->
                <div class="relative bg-white dark:bg-zinc-800 rounded-lg shadow-xl max-w-4xl w-full h-[80vh] flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-600">
                        <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">
                            Documento PDF
                        </h3>
                        <button @click="mostrarModalPdf = false" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 bg-zinc-100 dark:bg-zinc-900 rounded-b-lg overflow-hidden">
                        <iframe 
                            :src="'/resoluciones/descargar-pdf/' + pdfIndex" 
                            class="w-full h-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-4xl mx-auto bg-white dark:bg-zinc-700 rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    {!! $this->plantilla !!}
                </div>
            </div>
        @endif
        </div>
    @endif
    </div>
</div>