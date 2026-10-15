<div class="space-y-4" x-data="{ showOficina: false, showCrearUsuario: false, showCuenta: false }"
    x-on:open-modal.window="if ($event.detail[0] === 'modal-oficina') showOficina = true; if ($event.detail[0] === 'modal-crear-usuario') showCrearUsuario = true; if ($event.detail[0] === 'modal-cuenta') showCuenta = true"
    x-on:close-modal.window="if ($event.detail[0] === 'modal-oficina') showOficina = false; if ($event.detail[0] === 'modal-crear-usuario') showCrearUsuario = false; if ($event.detail[0] === 'modal-cuenta') showCuenta = false">
    @if (auth()->user()->permiso('lista_usuario_ver'))
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">
            Carga y Configuración de Usuarios
        </h1>

        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-1/3">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live="search" placeholder="Buscar usuarios..."
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2 pl-10 pr-4 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
            </div>

            <div class="flex gap-2">
                @if (auth()->user()->permiso('lista_usuario_editar'))
                    <button type="button" @click="showCrearUsuario = true"
                        class="cursor-pointer rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
                        Nuevo usuario
                    </button>
                @endif
            </div>
        </div>

        @if ($claveGenerada)
            <div class="flex items-start justify-between gap-3 rounded-lg border border-ipv-blue/30 bg-ipv-blue/10 px-4 py-3 text-sm text-ipv-ink dark:text-ipv-ink-dark">
                <p>
                    {{ $claveTitulo }} Contraseña provisoria (se muestra una sola vez):
                    <span class="font-mono font-semibold select-all">{{ $claveGenerada }}</span>
                </p>
                <button type="button" wire:click="ocultarClave" aria-label="Cerrar aviso"
                    class="cursor-pointer text-ipv-ink/60 hover:text-ipv-ink dark:text-ipv-ink-dark/60">&times;</button>
            </div>
        @endif

        @php($puedeEditar = auth()->user()->permiso('lista_usuario_editar'))
        @php($columnas = $puedeEditar ? 'lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1.2fr)_12rem_14rem]' : 'lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1.2fr)]')

        <div class="sirex-glass-card overflow-hidden rounded-[14px] transition-opacity duration-150" wire:loading.delay.class="opacity-50 pointer-events-none" aria-live="polite">
            <div class="hidden gap-4 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50 lg:grid {{ $columnas }}">
                <span>Usuario</span>
                <span>Oficina</span>
                @if ($puedeEditar)
                    <span>Accesos</span>
                    <span></span>
                @endif
            </div>

            <ul class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                @forelse ($usuarios as $usuario)
                    <li wire:key="usr-{{ $usuario->id }}" class="grid items-center gap-x-4 gap-y-3 px-4 py-3 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] {{ $columnas }}">
                        {{-- Usuario --}}
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ipv-blue/12 text-sm font-bold text-ipv-blue dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr((string) $usuario->nombre, 0, 1).mb_substr((string) $usuario->apellido, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $usuario->apellido }}, {{ $usuario->nombre }}
                                    @if ($legajosActivos !== null && ! in_array((string) $usuario->legajo, $legajosActivos, true))
                                        <span class="ml-1 rounded-full bg-ipv-magenta/12 px-2 py-0.5 align-middle text-[11px] font-semibold text-ipv-magenta dark:text-ipv-magenta-light"
                                            title="El legajo {{ $usuario->legajo ?: '(vacío)' }} no figura como empleado activo del IPV (baja, pasivo o error de carga). Revisar si debe seguir teniendo acceso.">
                                            No activo en IPV
                                        </span>
                                    @endif
                                </p>
                                <p class="truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $usuario->email }}</p>
                            </div>
                        </div>

                        {{-- Oficina --}}
                        <div class="min-w-0">
                            @if ($puedeEditar)
                                <button type="button" wire:click="editarOficina({{ $usuario->id }})" title="Editar oficina" aria-label="Editar oficina de {{ $usuario->nombre }} {{ $usuario->apellido }}"
                                    class="group inline-flex max-w-full items-center gap-1.5 rounded-full bg-ipv-blue/12 px-2.5 py-1 text-xs font-semibold text-ipv-blue transition hover:bg-ipv-blue/20 focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light">
                                    <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5m14 0h2M5 21H3m6-14h2m-2 4h2m2-4h2m-2 4h2m-4 10v-4h4v4" /></svg>
                                    <span class="truncate">{{ $oficinaPorUsuario[$usuario->id] ?? 'Sin asignar' }}</span>
                                    <svg class="size-3 shrink-0 opacity-50 transition group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                </button>
                            @else
                                <span class="inline-flex max-w-full items-center rounded-full bg-ipv-blue/12 px-2.5 py-1 text-xs font-semibold text-ipv-blue dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light">
                                    <span class="truncate">{{ $oficinaPorUsuario[$usuario->id] ?? 'Sin asignar' }}</span>
                                </span>
                            @endif
                        </div>

                        @if ($puedeEditar)
                            {{-- Accesos: un ícono por módulo (apagado = sin acceso, contorno = ver, relleno = editar) --}}
                            <div class="flex items-center gap-1.5" role="list" aria-label="Accesos">
                                @foreach ($accesos[$usuario->id] ?? [] as $acceso)
                                    <span role="listitem" title="{{ $acceso['titulo'] }}: {{ $acceso['etiqueta'] }}{{ $acceso['extras'] > 0 ? ' (+'.$acceso['extras'].' más)' : '' }}"
                                        @class([
                                            'grid size-8 place-items-center rounded-lg border transition',
                                            'border-transparent bg-black/[0.04] text-ipv-ink/25 dark:bg-white/[0.05] dark:text-ipv-ink-dark/25' => $acceso['nivel'] === 0,
                                            'border-ipv-blue/30 bg-ipv-blue/10 text-ipv-blue dark:border-ipv-blue-light/40 dark:bg-ipv-blue-light/15 dark:text-ipv-blue-light' => $acceso['nivel'] === 1,
                                            'border-ipv-blue bg-ipv-blue text-white dark:border-[#1a6fc4] dark:bg-[#1a6fc4]' => $acceso['nivel'] >= 2 || ($acceso['nivel'] === 1 && $acceso['clave'] === 'administracion'),
                                        ])>
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $acceso['icono'] }}" /></svg>
                                        <span class="sr-only">{{ $acceso['titulo'] }}: {{ $acceso['etiqueta'] }}</span>
                                    </span>
                                @endforeach
                            </div>

                            {{-- Acciones --}}
                            <div class="flex gap-2 lg:justify-end">
                                <button type="button" wire:click="seleccionarUsuario({{ $usuario->id }})" aria-label="Asignar permisos a {{ $usuario->nombre }} {{ $usuario->apellido }}"
                                    class="sirex-boton sirex-boton-primario sirex-boton-chico">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-3Z" /></svg>
                                    Permisos
                                </button>
                                <button type="button" wire:click="abrirCuenta({{ $usuario->id }})" aria-label="Cuenta de {{ $usuario->nombre }} {{ $usuario->apellido }}"
                                    class="sirex-boton sirex-boton-secundario sirex-boton-chico">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z" /></svg>
                                    Cuenta
                                </button>
                            </div>
                        @endif
                    </li>
                @empty
                    <li>
                        <x-vacio icono="persona">No se encontraron usuarios.</x-vacio>
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Modal para crear usuario --}}
        @if (auth()->user()->permiso('lista_usuario_editar'))
            <div x-show="showCrearUsuario" x-cloak x-on:keydown.escape.window="showCrearUsuario = false"
                class="sirex-modal-fondo" x-on:click.self="showCrearUsuario = false">
                <div class="sirex-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-nuevo-usuario">
                    <div class="sirex-modal-cabeza">
                        <div class="sirex-modal-icono">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="titulo-nuevo-usuario" class="sirex-modal-titulo">Nuevo usuario</h3>
                            <p class="sirex-modal-subtitulo">Se crea a partir de un empleado activo del IPV.</p>
                        </div>
                        <button type="button" @click="showCrearUsuario = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="sirex-modal-cuerpo">
                        <div class="space-y-4">
                            {{-- Empleado activo del IPV: nombre, apellido y legajo salen de ahí. --}}
                            <div class="relative">
                                <label for="buscarEmpleado" class="sirex-etiqueta">Empleado</label>
                                @if ($empleadoId)
                                    <div class="flex items-center justify-between rounded-lg border border-ipv-blue/30 bg-ipv-blue/[0.06] px-3 py-2">
                                        <div>
                                            <p class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $nuevoApellido }}, {{ $nuevoNombre }}</p>
                                            <p class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">Legajo {{ $nuevoLegajo }}</p>
                                        </div>
                                        <button type="button" wire:click="quitarEmpleado" class="text-xs font-medium text-ipv-blue hover:underline dark:text-ipv-blue-light">Cambiar</button>
                                    </div>
                                @else
                                    <input id="buscarEmpleado" wire:model.live.debounce.300ms="buscarEmpleado" type="search" autocomplete="off"
                                        placeholder="Apellido o legajo" class="sirex-campo">
                                    <div wire:loading.delay wire:target="buscarEmpleado" class="mt-2 space-y-2">
                                        <div class="sirex-skeleton h-8 rounded"></div>
                                        <div class="sirex-skeleton h-8 rounded"></div>
                                    </div>
                                    @if ($this->empleadosSugeridos->isNotEmpty())
                                        <ul wire:loading.remove wire:target="buscarEmpleado"
                                            class="mt-1 max-h-56 overflow-y-auto rounded-lg border border-ipv-blue/15 bg-white shadow-lg dark:border-white/10 dark:bg-[#1a2332]">
                                            @foreach ($this->empleadosSugeridos as $emp)
                                                <li wire:key="emp-{{ $emp->id }}">
                                                    <button type="button" wire:click="seleccionarEmpleado({{ $emp->id }})"
                                                        class="flex w-full items-center justify-between px-3 py-2 text-left text-sm transition hover:bg-ipv-blue hover:text-white">
                                                        <span>{{ $emp->nombre_completo }}</span>
                                                        <span class="text-xs opacity-70">Leg. {{ $emp->legajo }}</span>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif (mb_strlen(trim($buscarEmpleado)) >= 2)
                                        <p wire:loading.remove wire:target="buscarEmpleado" class="mt-1 text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados activos con ese dato.</p>
                                    @endif
                                @endif
                                @error('buscarEmpleado') <p class="sirex-error">{{ $message }}</p> @enderror
                                @error('nuevoLegajo') <p class="sirex-error">Ese empleado ya tiene usuario.</p> @enderror
                            </div>
                            <div>
                                <label class="sirex-etiqueta">Email</label>
                                <input wire:model="nuevoEmail" type="email" class="sirex-campo">
                                @error('nuevoEmail') <p class="sirex-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevoOficinaId" class="sirex-etiqueta">Oficina</label>
                                <select id="nuevoOficinaId" wire:model="nuevoOficinaId" class="sirex-campo">
                                    <option value="">Seleccione oficina…</option>
                                    @foreach ($oficinas as $ofi)
                                        <option value="{{ $ofi['id'] }}">{{ $ofi['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('nuevoOficinaId') <p class="sirex-error">{{ $message }}</p> @enderror
                            </div>

                            <p class="sirex-ayuda">
                                El usuario se creará con una contraseña provisoria y deberá cambiarla al iniciar sesión por primera vez.
                            </p>
                        </div>
                    </div>

                    <div class="sirex-modal-pie">
                        <button type="button" @click="showCrearUsuario = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>
                        <button type="button" wire:click="crearUsuario" wire:loading.attr="disabled" class="sirex-boton sirex-boton-primario">
                            <span wire:loading.remove>Crear usuario</span>
                            <span wire:loading>Creando…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal de cuenta: email y restablecer contraseña --}}
        @if (auth()->user()->permiso('lista_usuario_editar'))
            <div x-show="showCuenta" x-cloak x-on:keydown.escape.window="showCuenta = false"
                class="sirex-modal-fondo" x-on:click.self="showCuenta = false">
                <div class="sirex-modal sirex-modal-sm" role="dialog" aria-modal="true" aria-labelledby="titulo-cuenta">
                    <div class="sirex-modal-cabeza">
                        <div class="sirex-modal-icono">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="titulo-cuenta" class="sirex-modal-titulo">Cuenta de usuario</h3>
                            <p class="sirex-modal-subtitulo">{{ $cuentaNombre }}</p>
                        </div>
                        <button type="button" @click="showCuenta = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="sirex-modal-cuerpo">
                        <div class="space-y-5">
                            <div>
                                <label for="cuentaEmail" class="sirex-etiqueta">Email</label>
                                <div class="flex gap-2">
                                    <input id="cuentaEmail" wire:model="cuentaEmail" type="email" class="sirex-campo" autocomplete="off">
                                    <button type="button" wire:click="guardarEmail" wire:loading.attr="disabled" wire:target="guardarEmail"
                                        class="sirex-boton sirex-boton-primario shrink-0">
                                        <span wire:loading.remove wire:target="guardarEmail">Guardar</span>
                                        <span wire:loading wire:target="guardarEmail">Guardando…</span>
                                    </button>
                                </div>
                                @error('cuentaEmail') <p class="sirex-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="border-t border-ipv-blue/10 pt-4 dark:border-white/10" x-data="{ confirmar: false }">
                                <p class="sirex-etiqueta">Restablecer contraseña</p>
                                <p class="sirex-ayuda">
                                    Genera una contraseña provisoria nueva y cierra las sesiones abiertas del usuario. Al entrar tendrá que cambiarla.
                                </p>
                                <button type="button" x-show="! confirmar" @click="confirmar = true" class="sirex-boton sirex-boton-secundario mt-2">
                                    Restablecer contraseña
                                </button>
                                <div x-show="confirmar" x-cloak class="mt-2 flex items-center gap-2">
                                    <button type="button" wire:click="restablecerClave" wire:loading.attr="disabled" wire:target="restablecerClave"
                                        class="sirex-boton sirex-boton-peligro">
                                        <span wire:loading.remove wire:target="restablecerClave">Sí, restablecer</span>
                                        <span wire:loading wire:target="restablecerClave">Restableciendo…</span>
                                    </button>
                                    <button type="button" @click="confirmar = false" class="sirex-boton sirex-boton-secundario">No</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sirex-modal-pie">
                        <button type="button" @click="showCuenta = false" class="sirex-boton sirex-boton-secundario">Cerrar</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal para editar oficina --}}
        <div x-show="showOficina" x-cloak x-on:keydown.escape.window="showOficina = false"
            class="sirex-modal-fondo" x-on:click.self="showOficina = false">
            <div class="sirex-modal sirex-modal-sm" role="dialog" aria-modal="true" aria-labelledby="titulo-asignar-oficina">
                <div class="sirex-modal-cabeza">
                    <div class="sirex-modal-icono">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m6-14h2m-2 4h2m2-4h2m-2 4h2m-4 10v-4h4v4" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="titulo-asignar-oficina" class="sirex-modal-titulo">Asignar oficina</h3>
                        <p class="sirex-modal-subtitulo">Elegí la oficina del usuario.</p>
                    </div>
                    <button type="button" @click="showOficina = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="sirex-modal-cuerpo">
                    <div>
                        <label for="oficina_id" class="sirex-etiqueta">Oficina</label>
                        <select id="oficina_id" wire:model="oficina_id" class="sirex-campo">
                            <option value="">Seleccione oficina…</option>
                            @foreach ($oficinas as $ofi)
                                <option value="{{ $ofi['id'] }}">{{ $ofi['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('oficina_id') <p class="sirex-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="sirex-modal-pie">
                    <button type="button" @click="showOficina = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>
                    <button type="button" wire:click="guardarOficina" wire:loading.attr="disabled" class="sirex-boton sirex-boton-primario">
                        <span wire:loading.remove>Guardar</span>
                        <span wire:loading>Guardando…</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
        @if (auth()->user()->permiso('lista_usuario_editar'))
            @include('livewire.modal-permisos')
        @endif
    @endif
</div>
