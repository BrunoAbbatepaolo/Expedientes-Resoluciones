<div class="sirex-resoluciones space-y-5" x-data="{ showEditar: false, showBorrar: false }"
    x-on:modal-close.window="
        if ($event.detail.name === 'edit-profile') showEditar = false;
        if ($event.detail.name === 'delete-profile') showBorrar = false;
    ">
    @if (auth()->user()->permiso('resolucion_ver'))
        @php
            $puedeEditar = (bool) auth()->user()->permiso('resolucion_editar');
            $verExpediente = (bool) auth()->user()->permiso('expediente_ver');
            $nombreTipo = fn (?string $t) => $t ? \App\Livewire\CrearResolucion::nombreDeTipo($t) : 'Sin tipo';
            // Color de acento por tipo (institucional: azul, oro y magenta del IPV).
            $acento = fn (?string $t) => match ($t) {
                'Cancelaciones', 'Transferencia-Cancelacion' => 'azul',
                'Transferencias' => 'cielo',
                'Resciciones' => 'magenta',
                'Rectificacion' => 'oro',
                'AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada' => 'verde',
                default => 'gris',
            };
            $hayFiltros = $buscar !== '' || $tipo !== '';
        @endphp

        {{-- Encabezado --}}
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Resoluciones</h1>
                <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">
                    {{ $total }} {{ $total === 1 ? 'resolución' : 'resoluciones' }} de tu oficina. Tocá una fila para abrir su PDF.
                </p>
            </div>
            @if ($puedeEditar)
                <a wire:navigate href="{{ route('resoluciones.elegir') }}"
                    class="sirex-res-nueva group inline-flex items-center gap-2 rounded-xl bg-ipv-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ipv-blue-dark hover:shadow-md">
                    <svg class="size-4 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    Nueva resolución
                </a>
            @endif
        </header>

        {{-- Recién guardada desde "Plantilla completa": el PDF se abre en otra pestaña; si el navegador la bloqueó, queda el enlace. --}}
        @php $guardada = session()->pull('resolucion_guardada'); @endphp
        @if ($guardada)
            <div role="status" class="sirex-res-aviso flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-600/25 bg-emerald-600/10 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
                <span class="inline-flex items-center gap-2">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path class="sirex-trazo-anim" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Resolución guardada{{ $guardada['numero_exp'] ? ' (expediente '.$guardada['numero_exp'].')' : '' }}.
                </span>
                <a href="{{ route('resoluciones.imprimir', $guardada['id']) }}" target="_blank" rel="noopener" class="font-semibold underline">Imprimir / Guardar PDF</a>
            </div>
        @endif

        {{-- Búsqueda y filtros --}}
        <div class="space-y-3">
            <label class="relative block">
                <span class="sr-only">Buscar resoluciones</span>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/40" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="M20 20l-3.5-3.5" /></svg>
                <input type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por expediente, número de resolución, barrio o casa"
                    class="w-full rounded-xl border border-ipv-blue/15 bg-white/85 py-2.5 pl-10 pr-10 text-sm text-ipv-ink shadow-sm transition focus:border-ipv-blue/40 focus:outline-none focus:ring-4 focus:ring-ipv-blue/10 dark:border-white/12 dark:bg-white/[0.05] dark:text-ipv-ink-dark" />
                <svg wire:loading wire:target="buscar,tipo" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 animate-spin text-ipv-blue" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-20" /><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
            </label>

            @if ($tiposPresentes->count() > 1)
                <div class="-mx-1 flex gap-1.5 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Filtrar por tipo">
                    <button type="button" wire:click="$set('tipo', '')" @class(['sirex-res-chip', 'is-on' => $tipo === ''])>Todas</button>
                    @foreach ($tiposPresentes as $t)
                        <button type="button" wire:click="$set('tipo', '{{ $t }}')" @class(['sirex-res-chip', 'is-on' => $tipo === $t]) data-acento="{{ $acento($t === 'otras' ? null : $t) }}">
                            <span class="sirex-res-punto" aria-hidden="true"></span>{{ $t === 'otras' ? 'Sin tipo' : $nombreTipo($t) }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Lista: tabla en escritorio, tarjetas en celular --}}
        <div class="sirex-glass-card overflow-hidden rounded-[16px] transition-opacity duration-150" wire:loading.delay.class="opacity-60" aria-live="polite">
            @if ($resolucionesConExpediente->isNotEmpty())
                <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1.5fr)_8rem_minmax(0,1fr)_minmax(0,1.1fr)_3rem] gap-4 border-b border-ipv-blue/10 px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-ipv-ink/45 lg:grid dark:border-white/10 dark:text-ipv-ink-dark/45">
                    <span>Expediente</span><span>Resolución</span><span>Fecha</span><span>Vivienda</span><span>Documentos</span><span class="sr-only">Acciones</span>
                </div>
            @endif

            <ul class="divide-y divide-ipv-blue/[0.08] dark:divide-white/[0.07]">
                @forelse ($resolucionesConExpediente as $resolucion)
                    @php
                        $urlPdf = $resolucion->urlPdf();
                        $adjuntos = $resolucion->archivos;
                        $fecha = $resolucion->fecha ? \Carbon\Carbon::parse($resolucion->fecha)->format('d/m/Y') : null;
                        $numeroExp = $resolucion->expediente?->numero ?? $resolucion->numero_exp;
                    @endphp
                    <li wire:key="res-{{ $resolucion->id }}" class="sirex-res-fila group relative" style="--i: {{ min($loop->index, 14) }}" data-acento="{{ $acento($resolucion->tipo) }}">
                        {{-- Toda la fila abre el PDF (los enlaces y botones internos van por encima). --}}
                        @if ($urlPdf)
                            <a href="{{ $urlPdf }}" target="_blank" rel="noopener" class="absolute inset-0 z-0" aria-label="Abrir el PDF de la resolución {{ $resolucion->numero_resolucion ?: 's/n' }}"></a>
                        @endif

                        <div class="pointer-events-none relative grid grid-cols-[1fr_auto] gap-x-3 gap-y-1.5 px-4 py-3.5 sm:px-5 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1.5fr)_8rem_minmax(0,1fr)_minmax(0,1.1fr)_3rem] lg:items-center lg:gap-4">
                            {{-- Expediente --}}
                            <div class="col-start-1 row-start-1 min-w-0 lg:col-start-auto lg:row-start-auto">
                                <span class="mr-1 text-[11px] font-semibold uppercase tracking-wider text-ipv-ink/40 lg:hidden dark:text-ipv-ink-dark/40">Expte.</span>
                                @if ($resolucion->expediente && $verExpediente)
                                    <a href="{{ route('expedientes.ipv.ficha', $resolucion->expediente->id) }}" wire:navigate.hover
                                        class="pointer-events-auto relative z-10 font-mono text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light" title="Ver el expediente">{{ $resolucion->expediente->numero }}</a>
                                @elseif ($numeroExp)
                                    <span class="font-mono text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $numeroExp }}</span>
                                @else
                                    <span class="text-sm italic text-ipv-ink/40 dark:text-ipv-ink-dark/40">Sin expediente</span>
                                @endif
                            </div>

                            {{-- Resolución: número + tipo --}}
                            <div class="col-start-1 row-start-2 flex min-w-0 items-center gap-2.5 lg:col-start-auto lg:row-start-auto">
                                <span class="sirex-res-icono grid size-9 shrink-0 place-items-center rounded-xl" aria-hidden="true">
                                    <svg class="size-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h4" /></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">Resolución {{ $resolucion->numero_resolucion ?: 's/n' }}</span>
                                    <span class="block truncate text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $nombreTipo($resolucion->tipo) }}</span>
                                </span>
                            </div>

                            {{-- Fecha dd/mm/aaaa --}}
                            <div class="col-start-2 row-start-1 text-right lg:col-start-auto lg:row-start-auto lg:text-left">
                                <span class="inline-flex items-center gap-1.5 text-sm tabular-nums text-ipv-ink/75 dark:text-ipv-ink-dark/75">
                                    <svg class="size-3.5 text-ipv-ink/35 dark:text-ipv-ink-dark/35" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" /><path stroke-linecap="round" d="M3.5 10h17M8 3v4M16 3v4" /></svg>
                                    {{ $fecha ?? 'Sin fecha' }}
                                </span>
                            </div>

                            {{-- Vivienda --}}
                            <div class="hidden text-sm text-ipv-ink/70 lg:block dark:text-ipv-ink-dark/70">
                                @if ($resolucion->cod_barrio || $resolucion->cod_casa)
                                    Mz. {{ $resolucion->cod_barrio ?? '—' }} · Lote {{ $resolucion->cod_casa ?? '—' }}
                                @else
                                    <span class="text-ipv-ink/35 dark:text-ipv-ink-dark/35">—</span>
                                @endif
                            </div>

                            {{-- Documentos --}}
                            <div class="col-start-1 row-start-3 flex flex-wrap items-center gap-1.5 lg:col-start-auto lg:row-start-auto">
                                @if ($urlPdf)
                                    <span class="sirex-res-pdf inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8" /></svg>
                                        Ver PDF
                                    </span>
                                @else
                                    <span class="text-xs text-ipv-ink/40 dark:text-ipv-ink-dark/40">Sin PDF</span>
                                @endif
                                @if ($resolucion->tipo && $adjuntos->isNotEmpty())
                                    <a href="{{ route('resoluciones.archivo', $adjuntos->first()) }}" target="_blank" rel="noopener"
                                        class="pointer-events-auto relative z-10 inline-flex items-center gap-1 rounded-full bg-black/[0.04] px-2.5 py-1 text-xs font-medium text-ipv-ink/70 hover:bg-black/[0.08] dark:bg-white/[0.06] dark:text-ipv-ink-dark/75"
                                        title="Archivos adjuntos">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 7.8l-6.4 6.4a2 2 0 102.8 2.8l6.4-6.4a4 4 0 10-5.6-5.6l-6.4 6.4a6 6 0 108.5 8.5L19 15.4" /></svg>
                                        {{ $adjuntos->count() }} {{ $adjuntos->count() === 1 ? 'adjunto' : 'adjuntos' }}
                                    </a>
                                @elseif (! $resolucion->tipo && $adjuntos->count() > 1)
                                    <span class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">+{{ $adjuntos->count() - 1 }} {{ $adjuntos->count() - 1 === 1 ? 'archivo' : 'archivos' }}</span>
                                @endif
                            </div>

                            {{-- Acciones --}}
                            <div class="col-start-2 row-start-2 row-span-2 flex items-center justify-end lg:col-start-auto lg:row-start-auto lg:row-span-1">
                                @if ($puedeEditar)
                                    <div class="pointer-events-auto relative z-20" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                                        <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Acciones de la resolución {{ $resolucion->numero_resolucion ?: 's/n' }}"
                                            class="grid size-9 place-items-center rounded-xl text-ipv-ink/55 transition hover:bg-black/[0.06] hover:text-ipv-ink dark:text-ipv-ink-dark/60 dark:hover:bg-white/[0.08] dark:hover:text-ipv-ink-dark">
                                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5.5" r="1.7" /><circle cx="12" cy="12" r="1.7" /><circle cx="12" cy="18.5" r="1.7" /></svg>
                                        </button>
                                        <div x-show="open" x-cloak
                                            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            class="absolute right-0 z-50 mt-1.5 w-52 origin-top-right overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-ipv-blue/15 dark:bg-slate-800 dark:ring-white/10">
                                            @if ($urlPdf)
                                                <a href="{{ $urlPdf }}" target="_blank" rel="noopener" @click="open = false"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-ipv-ink/85 transition hover:bg-ipv-blue/[0.08] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.06]">
                                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a2 2 0 012-2h14a2 2 0 012 2v6a1 1 0 01-1 1h-2M7 14h10v7H7z" /></svg>
                                                    Ver / imprimir PDF
                                                </a>
                                            @endif
                                            <button type="button" wire:click="cargarResolucion({{ $resolucion->id }})" @click="open = false; showEditar = true"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-ipv-ink/85 transition hover:bg-ipv-blue/[0.08] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.06]">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Modificar datos
                                            </button>
                                            <div class="my-1 border-t border-ipv-blue/10 dark:border-white/10"></div>
                                            <button type="button" wire:click="confirmarBorrado({{ $resolucion->id }})" @click="open = false; showBorrar = true"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-ipv-magenta transition hover:bg-ipv-magenta/10">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-6">
                        @if ($hayFiltros)
                            <x-vacio icono="buscar">No hay resoluciones que coincidan con la búsqueda.</x-vacio>
                            <div class="mt-2 text-center">
                                <button type="button" wire:click="limpiarFiltros" class="text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">Limpiar búsqueda y filtros</button>
                            </div>
                        @else
                            <x-vacio icono="buscar">Todavía no hay resoluciones.</x-vacio>
                            @if ($puedeEditar)
                                <div class="mt-2 text-center">
                                    <a wire:navigate href="{{ route('resoluciones.elegir') }}" class="text-sm font-semibold text-ipv-blue hover:underline dark:text-ipv-blue-light">Crear la primera</a>
                                </div>
                            @endif
                        @endif
                    </li>
                @endforelse
            </ul>
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
                        @error('resolucionForm.numero_resolucion') <p class="sirex-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="sirex-etiqueta">Fecha</label>
                        <input type="date" wire:model="resolucionForm.fecha" class="sirex-campo">
                        @error('resolucionForm.fecha') <p class="sirex-error">{{ $message }}</p> @enderror
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
                    <button type="button" wire:click="guardarEdicion" wire:loading.attr="disabled" wire:target="guardarEdicion" class="sirex-boton sirex-boton-primario">Guardar cambios</button>
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
