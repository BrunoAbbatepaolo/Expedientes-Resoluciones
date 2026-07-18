<div class="space-y-4" x-data="{ showFiltro: false, showNuevo: false, showEditar: false, showPase: false, showBorrar: false }"
    x-on:modal-close.window="
        if ($event.detail.name === 'modal-filtro') showFiltro = false;
        if ($event.detail.name === 'modal-exp') showNuevo = false;
        if ($event.detail.name === 'modal-editarExpediente') showEditar = false;
        if ($event.detail.name === 'modal-realizarPase') showPase = false;
        if ($event.detail.name === 'modal-ConfirmarBorrado') showBorrar = false;
    ">
    @if (auth()->user()->permiso('expediente_ver'))
        <h1 class="text-2xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">
            Sistema de Expedientes - {{ $oficinaUsuario->nombre ?? 'Sin oficina asignada' }}
        </h1>
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-1/3">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-ipv-ink/40 dark:text-ipv-ink-dark/45"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live="search" placeholder="Buscar expediente..."
                    class="w-full rounded-lg border border-ipv-blue/20 bg-white/80 py-2 pl-10 pr-4 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark" />
            </div>
            <div class="flex gap-2">
                <button type="button" @click="showFiltro = true"
                    class="cursor-pointer rounded-lg border border-ipv-blue/20 bg-white/70 px-4 py-2 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                    Filtrar
                </button>
                @if (auth()->user()->permiso('expediente_editar'))
                    <button type="button" @click="showNuevo = true"
                        class="cursor-pointer rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">
                        Nuevo Expediente
                    </button>
                @endif
            </div>
        </div>

        <div class="sirex-glass-card overflow-hidden rounded-[14px]">
            @if ($tipoVista === 'entrantes')
                <table class="w-full table-fixed">
                    <thead>
                        <tr>
                            <th class="w-[110px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Número</th>
                            <th class="w-[180px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Causante</th>
                            <th class="w-[180px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Origen</th>
                            <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Fecha</th>
                            <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                        @forelse ($expedientes as $pase)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                                <td wire:click="verDetalle({{ $pase->expediente_id }})"
                                    class="cursor-pointer overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $pase->expediente->num_exp ?? '-' }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $this->formatearCausante($pase->expediente->causante ?? '-') }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                    {{ $pase->oficinaOrigen->nombre ?? '-' }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                    {{ $this->obtenerDMY($pase->fecha) }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    @if (auth()->user()->permiso('expediente_editar'))
                                        <button type="button" wire:click="aceptarPase({{ $pase->id }})"
                                            class="cursor-pointer rounded-lg bg-ipv-blue px-3 py-1.5 text-xs font-semibold text-white hover:bg-ipv-blue-dark">
                                            Aceptar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                                    No hay expedientes entrantes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full table-fixed">
                    <thead>
                        <tr>
                            <th class="w-[110px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Número</th>
                            <th class="hidden w-[80px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50 2xl:table-cell">Fojas</th>
                            <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Ingreso</th>
                            <th class="w-[180px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Causante</th>
                            <th class="hidden w-[200px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50 2xl:table-cell">Asunto</th>
                            <th class="w-[180px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Oficina Salida</th>
                            <th class="w-[100px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Salida</th>
                            @if (auth()->user()->permiso('expediente_editar'))
                                <th class="w-[80px] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
                        @forelse ($expedientes as $expediente)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                                <td wire:click="verDetalle({{ $expediente->id }})"
                                    class="cursor-pointer overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $expediente->num_exp }}
                                </td>
                                <td class="hidden overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70 2xl:table-cell">
                                    {{ $expediente->folio }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                    {{ $this->obtenerDMY($expediente->fecha_ingreso) }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $this->formatearCausante($expediente->causante) }}
                                </td>
                                <td class="hidden px-4 py-2.5 text-center 2xl:table-cell">
                                    <div class="overflow-hidden text-ellipsis whitespace-nowrap text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">{{ $expediente->asunto }}</div>
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                    {{ $expediente->oficina->nombre ?? '-' }}
                                </td>
                                <td class="overflow-hidden text-ellipsis whitespace-nowrap px-4 py-2.5 text-center text-sm text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                                    {{ $this->obtenerDMY($expediente->fecha_salida) }}
                                </td>
                                @if (auth()->user()->permiso('expediente_editar'))
                                    <td class="px-4 py-2.5 text-center">
                                        <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                            <button @click="open = !open"
                                                class="cursor-pointer rounded-lg bg-black/[0.04] p-2 transition-colors duration-200 hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.12]">
                                                <svg class="size-5 text-ipv-ink/70 dark:text-ipv-ink-dark/70" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 3c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 14c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                                </svg>
                                            </button>

                                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute right-0 z-50 mt-2 w-48 origin-top-right overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-ipv-blue/15 dark:bg-slate-800 dark:ring-white/10">
                                                <button wire:click="editar({{ $expediente->id }})" @click="open = false; showEditar = true"
                                                    class="flex w-full cursor-pointer items-center gap-2 px-4 py-3 text-left text-sm text-ipv-ink/80 transition-all duration-200 hover:bg-ipv-blue hover:text-white dark:text-ipv-ink-dark/85">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Editar expediente
                                                </button>
                                                <button wire:click="abrirPase({{ $expediente->id }})" @click="open = false; showPase = true"
                                                    class="flex w-full cursor-pointer items-center gap-2 px-4 py-3 text-left text-sm text-ipv-ink/80 transition-all duration-200 hover:bg-emerald-500 hover:text-white dark:text-ipv-ink-dark/85">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                    </svg>
                                                    Realizar pase
                                                </button>
                                                <button wire:click="confirmarBorrado({{ $expediente->id }})" @click="open = false; showBorrar = true"
                                                    class="flex w-full cursor-pointer items-center gap-2 px-4 py-3 text-left text-sm text-ipv-magenta transition-all duration-200 hover:bg-ipv-magenta hover:text-white">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Eliminar expediente
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                                    No se encontraron expedientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <div class="mt-4">
            {{ $expedientes->links() }}
        </div>

        @include('livewire.modal-filtros')
        @include('livewire.Expedientes.modal-NuevoExpediente')
        @include('livewire.Expedientes.modal-EditarExpediente')
        @include('livewire.Expedientes.modal-RealizarPase')
        @include('livewire.modal-ConfirmarBorrado')
    @endif
</div>
