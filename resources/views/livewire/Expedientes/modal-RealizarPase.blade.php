<div x-show="showPase" x-cloak x-on:keydown.escape.window="showPase = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
    x-on:click.self="showPase = false">
    <div class="w-full max-w-[26rem] rounded-2xl border border-ipv-blue/20 bg-white/95 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
    <div class="space-y-6 rounded-lg p-5">
        <div>
            <h3 class="text-center text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark">Realizar Pase</h3>
            <p class="mt-2 text-ipv-ink/60 dark:text-ipv-ink-dark/60">Elegí la oficina de destino. El expediente queda pendiente de aceptación hasta que esa oficina lo confirme.</p>
        </div>

        <div class="relative mb-4">
            <label class="mb-1.5 block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Oficina de Destino</label>
            <div class="relative">
                <input
                    type="text"
                    wire:model.live="query"
                    class="w-full rounded-md border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink shadow-sm focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark"
                    placeholder="Buscar oficina de destino"
                    autocomplete="off" />

                @if(!empty($query))
                <button type="button"
                    wire:click="$set('query', '')"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-ipv-ink/40 hover:text-ipv-ink/70 dark:text-ipv-ink-dark/40 dark:hover:text-ipv-ink-dark/70">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
                @endif
            </div>

            @if(!empty($oficinas))
            <ul class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-ipv-blue/15 bg-white shadow-lg dark:border-white/10 dark:bg-slate-800">
                @foreach($oficinas as $oficina)
                <li wire:key="oficina-pase-{{ $oficina->id }}"
                    class="cursor-pointer px-4 py-2.5 text-ipv-ink transition-colors duration-150 hover:bg-black/[0.03] dark:text-ipv-ink-dark dark:hover:bg-white/5"
                    wire:click="selectOficina({{ $oficina->id }})">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ $oficina->nombre }}</span>
                        <span class="rounded bg-black/[0.04] px-2 py-0.5 text-xs text-ipv-ink/50 dark:bg-white/5 dark:text-ipv-ink-dark/50">
                            {{ $oficina->codigo }}
                        </span>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif

            <!-- Campo oculto para mantener el ID de la oficina -->
            <input type="hidden" wire:model="expedienteForm.ofi_salida">
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 border-t border-ipv-blue/10 pt-4 dark:border-white/10">
            <button type="button" wire:click="confirmarPase"
                class="rounded-lg bg-ipv-blue px-5 py-2 font-medium text-white shadow-md transition-all duration-200 hover:scale-105 hover:bg-ipv-blue-dark focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 focus:ring-offset-2">
                Confirmar Pase
            </button>
            <button type="button" wire:click="cancelarPase" @click="showPase = false"
                class="rounded-lg border border-ipv-blue/20 px-5 py-2 font-medium text-ipv-ink/80 shadow-md transition-all duration-200 hover:scale-105 hover:bg-black/[0.03] focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 focus:ring-offset-2 dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                Cerrar
            </button>
        </div>
    </div>
    </div>
</div>
