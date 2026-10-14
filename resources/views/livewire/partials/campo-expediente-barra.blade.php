{{-- Número de expediente para los modos de edición libre (personalizado / plantilla): la resolución se guarda vinculada a él. --}}
<div class="flex min-w-[16rem] flex-col gap-0.5">
    <label class="flex items-center gap-2 text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
        Expediente
        <input type="text" wire:model.live.debounce.400ms="datos.num_exp" placeholder="Ej. 7844/440-2026"
            class="w-48 rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-1.5 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
    </label>
    @include('livewire.partials.aviso-expediente')
</div>
