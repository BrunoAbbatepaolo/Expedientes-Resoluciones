{{-- Ventana de permisos: una fila por módulo con un selector de nivel (Sin acceso / Ver / Editar), plantillas de rol
     y un resumen de lo que se va a cambiar. Toda la lógica del navegador está en resources/js/modal-permisos.js
     y el catálogo en App\Support\CatalogoPermisos. Nada se guarda hasta apretar Guardar. --}}
@php($catalogo = \App\Support\CatalogoPermisos::paraPantalla())
@php($puedeAdmin = (bool) auth()->user()?->permiso('admin_ver'))

<div x-data="modalPermisos(@js($catalogo), @js($puedeAdmin))"
    x-on:open-modal.window="if ($event.detail[0] === 'modal-permisos') show = true"
    x-on:close-modal.window="if ($event.detail[0] === 'modal-permisos') show = false"
    x-show="show" x-cloak x-on:keydown.escape.window="show = false"
    class="sirex-modal-fondo" x-on:click.self="show = false">
    <div class="sirex-modal" style="max-width: 40rem;" role="dialog" aria-modal="true" aria-labelledby="titulo-permisos">

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

        {{-- Plantillas de rol: un clic y queda armado --}}
        <div class="px-6 pb-2">
            <div class="mb-1.5 flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Perfil rápido</p>
                <button type="button" @click="quitarTodo()" x-show="totalActivos > 0" x-cloak
                    class="rounded-md px-1.5 py-0.5 text-xs font-medium text-ipv-magenta hover:bg-ipv-magenta/10 dark:text-ipv-magenta-light">
                    Quitar todo
                </button>
            </div>
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

        {{-- Módulos: una fila cada uno --}}
        <div class="sirex-modal-cuerpo space-y-2 !pt-2">
            @foreach ($catalogo['modulos'] as $i => $modulo)
                <section x-data="{ m: catalogo.modulos[{{ $i }}] }" class="sirex-modulo" :class="{ 'is-on': nivelDe(m) > 0, 'sirex-resalte': m.permisos.some((p) => resaltadas.includes(p.clave)) }">
                    <span class="sirex-modal-icono !size-10 !rounded-xl">
                        <svg class="!size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $modulo['icono'] }}" /></svg>
                    </span>

                    <div class="min-w-0 flex-1 basis-40">
                        <p class="font-semibold text-ipv-ink dark:text-ipv-ink-dark" id="mod-{{ $modulo['clave'] }}">{{ $modulo['titulo'] }}</p>
                        <p class="text-xs leading-snug text-ipv-ink/55 dark:text-ipv-ink-dark/55" x-text="detalle(m)"></p>
                    </div>

                    <div class="sirex-segmentos max-sm:w-full" role="radiogroup" aria-labelledby="mod-{{ $modulo['clave'] }}"
                        @keydown.right.prevent="fijarNivel(m, Math.min(m.niveles.length, nivelDe(m) + 1))"
                        @keydown.left.prevent="fijarNivel(m, Math.max(0, nivelDe(m) - 1))">
                        <button type="button" role="radio" :aria-checked="(nivelDe(m) === 0).toString()" :tabindex="nivelDe(m) === 0 ? 0 : -1"
                            :disabled="bloqueado(m.niveles[0].clave)" @click="fijarNivel(m, 0)"
                            class="sirex-segmento" :class="{ 'is-off': nivelDe(m) === 0 }"
                            :title="bloqueado(m.niveles[0].clave) ? 'Solo quien ya tiene el panel de administración puede darlo o quitarlo.' : ''">
                            <svg x-show="bloqueado(m.niveles[0].clave)" x-cloak class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" /></svg>
                            Sin acceso
                        </button>
                        <template x-for="(n, k) in m.niveles" :key="n.clave">
                            <button type="button" role="radio" :aria-checked="(nivelDe(m) === k + 1).toString()" :tabindex="nivelDe(m) === k + 1 ? 0 : -1"
                                :disabled="bloqueado(n.clave)" @click="fijarNivel(m, k + 1)"
                                class="sirex-segmento" :class="{ 'is-on': nivelDe(m) === k + 1 }"
                                :title="bloqueado(n.clave) ? 'Solo quien ya tiene el panel de administración puede darlo o quitarlo.' : ''">
                                <svg x-show="n.titulo === 'Ver'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" /></svg>
                                <svg x-show="n.titulo === 'Editar'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                <svg x-show="n.titulo === 'Sí'" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7" /></svg>
                                <span x-text="n.titulo"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Permisos sueltos que se apoyan en el primer nivel (RRHH) --}}
                    @if ($modulo['extras'] !== [])
                        <div x-show="nivelDe(m) > 0" x-cloak x-collapse.duration.200ms class="w-full">
                            <div class="flex flex-wrap gap-1.5 border-t border-ipv-blue/10 pt-2.5 dark:border-white/10">
                                <template x-for="e in extrasDe(m)" :key="e.clave">
                                    <button type="button" role="switch" :aria-checked="activo(e.clave).toString()" @click="alternar(e.clave)" :title="e.descripcion"
                                        class="sirex-rol" :class="{ 'is-on': activo(e.clave), 'sirex-resalte': resaltadas.includes(e.clave) }">
                                        <svg x-show="activo(e.clave)" x-cloak class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7" /></svg>
                                        <svg x-show="! activo(e.clave)" class="size-3 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                                        <span x-text="e.titulo"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                </section>
            @endforeach
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
