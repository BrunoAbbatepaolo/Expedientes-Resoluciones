{{-- Autocompletar desde un PDF o Word: detecta el tipo y precarga el formulario (la persona revisa y guarda). --}}
<div class="sirex-glass-card mb-6 rounded-[14px] p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-base font-semibold text-ipv-ink dark:text-ipv-ink-dark">Autocompletar desde un documento <span class="text-xs font-normal opacity-60">(opcional)</span></h2>
            <p class="sirex-ayuda mt-1">Subí la resolución en PDF o Word: detecto de qué tipo es y cargo los datos que encuentre. Vos los revisás antes de guardar.</p>
        </div>
        <label class="sirex-boton sirex-boton-secundario cursor-pointer" wire:loading.class="opacity-60 pointer-events-none" wire:target="documentoAnalizar">
            <span wire:loading.remove wire:target="documentoAnalizar">Elegir PDF o Word</span>
            <span wire:loading wire:target="documentoAnalizar">Leyendo el documento…</span>
            <input type="file" wire:model="documentoAnalizar" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="hidden" />
        </label>
    </div>

    @if ($sugerencia)
        <div role="status" class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-ipv-blue/10 px-4 py-2.5 text-sm text-ipv-ink dark:bg-ipv-blue/20 dark:text-ipv-ink-dark">
            <span>Este documento parece ser una resolución de tipo <strong>{{ $sugerencia['nombre'] }}</strong>.</span>
            <button type="button" wire:click="aplicarSugerencia" class="sirex-boton sirex-boton-chico">Ir a ese formulario con los datos cargados</button>
        </div>
    @endif
</div>
