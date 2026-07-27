<x-layouts.app title="Acceso denegado">
    <div class="flex min-h-[60vh] flex-col items-center justify-center gap-4 text-center">
        <div class="flex size-16 items-center justify-center rounded-full bg-ipv-magenta/10 text-ipv-magenta">
            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="4.5" y="10.5" width="15" height="9.5" rx="2" />
                <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5" />
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Acceso denegado</h1>
            <p class="mt-1 max-w-sm text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                No tenés permiso para acceder a esta sección. Si creés que es un error, contactá a un administrador.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" wire:navigate
            class="mt-2 rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
            Volver al panel
        </a>
    </div>
</x-layouts.app>
