<button type="button" wire:click="alternar" wire:loading.attr="disabled" aria-pressed="{{ $siguiendo ? 'true' : 'false' }}"
    class="sirex-btn inline-flex items-center gap-1.5 rounded-lg border px-3.5 py-2 text-sm font-medium transition hover:-translate-y-px hover:shadow-sm disabled:opacity-60
        {{ $siguiendo
            ? 'border-ipv-gold/50 bg-ipv-gold/20 text-ipv-gold-ink dark:text-ipv-gold-light'
            : 'border-ipv-blue/20 bg-white/70 text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85' }}">
    <svg class="size-4 transition-transform {{ $siguiendo ? 'scale-110' : '' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        fill="{{ $siguiendo ? 'currentColor' : 'none' }}" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44a.56.56 0 0 1 .32.99l-4.2 3.6 1.28 5.39a.56.56 0 0 1-.84.61L12 16.77l-4.73 2.87a.56.56 0 0 1-.84-.61l1.28-5.39-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44 2.13-5.11Z" />
    </svg>
    {{ $siguiendo ? 'Siguiendo' : 'Seguir' }}
</button>
