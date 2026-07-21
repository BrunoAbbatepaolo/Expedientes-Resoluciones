<x-layouts.app title="No encontrado">
    <div class="flex min-h-[60vh] flex-col items-center justify-center gap-4 text-center">
        <div class="flex size-16 items-center justify-center rounded-full bg-ipv-gold/15 text-ipv-gold-ink dark:text-ipv-gold-light">
            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="M21 21l-4.3-4.3" />
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">No encontrado</h1>
            <p class="mt-1 max-w-sm text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                Lo que buscás no existe o fue eliminado.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" wire:navigate
            class="mt-2 rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            Volver al panel
        </a>
    </div>
</x-layouts.app>
