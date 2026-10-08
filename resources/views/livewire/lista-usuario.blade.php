<div class="space-y-4" x-data="{ showOficina: false, showCrearUsuario: false }"
    x-on:open-modal.window="if ($event.detail[0] === 'modal-oficina') showOficina = true; if ($event.detail[0] === 'modal-crear-usuario') showCrearUsuario = true"
    x-on:close-modal.window="if ($event.detail[0] === 'modal-oficina') showOficina = false; if ($event.detail[0] === 'modal-crear-usuario') showCrearUsuario = false">
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
                    Usuario creado. Contraseña provisoria (se muestra una sola vez):
                    <span class="font-mono font-semibold select-all">{{ $claveGenerada }}</span>
                </p>
                <button type="button" wire:click="ocultarClave" aria-label="Cerrar aviso"
                    class="cursor-pointer text-ipv-ink/60 hover:text-ipv-ink dark:text-ipv-ink-dark/60">&times;</button>
            </div>
        @endif

        <div class="sirex-glass-card overflow-x-auto rounded-[14px] transition-opacity duration-150" wire:loading.delay.class="opacity-50 pointer-events-none" aria-live="polite">
            <table class="w-full table-fixed">
                <thead>
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Nombre</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Apellido</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Oficina</th>
                        @if (auth()->user()->permiso('lista_usuario_editar'))
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Permisos</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($usuarios as $usuario)
                        <tr wire:key="usr-{{ $usuario->id }}" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                            <td class="truncate px-4 py-2.5 text-sm text-ipv-ink dark:text-ipv-ink-dark">{{ $usuario->nombre }}</td>
                            <td class="truncate px-4 py-2.5 text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                {{ $usuario->apellido }}
                                @if ($legajosActivos !== null && ! in_array((string) $usuario->legajo, $legajosActivos, true))
                                    <span class="ml-1 rounded-full bg-ipv-magenta/12 px-2 py-0.5 text-[11px] font-semibold text-ipv-magenta dark:text-ipv-magenta-light"
                                        title="El legajo {{ $usuario->legajo ?: '(vacío)' }} no figura como empleado activo del IPV (baja, pasivo o error de carga). Revisar si debe seguir teniendo acceso.">
                                        No activo en IPV
                                    </span>
                                @endif
                            </td>
                            <td class="truncate px-4 py-2.5 text-sm text-ipv-ink dark:text-ipv-ink-dark">{{ $usuario->email }}</td>

                            {{-- Columna Oficina --}}
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full bg-ipv-blue/12 px-2.5 py-0.5 text-xs font-semibold text-ipv-blue dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light">
                                        {{ $oficinaPorUsuario[$usuario->id] ?? 'Sin asignar' }}
                                    </span>

                                    @if (auth()->user()->permiso('lista_usuario_editar'))
                                        <button type="button" wire:click="editarOficina({{ $usuario->id }})"
                                            class="inline-flex items-center gap-1 rounded-md bg-ipv-blue px-3 py-1 text-xs text-white hover:bg-ipv-blue-dark focus:outline-none focus:ring-2 focus:ring-ipv-blue/40">
                                            Editar oficina
                                        </button>
                                    @endif
                                </div>
                            </td>

                            @if (auth()->user()->permiso('lista_usuario_editar'))
                                {{-- Columna Permisos --}}
                                <td class="px-4 py-2.5">
                                    <button type="button" wire:click="seleccionarUsuario({{ $usuario->id }})" @click="$dispatch('open-modal', ['modal-permisos'])"
                                        class="rounded bg-ipv-blue px-3 py-1 text-xs text-white hover:bg-ipv-blue-dark">
                                        Asignar Permisos
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->permiso('lista_usuario_editar') ? 5 : 4 }}" class="py-4 text-center text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal para crear usuario --}}
        @if (auth()->user()->permiso('lista_usuario_editar'))
            <div x-show="showCrearUsuario" x-cloak x-on:keydown.escape.window="showCrearUsuario = false"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
                x-on:click.self="showCrearUsuario = false">
                <div class="w-full max-w-md rounded-2xl border border-ipv-blue/20 bg-white/95 p-4 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">Nuevo usuario</h3>

                        <div class="space-y-4">
                            {{-- Empleado activo del IPV: nombre, apellido y legajo salen de ahí. --}}
                            <div class="relative">
                                <label for="buscarEmpleado" class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Empleado</label>
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
                                        placeholder="Apellido o legajo"
                                        class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
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
                                @error('buscarEmpleado') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
                                @error('nuevoLegajo') <span class="mt-1 block text-xs text-ipv-magenta">Ese empleado ya tiene usuario.</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Email</label>
                                <input wire:model="nuevoEmail" type="email"
                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                                @error('nuevoEmail') <span class="mt-1 block text-xs text-ipv-magenta">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="nuevoOficinaId" class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Oficina</label>
                                <select id="nuevoOficinaId" wire:model="nuevoOficinaId"
                                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                                    <option value="">Seleccione oficina…</option>
                                    @foreach ($oficinas as $ofi)
                                        <option value="{{ $ofi['id'] }}">{{ $ofi['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('nuevoOficinaId')
                                    <p class="mt-1 text-sm text-ipv-magenta">{{ $message }}</p>
                                @enderror
                            </div>

                            <p class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">
                                El usuario se creará con una contraseña provisoria y deberá cambiarla al iniciar sesión por primera vez.
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showCrearUsuario = false"
                                class="rounded-md border border-ipv-blue/20 px-4 py-2 text-sm text-ipv-ink/80 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                                Cancelar
                            </button>

                            <button type="button" wire:click="crearUsuario" wire:loading.attr="disabled"
                                class="rounded-md bg-ipv-blue px-4 py-2 text-sm text-white hover:bg-ipv-blue-dark disabled:opacity-60">
                                <span wire:loading.remove>Crear usuario</span>
                                <span wire:loading>Creando…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal para editar oficina --}}
        <div x-show="showOficina" x-cloak x-on:keydown.escape.window="showOficina = false"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
            x-on:click.self="showOficina = false">
            <div class="w-full max-w-md rounded-2xl border border-ipv-blue/20 bg-white/95 p-4 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">Asignar Oficina</h3>

                    <div>
                        <label for="oficina_id" class="mb-1 block text-sm font-medium text-ipv-ink/80 dark:text-ipv-ink-dark/80">Oficina</label>
                        <select id="oficina_id" wire:model="oficina_id"
                            class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                            <option value="">Seleccione oficina…</option>
                            @foreach ($oficinas as $ofi)
                                <option value="{{ $ofi['id'] }}">{{ $ofi['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('oficina_id')
                            <p class="mt-1 text-sm text-ipv-magenta">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showOficina = false"
                            class="rounded-md border border-ipv-blue/20 px-4 py-2 text-sm text-ipv-ink/80 hover:bg-black/[0.03] dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                            Cancelar
                        </button>

                        <button type="button" wire:click="guardarOficina" wire:loading.attr="disabled"
                            class="rounded-md bg-ipv-blue px-4 py-2 text-sm text-white hover:bg-ipv-blue-dark disabled:opacity-60">
                            <span wire:loading.remove>Guardar</span>
                            <span wire:loading>Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
        @include('livewire.modal-permisos')
    @endif
</div>
