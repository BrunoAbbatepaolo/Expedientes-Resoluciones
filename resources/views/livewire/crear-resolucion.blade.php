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
                    <span class="text-xs font-normal opacity-70">El mismo documento, pero podés cambiar todo el texto</span>
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
                    {{-- Guía: cuántos datos obligatorios faltan y, al tocar, lleva al siguiente. --}}
                    <div x-data="{
                            obligatorios: @js($this->obligatorios()),
                            get faltan() { return this.obligatorios.filter((c) => String(this.$wire.datos[c] ?? '').trim() === ''); },
                            irAlSiguiente() {
                                const el = this.faltan.map((c) => document.querySelector(`[data-campo='${c}']`)).find(Boolean);
                                if (! el) return;
                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                el.focus({ preventScroll: true });
                            },
                        }" class="text-sm">
                        <button type="button" x-show="faltan.length" x-on:click="irAlSiguiente()"
                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-900 hover:bg-amber-200 dark:bg-amber-400/15 dark:text-amber-200 dark:hover:bg-amber-400/25">
                            Faltan <span x-text="faltan.length"></span> <span x-text="faltan.length === 1 ? 'dato obligatorio' : 'datos obligatorios'"></span>
                            <span class="underline">Ir al siguiente</span>
                        </button>
                        <span x-show="! faltan.length" x-cloak class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-200">
                            ✓ Datos obligatorios completos
                        </span>
                    </div>
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
            {{-- Si al guardar falta algo, esos campos se marcan en rojo y se va al primero. --}}
            <div class="sirex-glass-card mx-auto overflow-auto rounded-[14px]"
                x-data
                x-on:campos-con-error.window="
                    $el.querySelectorAll('.campo-con-error').forEach((e) => e.classList.remove('campo-con-error'));
                    ($event.detail.campos ?? []).forEach((c) => $el.querySelectorAll(`[data-campo='${c}']`).forEach((e) => e.classList.add('campo-con-error')));
                    const primero = $el.querySelector('.campo-con-error');
                    if (primero) { primero.scrollIntoView({ behavior: 'smooth', block: 'center' }); primero.focus({ preventScroll: true }); }
                "
                x-on:input="$event.target.classList.remove('campo-con-error')"
                x-on:change="$event.target.classList.remove('campo-con-error')">
                <div class="p-2" wire:ignore wire:key="documento-{{ $tipo }}-{{ implode('-', $articulosQuitados) ?: 'todos' }}">
                    {!! $this->documentoEditable() !!}
                </div>
            </div>

            @include('livewire.partials.adjuntos-resolucion')
        </div>

        @elseif ($modo === 'personalizado')
        <div class="min-h-screen bg-black/[0.02] dark:bg-white/[0.02] -mx-6 -my-6 p-6"
            x-data="{
                marcadores: 0,
                guardando: false,
                cambios: false,
                volver() {
                    if (this.cambios && ! confirm('Si volvés se pierde lo que escribiste en el documento. ¿Volver igual?')) return;
                    $wire.set('modo', '');
                },
                contar() { this.marcadores = [...this.$refs.doc.querySelectorAll('.marcador-dato')].filter((e) => /^\s*\[.*\]\s*$/.test(e.textContent)).length; },
                irAlSiguiente() {
                    const m = [...this.$refs.doc.querySelectorAll('.marcador-dato')].find((e) => /^\s*\[.*\]\s*$/.test(e.textContent));
                    if (! m) return;
                    m.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    this.seleccionar(m);
                },
                seleccionar(el) {
                    const rango = document.createRange();
                    rango.selectNodeContents(el);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(rango);
                    this.$refs.doc.focus({ preventScroll: true });
                },
                formato(cmd, valor = null) { this.$refs.doc.focus(); document.execCommand(cmd, false, valor); this.contar(); },
                guardar() {
                    this.guardando = true;
                    $wire.plantilla = this.$refs.doc.innerHTML;
                    $wire.guardarPersonalizado().then((url) => {
                        if (! url) { this.guardando = false; return; }
                        if (! window.open(url, '_blank')) { window.location.href = url; return; }
                        window.location.href = @js(route('resoluciones'));
                    }).catch(() => { this.guardando = false; });
                },
            }"
            x-on:beforeunload.window="if (cambios && ! guardando) { $event.preventDefault(); $event.returnValue = ''; }"
            x-init="
                $nextTick(() => contar());
                // Lo que se escribe en la barra (expediente y N° de resolución) se completa solo en el documento.
                ['num_exp', 'num_res'].forEach((c) => $watch(`$wire.datos.${c}`, (v) => {
                    if (! String(v ?? '').trim()) return;
                    $refs.doc.querySelectorAll(`.dato-${c}`).forEach((e) => { e.textContent = v; e.classList.remove('marcador-dato'); });
                    contar();
                }));
            ">
            <div class="sirex-glass-card mb-4 -mx-6 flex flex-wrap items-center justify-between gap-3 rounded-lg px-6 py-3">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" x-on:click="volver()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-ipv-blue/20 bg-white/70 px-3 py-1.5 text-sm font-medium text-ipv-ink/80 hover:bg-white dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark/85 dark:hover:bg-white/[0.1]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </button>
                    <button type="button" x-show="marcadores" x-on:click="irAlSiguiente()"
                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-900 hover:bg-amber-200 dark:bg-amber-400/15 dark:text-amber-200 dark:hover:bg-amber-400/25">
                        Faltan <span x-text="marcadores"></span> <span x-text="marcadores === 1 ? 'dato' : 'datos'"></span> marcados
                        <span class="underline">Ir al siguiente</span>
                    </button>
                    <span x-show="! marcadores" x-cloak class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-200">✓ Sin datos marcados pendientes</span>
                </div>
                <div class="flex flex-wrap items-start gap-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
                        N° resolución
                        <input type="text" wire:model="datos.num_res" placeholder="Ej. 2108/26"
                            class="w-28 rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-1.5 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/40 dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark">
                    </label>
                    @include('livewire.partials.campo-expediente-barra')
                </div>
                <button type="button" x-on:click="guardar()" x-bind:disabled="guardando"
                    class="sirex-btn rounded-lg bg-ipv-blue px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ipv-blue-dark disabled:cursor-wait disabled:opacity-60">
                    <span x-show="! guardando">Guardar y generar PDF</span>
                    <span x-show="guardando" x-cloak>Guardando…</span>
                </button>
            </div>

            {{-- Barra de formato: siempre visible arriba mientras se escribe. --}}
            <div class="sticky top-2 z-20 mx-auto mb-3 flex w-fit flex-wrap items-center gap-1 rounded-xl border border-ipv-blue/15 bg-white/95 p-1 shadow-md backdrop-blur dark:border-white/10 dark:bg-slate-800/95" role="toolbar" aria-label="Formato del texto">
                @foreach ([['bold', 'Negrita', '<b>N</b>'], ['italic', 'Cursiva', '<i>C</i>'], ['underline', 'Subrayado', '<u>S</u>']] as [$cmd, $titulo, $icono])
                    <button type="button" x-on:mousedown.prevent x-on:click="formato('{{ $cmd }}')" title="{{ $titulo }}" aria-label="{{ $titulo }}"
                        class="grid size-8 place-items-center rounded-lg font-serif text-[15px] text-ipv-ink/80 hover:bg-ipv-blue/10 dark:text-ipv-ink-dark/85 dark:hover:bg-white/10">{!! $icono !!}</button>
                @endforeach
                <span class="mx-1 h-5 w-px bg-ipv-blue/15 dark:bg-white/15" aria-hidden="true"></span>
                @foreach ([['justifyLeft', 'Alinear a la izquierda', 'M4 6h16M4 10h10M4 14h16M4 18h10'], ['justifyCenter', 'Centrar', 'M4 6h16M7 10h10M4 14h16M7 18h10'], ['justifyFull', 'Justificar', 'M4 6h16M4 10h16M4 14h16M4 18h16']] as [$cmd, $titulo, $d])
                    <button type="button" x-on:mousedown.prevent x-on:click="formato('{{ $cmd }}')" title="{{ $titulo }}" aria-label="{{ $titulo }}"
                        class="grid size-8 place-items-center rounded-lg text-ipv-ink/80 hover:bg-ipv-blue/10 dark:text-ipv-ink-dark/85 dark:hover:bg-white/10">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $d }}" /></svg>
                    </button>
                @endforeach
                <span class="mx-1 h-5 w-px bg-ipv-blue/15 dark:bg-white/15" aria-hidden="true"></span>
                <button type="button" x-on:mousedown.prevent x-on:click="formato('undo')" title="Deshacer (Ctrl+Z)" aria-label="Deshacer"
                    class="grid size-8 place-items-center rounded-lg text-ipv-ink/80 hover:bg-ipv-blue/10 dark:text-ipv-ink-dark/85 dark:hover:bg-white/10">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14L4 9l5-5" /><path d="M4 9h10.5a5.5 5.5 0 010 11H11" /></svg>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="formato('redo')" title="Rehacer (Ctrl+Y)" aria-label="Rehacer"
                    class="grid size-8 place-items-center rounded-lg text-ipv-ink/80 hover:bg-ipv-blue/10 dark:text-ipv-ink-dark/85 dark:hover:bg-white/10">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 14l5-5-5-5" /><path d="M20 9H9.5a5.5 5.5 0 000 11H13" /></svg>
                </button>
                <span class="hidden px-2 text-xs text-ipv-ink/50 sm:inline dark:text-ipv-ink-dark/50">Hacé clic en cualquier texto para cambiarlo</span>
            </div>

            {{-- El documento de la plantilla con todo el texto editable. Los marcadores [ASÍ] se seleccionan con un clic. --}}
            <div class="sirex-glass-card mx-auto overflow-auto rounded-[14px]">
                <div class="p-2" wire:ignore>
                    <style>
                        .doc-personalizado .marcador-dato { background: #fef3c7; color: #92400e; border-radius: 3px; padding: 0 2px; box-shadow: inset 0 -1.5px 0 #f59e0b; cursor: pointer; }
                        .doc-personalizado [contenteditable]:focus { outline: none; }
                        .doc-personalizado .documento-resolucion .sheet { width: 100%; max-width: 1200px; }
                        @media (max-width: 640px) { .doc-personalizado .documento-resolucion { padding: 12px !important; } }
                    </style>
                    {!! $this->estilosPersonalizado() !!}
                    <div class="doc-personalizado">
                        <div x-ref="doc" contenteditable="true" spellcheck="true" lang="es"
                            x-on:input="
                                const m = window.getSelection().anchorNode?.parentElement?.closest('.marcador-dato');
                                if (m && ! /^\s*\[.*\]\s*$/.test(m.textContent)) m.classList.remove('marcador-dato');
                                cambios = true;
                                contar();
                            "
                            x-on:click="if ($event.target.classList.contains('marcador-dato')) seleccionar($event.target)">{!! \Mews\Purifier\Facades\Purifier::clean($plantilla, 'resolucion') !!}</div>
                    </div>
                </div>
            </div>

            @include('livewire.partials.adjuntos-resolucion')
        </div>

    @endif
    </div>
</div>
