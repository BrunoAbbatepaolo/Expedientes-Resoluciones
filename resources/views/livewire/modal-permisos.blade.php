<flux:modal name="modal-permisos" title="Gestionar Permisos de Usuario"
    description="Activa o desactiva los permisos correspondientes para este usuario." x-data="{
        saving: false,
        hasChanges: false,
        originalPermisos: {},
        init() {
            // Crear una copia limpia de los permisos originales
            this.originalPermisos = {};
            Object.keys($wire.permisos || {}).forEach(key => {
                this.originalPermisos[key] = Boolean($wire.permisos[key]);
            });
    
            this.$watch('$wire.permisos', () => {
                this.checkForChanges();
            });
        },
        checkForChanges() {
            let currentPermisos = {};
            Object.keys($wire.permisos || {}).forEach(key => {
                currentPermisos[key] = Boolean($wire.permisos[key]);
            });
    
            // Comparar boolean a boolean
            let changed = false;
            Object.keys(this.originalPermisos).forEach(key => {
                if (Boolean(this.originalPermisos[key]) !== Boolean(currentPermisos[key])) {
                    changed = true;
                }
            });
    
            this.hasChanges = changed;
        }
    }"
    class="backdrop-blur-md">

    <!-- Estilos personalizados para el backdrop -->
    <style>
        .flux-modal-backdrop {
            background-color: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(6px) saturate(150%) !important;
        }
    </style>

    <!-- Notificación de éxito -->
    @if (session()->has('message'))
        <div
            class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg dark:bg-green-900/50 dark:border-green-700 dark:text-green-200">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    <div class="p-6 space-y-6">
        <!-- Encabezado mejorado -->
        <div class="flex items-center gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="p-3 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Gestión de Permisos</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Configurar accesos para: <span class="font-medium text-blue-600 dark:text-blue-400">
                        {{ $usuarioSeleccionado->name ?? 'Usuario' }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Indicador de cambios - VERSION SIMPLIFICADA -->
        {{-- <div x-show="hasChanges"
             x-transition
             class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-700">
            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm text-amber-800 dark:text-amber-200">Tienes cambios sin guardar</span>
        </div> --}}

        <!-- Grid de permisos usando el componente -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $permisos = [
                    'expediente_ver' => [
                        'title' => 'Ver Expedientes',
                        'description' => 'Acceso de lectura a expedientes.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />',
                    ],
                    'expediente_editar' => [
                        'title' => 'Editar Expedientes',
                        'description' => 'Crear, modificar y eliminar expedientes.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />',
                    ],
                    'resolucion_ver' => [
                        'title' => 'Ver Resoluciones',
                        'description' => 'Acceso de lectura a resoluciones.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                    ],
                    'resolucion_editar' => [
                        'title' => 'Editar Resoluciones',
                        'description' => 'Crear, modificar y eliminar resoluciones.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                    ],
                    'lista_usuario_ver' => [
                        'title' => 'Ver Lista de Usuarios',
                        'description' => 'Acceso de lectura a usuarios.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
                    ],
                    'lista_usuario_editar' => [
                        'title' => 'Editar Lista de Usuarios',
                        'description' => 'Crear, modificar y eliminar usuarios.',
                        'icon' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15M19.5 12h-15" />',
                    ],
                ];
            @endphp

            @foreach ($permisos as $key => $config)
                <x-permiso-card :permiso="$key" :title="$config['title']" :description="$config['description']" :icon="$config['icon']" />
            @endforeach
        </div>

        <!-- Acciones rápidas -->
        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="seleccionarTodos"
                class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                Seleccionar Todo
            </button>
            <button type="button" wire:click="limpiarSeleccion"
                class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                Limpiar Todo
            </button>
            <button type="button" wire:click="aplicarPermisosSoloLectura"
                class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40 transition-colors">
                Solo Lectura
            </button>
        </div>
    </div>

    <!-- Footer del modal - SIMPLIFICADO -->
    <div class="flex items-center justify-end p-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex gap-3">
            <flux:button variant="subtle" wire:click="$dispatch('close-modal', 'modal-permisos')">
                Cancelar
            </flux:button>

            <flux:button class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded"
                wire:click="guardarPermisos" wire:loading.attr="disabled" wire:target="guardarPermisos">

                <span wire:loading.remove wire:target="guardarPermisos" class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Guardar Permisos</span>
                </span>

                <span wire:loading wire:target="guardarPermisos" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>Guardando...</span>
                </span>

            </flux:button>
        </div>
    </div>
</flux:modal>
