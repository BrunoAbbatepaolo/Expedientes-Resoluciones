<div class="p-6 text-ipv-ink dark:text-ipv-ink-dark">
    <div class="max-w-7xl mx-auto">
        <h1 class="mb-6 text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark md:text-3xl">
            Crear resolución: <span class="text-ipv-blue dark:text-ipv-blue-light">{{ $this->displayName }}</span>
        </h1>

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
                <p class="font-semibold">Revisá lo siguiente:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach (array_unique($errors->all()) as $mensajeError)
                        <li>{{ $mensajeError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (in_array($modo, ['', 'plantilla'], true))
            @include('livewire.partials.autocompletar-resolucion')
        @endif

        @if ($modo === '')
        <div class="mb-4">
            <button type="button" onclick="window.location.href='/resoluciones/elegir'"
                class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </button>
        </div>
        <div class="sirex-glass-card mb-6 rounded-[14px] p-6">
            <h2 class="mb-4 text-xl font-semibold text-ipv-ink dark:text-ipv-ink-dark">Seleccione el modo de creación</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="button" wire:click="usarPlantilla"
                    class="flex flex-1 flex-col items-center justify-center gap-1 rounded-lg bg-ipv-blue px-4 py-3 font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    <span class="inline-flex items-center gap-2">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Plantilla completa
                    </span>
                    <span class="text-xs font-normal opacity-80">Completás los datos directamente en el documento</span>
                </button>
                <button type="button" wire:click="usarPersonalizado"
                    class="flex flex-1 flex-col items-center justify-center gap-1 rounded-lg border border-ipv-blue/20 bg-white/70 px-4 py-3 font-medium text-ipv-ink/80 transition-colors hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                    <span class="inline-flex items-center gap-2">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Personalizado
                    </span>
                    <span class="text-xs font-normal opacity-70">Redactás el texto libremente, como en un Word</span>
                </button>
            </div>
        </div>

        @elseif ($modo === 'plantilla')
        <div class="min-h-screen bg-black/[0.02] dark:bg-white/[0.02] -mx-6 -my-6 p-6">
            <div class="sirex-glass-card mb-6 -mx-6 flex flex-wrap items-center justify-between gap-3 rounded-lg px-6 py-3">
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="$set('modo', '')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </button>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        Completá los datos resaltados en el documento
                    </span>
                </div>
                @include('livewire.partials.campo-expediente-barra')
                {{-- La pestaña del PDF se abre al volver la respuesta; si el navegador la bloquea, se abre en esta. --}}
                <button type="button" x-data="{ guardando: false }" x-bind:disabled="guardando"
                    x-on:click="
                        guardando = true;
                        $wire.guardarPlantilla().then((url) => {
                            if (! url) { guardando = false; return; }
                            if (! window.open(url, '_blank')) { window.location.href = url; return; }
                            window.location.href = @js(route('resoluciones'));
                        }).catch(() => { guardando = false; });
                    "
                    class="sirex-btn rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark disabled:cursor-wait disabled:opacity-60">
                    <span x-show="! guardando">Guardar y generar PDF</span>
                    <span x-show="guardando" x-cloak>Guardando…</span>
                </button>
            </div>

            {{-- wire:ignore: lo tipeado vive en $wire.datos y los re-renderizados no pisan el documento.
                 La key cambia al quitar/restaurar artículos para que ahí sí se vuelva a dibujar. --}}
            <div class="sirex-glass-card mx-auto overflow-auto rounded-[14px]">
                <div class="p-2" wire:ignore wire:key="documento-{{ $tipo }}-{{ implode('-', $articulosQuitados) ?: 'todos' }}">
                    {!! $this->documentoEditable() !!}
                </div>
            </div>

            @include('livewire.partials.adjuntos-resolucion')
        </div>

        @elseif ($modo === 'personalizado')
        <div class="min-h-screen bg-black/[0.02] dark:bg-white/[0.02] -mx-6 -my-6 p-6">
            <div class="sirex-glass-card mb-6 -mx-6 flex flex-wrap items-center justify-between gap-3 rounded-lg px-6 py-3">
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="$set('modo', '')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </button>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        Editá el documento como si fuera un Word
                    </span>
                </div>
                @include('livewire.partials.campo-expediente-barra')
                <button type="button" wire:click="guardarPersonalizado"
                    class="sirex-btn rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark">
                    Guardar resolución
                </button>
            </div>

            <div class="sirex-glass-card mx-auto max-w-3xl overflow-hidden rounded-[14px]">
                <div wire:ignore
                     x-data="quillInit()"
                     class="[&_.ql-toolbar.ql-snow]:!border-zinc-200 dark:[&_.ql-toolbar.ql-snow]:!border-zinc-700 dark:[&_.ql-snow_.ql-toolbar]:!bg-zinc-800 dark:[&_.ql-snow_.ql-stroke]:!stroke-zinc-400 dark:[&_.ql-snow_.ql-fill]:!fill-zinc-400 dark:[&_.ql-snow_.ql-picker]:!text-zinc-400 dark:[&_.ql-snow_.ql-picker-options]:!bg-zinc-700 [&_.ql-editor]:!font-serif [&_.ql-editor]:!text-base [&_.ql-editor]:!leading-relaxed [&_.ql-editor]:!p-12 [&_.ql-editor]:!text-justify [&_.ql-container]:!border-none">
                    <div data-quill-editor></div>
                </div>
            </div>
        </div>

    @endif
    </div>
</div>
