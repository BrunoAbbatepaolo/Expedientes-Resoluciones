<div x-show="showPase" x-cloak x-on:keydown.escape.window="showPase = false"
    class="sirex-modal-fondo" x-on:click.self="showPase = false">
    <div class="sirex-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-pase" style="overflow: visible;">
        <div class="sirex-modal-cabeza">
            <div class="sirex-modal-icono">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </div>
            <div>
                <h3 id="titulo-pase" class="sirex-modal-titulo">Realizar pase</h3>
                <p class="sirex-modal-subtitulo">Elegí la oficina de destino. El expediente queda pendiente de aceptación hasta que esa oficina lo confirme.</p>
            </div>
            <button type="button" wire:click="cancelarPase" @click="showPase = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="sirex-modal-cuerpo" style="overflow: visible;">
            <div class="relative">
                <label class="sirex-etiqueta">Oficina de destino</label>
                <div class="relative">
                    <input type="text" wire:model.live="query" class="sirex-campo pr-9"
                        placeholder="Buscar oficina de destino" autocomplete="off" />

                    @if(!empty($query))
                        <button type="button" wire:click="$set('query', '')" aria-label="Borrar búsqueda"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-ipv-ink/40 hover:text-ipv-ink/70 dark:text-ipv-ink-dark/40 dark:hover:text-ipv-ink-dark/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </div>

                @if(!empty($oficinas))
                    <ul class="absolute z-10 mt-1.5 max-h-48 w-full overflow-y-auto rounded-xl border border-ipv-blue/15 bg-white p-1 shadow-xl dark:border-white/10 dark:bg-[#1a2332]">
                        @foreach($oficinas as $oficina)
                            <li wire:key="oficina-pase-{{ $oficina->id }}"
                                class="cursor-pointer rounded-lg px-3 py-2 text-sm text-ipv-ink transition-colors duration-150 hover:bg-ipv-blue hover:text-white dark:text-ipv-ink-dark"
                                wire:click="selectOficina({{ $oficina->id }})">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-medium">{{ $oficina->nombre }}</span>
                                    <span class="rounded bg-black/[0.05] px-2 py-0.5 text-xs opacity-70 dark:bg-white/10">{{ $oficina->codigo }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <!-- Campo oculto para mantener el ID de la oficina -->
                <input type="hidden" wire:model="expedienteForm.ofi_salida">
            </div>
        </div>

        <div class="sirex-modal-pie rounded-b-[1.125rem]">
            <button type="button" wire:click="cancelarPase" @click="showPase = false" class="sirex-boton sirex-boton-secundario">Cerrar</button>
            <x-boton-animado variante="pase" accion="$wire.confirmarPase()" etiqueta="Confirmar pase" listo="Enviado"
                si="$wire.expedienteForm.ofi_salida" />
        </div>
    </div>
</div>
