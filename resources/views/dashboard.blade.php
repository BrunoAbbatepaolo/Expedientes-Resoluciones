<x-layouts.app :title="__('Dashboard')">
    <div class="w-full">
        <!-- Contenedor fluido con padding responsivo -->
        <div class="container mx-auto max-w-7xl px-3 sm:px-4 lg:px-6">
            <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl min-w-0">

                <!-- Grid superior responsiva -->
                <div class="grid auto-rows-min gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

                    <!-- Bienvenida mejorada -->
                    <div
                        class="relative md:aspect-video aspect-[4/3] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg min-w-0">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                            <div class="mb-2">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-[clamp(1rem,2.5vw,1.25rem)] leading-tight">
                                    ¡Hola, {{ Auth::user()->nombre }}!
                                </h2>
                                <p class="text-blue-100 mt-1 text-[clamp(0.8rem,2vw,0.95rem)]">
                                    {{ Auth::user()->oficinaUsuario->nombre ?? 'Sistema Administrativo' }}
                                </p>
                            </div>

                            <!-- Hora local del navegador (precisa al hacer zoom y en cualquier tz) -->
                            <div x-data="{
                                now: new Date(),
                                pad(n) { return String(n).padStart(2, '0') },
                                fmt() {
                                    const d = this.now;
                                    return `${this.pad(d.getDate())}/${this.pad(d.getMonth()+1)}/${d.getFullYear()} - ${this.pad(d.getHours())}:${this.pad(d.getMinutes())}`;
                                }
                            }" x-init="setInterval(() => now = new Date(), 1000)"
                                class="text-xs text-blue-200 bg-white/10 px-3 py-1 rounded-full">
                                <span x-text="fmt()"></span>
                            </div>

                            <!-- Fallback del servidor: comentar/usar si preferís server-time -->
                            <!-- <div class="text-xs text-blue-200 bg-white/10 px-3 py-1 rounded-full">
                                {{ now()->timezone(config('app.timezone'))->format('d/m/Y - H:i') }}
                            </div> -->
                        </div>
                    </div>

                    <!-- Próximos Cumpleaños -->
                    <div
                        class="relative md:aspect-video aspect-[4/3] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-pink-50 to-rose-100 dark:from-pink-900/20 dark:to-rose-800/20 min-w-0">
                        <div class="absolute inset-0 p-4">
                            <div class="flex items-center mb-3 min-w-0">
                                <svg class="w-6 h-6 text-pink-600 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <h3
                                    class="text-[clamp(1rem,2.5vw,1.125rem)] font-semibold text-pink-800 dark:text-pink-200 truncate">
                                    Próximos Cumpleaños
                                </h3>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div
                                    class="flex items-center justify-between p-2 bg-white/50 dark:bg-pink-800/20 rounded-lg">
                                    <div class="flex items-center min-w-0">
                                        <div
                                            class="w-8 h-8 bg-pink-200 dark:bg-pink-700 rounded-full flex items-center justify-center mr-2 shrink-0">
                                            <span class="text-sm font-bold text-pink-800 dark:text-pink-200">15</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium truncate">María García</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 truncate">Recursos
                                                Humanos</div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs bg-pink-200 dark:bg-pink-700 text-pink-800 dark:text-pink-200 px-2 py-1 rounded">3
                                        días</span>
                                </div>

                                <div
                                    class="flex items-center justify-between p-2 bg-white/50 dark:bg-pink-800/20 rounded-lg">
                                    <div class="flex items-center min-w-0">
                                        <div
                                            class="w-8 h-8 bg-pink-200 dark:bg-pink-700 rounded-full flex items-center justify-center mr-2 shrink-0">
                                            <span class="text-sm font-bold text-pink-800 dark:text-pink-200">22</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium truncate">Juan Pérez</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 truncate">Contaduría
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs bg-pink-200 dark:bg-pink-700 text-pink-800 dark:text-pink-200 px-2 py-1 rounded">10
                                        días</span>
                                </div>
                            </div>

                            <div class="absolute bottom-3 left-4 right-4">
                                <div
                                    class="bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg p-2">
                                    <div class="flex items-center text-yellow-800 dark:text-yellow-200">
                                        <svg class="w-4 h-4 mr-1 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                                            aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs font-medium">Función en mantenimiento</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Estadísticas -->
                    <div
                        class="relative md:aspect-video aspect-[4/3] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 min-w-0">
                        <div class="absolute inset-0 p-4">
                            <h3
                                class="mb-4 text-gray-800 dark:text-gray-200 font-semibold text-[clamp(1rem,2.5vw,1.125rem)]">
                                Resumen General</h3>
                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="text-xl font-bold text-blue-600">{{ $expedientesActivos ?? 0 }}</div>
                                    <div class="text-xs text-blue-700 dark:text-blue-300">Expedientes</div>
                                </div>
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="text-xl font-bold text-blue-600">{{ $resolucionesPendientes ?? 0 }}
                                    </div>
                                    <div class="text-xs text-blue-700 dark:text-blue-300">Resoluciones</div>
                                </div>
                                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                                    <div class="text-xl font-bold text-orange-600">{{ $faltasMes ?? 0 }}</div>
                                    <div class="text-xs text-orange-700 dark:text-orange-300">Faltas/Mes</div>
                                </div>
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="text-xl font-bold text-blue-600">{{ $informesPendientes ?? 0 }}</div>
                                    <div class="text-xs text-blue-700 dark:text-blue-300">Informes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección inferior grande -->
                <div
                    class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 min-w-0">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-6">
                            <h3
                                class="text-[clamp(1.05rem,2.5vw,1.25rem)] font-semibold text-gray-800 dark:text-gray-200">
                                Accesos Rápidos</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sistema v2.1</span>
                        </div>

                        <!-- Grid de accesos responsiva -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                            <!-- Expedientes -->
                            <a href="{{ route('expedientes') }}"
                                class="group p-4 bg-blue-50 dark:bg-blue-900/10 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-all duration-200 border border-blue-200 dark:border-blue-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-blue-200 dark:bg-blue-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 truncate">Expedientes</h4>
                                    <p class="text-sm text-blue-600 dark:text-blue-300 mt-1 truncate">Gestionar
                                        expedientes</p>
                                </div>
                            </a>

                            <!-- Resoluciones -->
                            <a href="{{ route('resoluciones') }}"
                                class="group p-4 bg-green-50 dark:bg-green-900/10 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/20 transition-all duration-200 border border-green-200 dark:border-green-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-green-200 dark:bg-green-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-green-800 dark:text-green-200 truncate">Resoluciones
                                    </h4>
                                    <p class="text-sm text-green-600 dark:text-green-300 mt-1 truncate">Gestionar
                                        resoluciones</p>
                                </div>
                            </a>

                            <!-- Usuarios -->
                            <a href="{{ route('listausuarios') }}"
                                class="group p-4 bg-purple-50 dark:bg-purple-900/10 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900/20 transition-all duration-200 border border-purple-200 dark:border-purple-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-purple-200 dark:bg-purple-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path
                                                d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-purple-800 dark:text-purple-200 truncate">Usuarios
                                    </h4>
                                    <p class="text-sm text-purple-600 dark:text-purple-300 mt-1 truncate">Administrar
                                        usuarios</p>
                                </div>
                            </a>

                            <!-- Faltas -->
                            <a href="#"
                                class="group p-4 bg-red-50 dark:bg-red-900/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/20 transition-all duration-200 border border-red-200 dark:border-red-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-red-200 dark:bg-red-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 truncate">Faltas</h4>
                                    <p class="text-sm text-red-600 dark:text-red-300 mt-1 truncate">Control de
                                        asistencia</p>
                                </div>
                            </a>

                            <!-- Informes -->
                            <a href="#"
                                class="group p-4 bg-orange-50 dark:bg-orange-900/10 rounded-xl hover:bg-orange-100 dark:hover:bg-orange-900/20 transition-all duration-200 border border-orange-200 dark:border-orange-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-orange-200 dark:bg-orange-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm6 2a1 1 0 000 2h2a1 1 0 100-2H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-orange-800 dark:text-orange-200 truncate">Informes
                                    </h4>
                                    <p class="text-sm text-orange-600 dark:text-orange-300 mt-1 truncate">Reportes y
                                        estadísticas</p>
                                </div>
                            </a>

                            <!-- Configuración -->
                            <a href="{{ route('settings.profile') }}"
                                class="group p-4 bg-gray-50 dark:bg-gray-900/10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900/20 transition-all duration-200 border border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 truncate">Configuración
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 truncate">Ajustes del
                                        sistema</p>
                                </div>
                            </a>

                            <!-- Ayuda -->
                            <a href="#"
                                class="group p-4 bg-indigo-50 dark:bg-indigo-900/10 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/20 transition-all duration-200 border border-indigo-200 dark:border-indigo-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-indigo-200 dark:bg-indigo-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 truncate">Ayuda</h4>
                                    <p class="text-sm text-indigo-600 dark:text-indigo-300 mt-1 truncate">Soporte y
                                        documentación</p>
                                </div>
                            </a>

                            <!-- Manual -->
                            <a href="#"
                                class="group p-4 bg-teal-50 dark:bg-teal-900/10 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-900/20 transition-all duration-200 border border-teal-200 dark:border-teal-800">
                                <div class="flex flex-col items-center text-center min-w-0">
                                    <div
                                        class="w-12 h-12 bg-teal-200 dark:bg-teal-800 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-teal-600 dark:text-teal-300" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path
                                                d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-teal-800 dark:text-teal-200 truncate">Manual</h4>
                                    <p class="text-sm text-teal-600 dark:text-teal-300 mt-1 truncate">Guías de usuario
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
