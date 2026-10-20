{{-- Autocompletar desde un PDF o Word: detecta el tipo y precarga el formulario (la persona revisa y guarda). --}}
<div class="mb-6">
    <label class="sirex-autocompletar group flex cursor-pointer flex-wrap items-center gap-3.5 rounded-2xl border-2 border-dashed border-ipv-blue/25 p-4 transition hover:border-ipv-blue/50 hover:bg-ipv-blue/[0.04] dark:border-white/20 dark:hover:border-white/40 dark:hover:bg-white/[0.04]"
        wire:loading.class="opacity-60 pointer-events-none" wire:target="documentoAnalizar">
        <span class="sirex-elegir-icono grid size-11 shrink-0 place-items-center rounded-xl" style="--acento: 0 81 158">
            <svg wire:loading.remove wire:target="documentoAnalizar" class="sirex-autocompletar-subir size-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0L8 8m4-4 4 4M4 15v3a2 2 0 002 2h12a2 2 0 002-2v-3" /></svg>
            <svg wire:loading wire:target="documentoAnalizar" class="size-6 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-20" /><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
        </span>
        <span class="min-w-0 flex-1 basis-48">
            <span class="block text-[0.95rem] font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                <span wire:loading.remove wire:target="documentoAnalizar">¿Ya la tenés hecha? Subila y se completa sola</span>
                <span wire:loading wire:target="documentoAnalizar">Leyendo el documento…</span>
            </span>
            <span class="block text-[0.8rem] text-ipv-ink/60 dark:text-ipv-ink-dark/60">PDF o Word. Detecto el tipo y cargo los datos que encuentre; vos los revisás antes de guardar.</span>
        </span>
        <span class="rounded-lg border border-ipv-blue/25 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-blue transition group-hover:bg-white dark:border-white/20 dark:bg-white/[0.06] dark:text-ipv-blue-light dark:group-hover:bg-white/10">Elegir archivo</span>
        <input type="file" wire:model="documentoAnalizar" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="hidden" />
    </label>

    @if ($sugerencia)
        <div role="status" class="sirex-res-aviso mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-ipv-blue/10 px-4 py-2.5 text-sm text-ipv-ink dark:bg-ipv-blue/20 dark:text-ipv-ink-dark">
            <span>Este documento parece ser una resolución de tipo <strong>{{ $sugerencia['nombre'] }}</strong>.</span>
            <button type="button" wire:click="aplicarSugerencia" class="sirex-boton sirex-boton-chico">Ir a ese formulario con los datos cargados</button>
        </div>
    @endif
</div>
