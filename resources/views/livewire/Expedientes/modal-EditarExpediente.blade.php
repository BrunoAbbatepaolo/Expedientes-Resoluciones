<div x-show="showEditar" x-cloak x-on:keydown.escape.window="showEditar = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm"
    x-on:click.self="showEditar = false">
    <div class="w-full max-w-[30rem] rounded-2xl border border-ipv-blue/20 bg-white/95 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
    <div class="space-y-6 rounded-lg p-5">
        <div>
            <h3 class="text-center text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark">Editar Expediente</h3>
            <p class="mt-2 text-ipv-ink/60 dark:text-ipv-ink-dark/60">Modifique los datos del expediente.</p>
        </div>

        <div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Numº Expediente</label>
                <input wire:model="expedienteForm.num_exp" placeholder="Ingrese número de expediente"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                <x-input-error for="expedienteForm.num_exp" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Folio</label>
                <input wire:model="expedienteForm.folio" placeholder="Ingrese el folio"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                <x-input-error for="expedienteForm.folio" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Causante</label>
                <input wire:model="expedienteForm.causante" placeholder="Ingrese el causante"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                <x-input-error for="expedienteForm.causante" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Asunto</label>
                <input wire:model="expedienteForm.asunto" placeholder="Ingrese el asunto"
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                <x-input-error for="expedienteForm.asunto" />
            </div>

            <div class="mb-4 flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Ingreso</label>
                    <input wire:model="expedienteForm.fecha_ingreso" type="date"
                        class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                    <x-input-error for="expedienteForm.fecha_ingreso" />
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">Fecha de Salida</label>
                    <input wire:model="expedienteForm.fecha_salida" type="date"
                        class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
                    @error('expedienteForm.fecha_salida')
                        <span class="text-sm text-ipv-magenta">{{ $message }}</span>
                    @enderror
                    <x-input-error for="expedienteForm.fecha_salida" />
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap justify-end gap-3 border-t border-ipv-blue/10 pt-4 dark:border-white/10">
            <button type="button" wire:click="actualizar"
                class="rounded-lg bg-ipv-blue px-5 py-2 font-medium text-white shadow-md transition-all duration-200 hover:scale-105 hover:bg-ipv-blue-dark focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 focus:ring-offset-2">
                Guardar
            </button>
            <button type="button" wire:click="cancelarModal" @click="showEditar = false"
                class="rounded-lg border border-ipv-blue/20 px-5 py-2 font-medium text-ipv-ink/80 shadow-md transition-all duration-200 hover:scale-105 hover:bg-black/[0.03] focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 focus:ring-offset-2 dark:border-white/15 dark:text-ipv-ink-dark/85 dark:hover:bg-white/5">
                Cerrar
            </button>
        </div>
    </div>
    </div>
</div>
