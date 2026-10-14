<div class="space-y-4" x-data="{ showEditar: false, showBorrar: false }"
    x-on:modal-close.window="
        if ($event.detail.name === 'edit-profile') showEditar = false;
        if ($event.detail.name === 'delete-profile') showBorrar = false;
    ">
    @if (auth()->user()->permiso('resolucion_ver'))
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">
            Sistema de Carga de Resoluciones
        </h1>

        <div class="flex items-center gap-2">
            <input type="text" placeholder="Búsqueda de resoluciones"
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-4 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
            @if (auth()->user()->permiso('resolucion_editar'))
                <a wire:navigate href="{{ route('resoluciones.elegir') }}"
                    class="flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Generar nueva resolución
                </a>
            @endif
        </div>

        <div class="sirex-glass-card overflow-x-auto rounded-[14px] transition-opacity duration-150" wire:loading.delay.class="opacity-50 pointer-events-none" aria-live="polite">
            <table class="w-full min-w-[640px] table-fixed">
                <thead>
                    <tr>
                        <th class="w-[140px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Nº de Expediente</th>
                        <th class="w-[140px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Nº de Resolución</th>
                        <th class="w-[120px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Fecha de Resolución</th>
                        <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Barrio</th>
                        <th class="w-[80px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Casa</th>
                        <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Archivo PDF</th>
                        @if (auth()->user()->permiso('resolucion_editar'))
                            <th class="w-[120px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                    @forelse ($resolucionesConExpediente as $resolucion)
                        <tr wire:key="res-{{ $resolucion->id }}" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                            <td class="truncate px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                @if ($resolucion->expediente && auth()->user()->permiso('expediente_ver'))
                                    <a href="{{ route('expedientes.ipv.ficha', $resolucion->expediente->id) }}" wire:navigate.hover
                                        class="font-mono font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light" title="Ver el expediente">{{ $resolucion->expediente->numero }}</a>
                                @elseif ($resolucion->expediente)
                                    {{ $resolucion->expediente->numero }}
                                @else
                                    <span class="italic text-ipv-ink/40 dark:text-ipv-ink-dark/40">Sin expediente</span>
                                @endif
                            </td>
                            <td class="truncate px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                {{ $resolucion->numero_resolucion }}
                            </td>
                            <td class="truncate px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                {{ $resolucion->fecha }}
                            </td>
                            <td class="truncate px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                {{ $resolucion->cod_barrio }}
                            </td>
                            <td class="truncate px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                {{ $resolucion->cod_casa }}
                            </td>
                            <td class="truncate px-4 py-2.5 text-center">
                                @php
                                    // Archivos adjuntados al crear (resolucion_archivos) o, si no hay,
                                    // el PDF cargado desde "Editar" (columna pdf).
                                    $urlPdf = $resolucion->archivos->isNotEmpty()
                                        ? route('resoluciones.archivo', $resolucion->archivos->first())
                                        : ($resolucion->pdf ? route('resoluciones.pdf', $resolucion) : null);
                                @endphp
                                @if ($urlPdf)
                                    <a href="{{ $urlPdf }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1 text-ipv-magenta transition-colors duration-200 hover:opacity-80">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ver PDF{{ $resolucion->archivos->count() > 1 ? ' ('.$resolucion->archivos->count().')' : '' }}
                                    </a>
                                @else
                                    <span class="text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">—</span>
                                @endif
                            </td>

                            @if (auth()->user()->permiso('resolucion_editar'))
                                <td class="px-4 py-2.5 text-center">
                                    <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                        <button @click="open = !open"
                                            class="cursor-pointer rounded-xl bg-black/[0.04] p-2 transition-colors duration-200 hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.12]">
                                            <svg class="size-5 text-ipv-ink/70 dark:text-ipv-ink-dark/70" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 3c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 14c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 z-50 mt-2 w-48 origin-top-right overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-ipv-blue/15 dark:bg-slate-800 dark:ring-white/10">
                                            <button wire:click="cargarResolucion({{ $resolucion->id }})" @click="open = false; showEditar = true"
                                                class="flex w-full cursor-pointer items-center gap-2 px-4 py-3 text-left text-sm text-ipv-ink/80 transition-all duration-200 hover:bg-ipv-blue hover:text-white dark:text-ipv-ink-dark/85">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Modificar resolución
                                            </button>
                                            <button wire:click="confirmarBorrado({{ $resolucion->id }})" @click="open = false; showBorrar = true"
                                                class="flex w-full cursor-pointer items-center gap-2 px-4 py-3 text-left text-sm text-ipv-magenta transition-all duration-200 hover:bg-ipv-magenta hover:text-white">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Eliminar resolución
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-vacio icono="buscar">No se encontraron resoluciones</x-vacio>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- modal para editar resoluciones -->
        <div x-show="showEditar" x-cloak x-on:keydown.escape.window="showEditar = false"
            class="sirex-modal-fondo" x-on:click.self="showEditar = false">
            <div class="sirex-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-editar-resolucion">
                <div class="sirex-modal-cabeza">
                    <div class="sirex-modal-icono sirex-modal-icono-oro">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="titulo-editar-resolucion" class="sirex-modal-titulo">Modificar resolución</h3>
                        <p class="sirex-modal-subtitulo">Modificá los datos de la resolución.</p>
                    </div>
                    <button type="button" @click="showEditar = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="sirex-modal-cuerpo">
                    <div>
                        <label class="sirex-etiqueta">Nº de expediente</label>
                        <input wire:model="resolucionForm.numero_exp" placeholder="Ingrese el número de expediente" class="sirex-campo">
                        @error('resolucionForm.numero_exp') <p class="sirex-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="sirex-etiqueta">Nº de resolución</label>
                        <input wire:model="resolucionForm.numero_resolucion" placeholder="Ingrese el Nº de resolución" class="sirex-campo">
                    </div>

                    <div>
                        <label class="sirex-etiqueta">Fecha</label>
                        <input type="date" wire:model="resolucionForm.fecha" class="sirex-campo">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="sirex-etiqueta">Barrio</label>
                            <input wire:model="resolucionForm.cod_barrio" placeholder="Barrio" class="sirex-campo">
                        </div>
                        <div>
                            <label class="sirex-etiqueta">Casa</label>
                            <input wire:model="resolucionForm.cod_casa" placeholder="Nº de casa" class="sirex-campo">
                        </div>
                    </div>
                </div>

                <div class="sirex-modal-pie">
                    <button type="button" @click="showEditar = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>
                    <button type="submit" class="sirex-boton sirex-boton-primario">Guardar cambios</button>
                </div>
            </div>
        </div>

        <!-- modal para confirmar borrado -->
        <div x-show="showBorrar" x-cloak x-on:keydown.escape.window="showBorrar = false"
            class="sirex-modal-fondo" x-on:click.self="showBorrar = false">
            <div class="sirex-modal sirex-modal-sm sirex-modal-peligro" role="alertdialog" aria-modal="true" aria-labelledby="titulo-borrar-resolucion">
                <div class="sirex-modal-cabeza">
                    <div class="sirex-modal-icono sirex-modal-icono-peligro">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="titulo-borrar-resolucion" class="sirex-modal-titulo">¿Borrar resolución?</h3>
                        <p class="sirex-modal-subtitulo">¿Estás seguro que querés borrarla?</p>
                    </div>
                </div>

                <div class="sirex-modal-pie">
                    <button type="button" @click="showBorrar = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>
                    <x-boton-animado accion="$wire.borrar()" etiqueta="Borrar" />
                </div>
            </div>
        </div>
    @else
        <div class="flex items-start gap-3 rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta">
            <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4.5" y="10.5" width="15" height="9.5" rx="2" stroke-width="2" />
                <path stroke-width="2" d="M8 10.5V7a4 4 0 0 1 8 0v3.5" />
            </svg>
            <p>No tenés permiso para ver resoluciones. Pedile a un administrador que te lo habilite en Usuarios.</p>
        </div>
    @endif
</div>
