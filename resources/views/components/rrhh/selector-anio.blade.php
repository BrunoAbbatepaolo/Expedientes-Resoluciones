{{-- Flechas de año (mueve 12 meses) para componentes que usan el trait NavegaMeses. --}}
@props(['mes'])

<div class="flex items-center gap-1">
    <button type="button" wire:click="moverMes(-12)" class="rounded-lg px-2 py-1 text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5" aria-label="Año anterior">‹</button>
    <span class="min-w-14 text-center text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $mes->year }}</span>
    <button type="button" wire:click="moverMes(12)" class="rounded-lg px-2 py-1 text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5" aria-label="Año siguiente">›</button>
</div>
