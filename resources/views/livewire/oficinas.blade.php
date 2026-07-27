<div class="space-y-4">
    <!-- Título principal -->
    <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">
        Sistema de Gestión de Oficinas
    </h1>

    <!-- Barra de búsqueda -->
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-1/3">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" wire:model.live="inputBusqueda" placeholder="Buscar por nombre..."
                class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2 pl-10 pr-4 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="sirex-glass-card overflow-hidden rounded-[14px]">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Nombre</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                @forelse($oficinas as $oficina)
                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                    <td class="px-4 py-3 text-sm text-ipv-ink dark:text-ipv-ink-dark">{{ $oficina->nombre }}</td>
                </tr>
                @empty
                <tr>
                    <td class="px-6 py-4 text-center text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">No se encontraron oficinas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $oficinas->links() }}
    </div>
</div>