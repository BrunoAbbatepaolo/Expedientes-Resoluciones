{{-- Ventana de permisos: módulos plegables con interruptores, buscador, plantillas de rol, dependencias entre
     permisos y un resumen de lo que se va a cambiar. Toda la lógica del navegador está en resources/js/modal-permisos.js
     y el catálogo en App\Support\CatalogoPermisos. Nada se guarda hasta apretar Guardar. --}}
@php($catalogo = \App\Support\CatalogoPermisos::paraPantalla())

<div x-data="modalPermisos(@js($catalogo))"
    x-on:open-modal.window="if ($event.detail[0] === 'modal-permisos') show = true"
    x-on:close-modal.window="if ($event.detail[0] === 'modal-permisos') show = false"
    x-show="show" x-cloak x-on:keydown.escape.window="show = false"
    class="sirex-modal-fondo" x-on:click.self="show = false">
    <div class="sirex-modal" style="max-width: 44rem;" role="dialog" aria-modal="true" aria-labelledby="titulo-permisos">

        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono sirex-modal-icono-oro">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 id="titulo-permisos" class="sirex-modal-titulo">Gestión de permisos</h2>
                <p class="sirex-modal-subtitulo">
                    Accesos de <span class="font-semibold text-ipv-blue dark:text-ipv-blue-light">{{ $usuarioSeleccionado->name ?? 'Usuario' }}</span>
                    · <span x-text="totalActivos"></span> activos
                </p>
            </div>
            <button type="button" @click="show = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Buscador, filtro y plantillas --}}
        <div class="space-y-3 px-6 pb-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[12rem] flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M21 21l-4.3-4.3m2-5.2a7.2 7.2 0 1 1-14.4 0 7.2 7.2 0 0 1 14.4 0Z" /></svg>
                    <input type="search" x-model="busqueda" placeholder="Buscar permiso…" aria-label="Buscar permiso" autocomplete="off"
                        x-on:keydown.escape.stop="if (busqueda) busqueda = ''; else show = false" class="sirex-campo pl-9">
                </div>
                <button type="button" role="switch" :aria-checked="soloActivos.toString()" @click="soloActivos = ! soloActivos"
                    class="sirex-boton sirex-boton-chico sirex-boton-secundario" :class="soloActivos ? '!border-ipv-blue !bg-ipv-blue/10 !text-ipv-blue dark:!text-ipv-blue-light' : ''">
                    Solo activos
                </button>
                <button type="button" @click="quitarTodo()" x-show="totalActivos > 0" x-cloak
                    class="sirex-boton sirex-boton-chico text-ipv-magenta hover:bg-ipv-magenta/10 dark:text-ipv-magenta-light">
                    Quitar todo
                </button>
            </div>

            <div>
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Plantillas de rol</p>
                <div class="-mx-6 flex gap-1.5 overflow-x-auto px-6 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0">
                    <template x-for="(rol, clave) in catalogo.roles" :key="clave">
                        <button type="button" @click="aplicarRol(rol)" :title="descripcionRol(rol)"
                            class="sirex-rol" :class="{ 'is-on': rolAplicado(rol) }" :aria-pressed="rolAplicado(rol).toString()">
                            <svg x-show="rolAplicado(rol)" x-cloak class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7" /></svg>
                            <span x-text="rol.nombre"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Módulos --}}
        <div class="sirex-modal-cuerpo !pt-1" style="min-height: 8rem;">
            @foreach ($catalogo['modulos'] as $i => $modulo)
                <section x-data="{ m: catalogo.modulos[{{ $i }}] }" x-show="moduloVisible(m)" class="sirex-permisos-modulo">
                    <div class="sirex-permisos-modulo-cabeza">
                        <button type="button" @click="alternarAbierto(m)" :aria-expanded="estaAbierto(m).toString()" class="sirex-permisos-modulo-titulo">
                            <span class="sirex-modal-icono !size-8 !rounded-[0.625rem]">
                                <svg class="!size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $modulo['icono'] }}" /></svg>
                            </span>
                            <span class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $modulo['titulo'] }}</span>
                            <span class="sirex-contador-modulo" :class="cuantosActivos(m) > 0 ? 'is-on' : ''" x-text="cuantosActivos(m) + '/' + m.permisos.length"></span>
                            <svg class="ml-auto size-4 shrink-0 opacity-50 transition-transform duration-200" :class="estaAbierto(m) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button type="button" @click="alternarModulo(m)" class="sirex-boton sirex-boton-chico sirex-boton-secundario shrink-0"
                            x-text="moduloCompleto(m) ? 'Quitar todo' : 'Activar todo'"></button>
                    </div>

                    <div x-show="estaAbierto(m)" x-collapse.duration.200ms>
                        @foreach ($modulo['permisos'] as $j => $permiso)
                            <button type="button" role="switch" x-data="{ p: m.permisos[{{ $j }}] }" x-show="visible(p)"
                                :aria-checked="activo(p.clave).toString()" @click="alternar(p.clave)"
                                class="sirex-permiso-fila" :class="{ 'is-on': activo(p.clave), 'sirex-resalte': resaltadas.includes(p.clave) }">
                                <span class="min-w-0 flex-1 text-left">
                                    <span class="block text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $permiso['titulo'] }}</span>
                                    <span class="block text-xs leading-snug text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $permiso['descripcion'] }}</span>
                                    @if ($permiso['requiere'])
                                        <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-ipv-ink/45 dark:text-ipv-ink-dark/45">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1 1M14 11a5 5 0 0 0-7.1 0l-3 3A5 5 0 0 0 11 21l1-1" /></svg>
                                            Requiere «{{ collect($catalogo['modulos'])->flatMap(fn ($mod) => $mod['permisos'])->firstWhere('clave', $permiso['requiere'])['titulo'] }}»
                                        </span>
                                    @endif
                                </span>
                                <span class="sirex-switch" aria-hidden="true"><span class="sirex-switch-perilla"></span></span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div x-show="! hayResultados" x-cloak>
                <x-vacio icono="buscar">Ningún permiso coincide con lo que buscaste.</x-vacio>
            </div>
        </div>

        {{-- Resumen de lo que se va a cambiar --}}
        <div class="sirex-permisos-resumen" aria-live="polite">
            <template x-if="! hayCambios">
                <span class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin cambios por guardar.</span>
            </template>
            <template x-if="hayCambios">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Vas a cambiar:</span>
                    <template x-for="c in agregados" :key="'a' + c">
                        <span class="sirex-cambio sirex-cambio-mas">+ <span x-text="titulo(c)"></span></span>
                    </template>
                    <template x-for="c in quitados" :key="'q' + c">
                        <span class="sirex-cambio sirex-cambio-menos">− <span x-text="titulo(c)"></span></span>
                    </template>
                </div>
            </template>
        </div>

        <div class="sirex-modal-pie">
            <button type="button" @click="show = false" class="sirex-boton sirex-boton-secundario">Cancelar</button>

            <button type="button" class="sirex-boton sirex-boton-primario" :disabled="! hayCambios"
                wire:click="guardarPermisos" wire:loading.attr="disabled" wire:target="guardarPermisos">
                <span wire:loading.remove wire:target="guardarPermisos" class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Guardar permisos</span>
                </span>
                <span wire:loading wire:target="guardarPermisos">Guardando...</span>
            </button>
        </div>
    </div>
</div>
