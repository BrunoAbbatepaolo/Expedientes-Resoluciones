<div class="space-y-5">
    <x-rrhh.encabezado titulo="Reportes" subtitulo="Se abren en otra pestaña, listos para imprimir o guardar como PDF. También se pueden bajar en Excel (CSV)." />

    <x-rrhh.aviso-error :mostrar="$error" />

    @php
        $entrada = 'w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark';
        $boton = 'sirex-btn inline-flex rounded-lg bg-ipv-blue px-4 py-1.5 text-sm font-medium text-white';
        $botonCsv = 'inline-flex rounded-lg border border-ipv-blue/20 px-4 py-1.5 text-sm font-medium text-ipv-blue dark:border-white/15 dark:text-sky-300';
        $url = fn ($reporte, $params, $csv = false) => route('rrhh.reportes.ver', ['reporte' => $reporte] + array_filter($params) + ($csv ? ['formato' => 'csv'] : []));
    @endphp

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Ficha personal</h2>
            <input type="text" inputmode="numeric" wire:model.live.debounce.300ms="legajo" placeholder="Legajo" class="{{ $entrada }}" />
            @if (ctype_digit($legajo))
                <a href="{{ $url('ficha', ['legajo' => $legajo]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
            @endif
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Presentismo mensual por oficina</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaPresentismo" vacio="Elegí una oficina" :opciones="$oficinas" />
                <input type="month" wire:model.live="mes" class="{{ $entrada }}" />
            </div>
            @if ($oficinaPresentismo)
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $url('presentismo', ['oficina' => $oficinaPresentismo, 'mes' => $mes]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                    <a href="{{ $url('presentismo', ['oficina' => $oficinaPresentismo, 'mes' => $mes], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
                </div>
            @endif
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Vacaciones adeudadas</h2>
            <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaAdeudadas" vacio="Todas las oficinas" :opciones="$oficinas" />
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('adeudadas', ['oficina' => $oficinaAdeudadas]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('adeudadas', ['oficina' => $oficinaAdeudadas], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Licencias por tipo</h2>
            <div class="grid gap-2 sm:grid-cols-3">
                <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaLicencias" vacio="Todas las oficinas" :opciones="$oficinas" />
                <input type="date" wire:model.live="desde" class="{{ $entrada }}" title="Desde" />
                <input type="date" wire:model.live="hasta" class="{{ $entrada }}" title="Hasta" />
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('licencias', ['oficina' => $oficinaLicencias, 'desde' => $desde, 'hasta' => $hasta]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('licencias', ['oficina' => $oficinaLicencias, 'desde' => $desde, 'hasta' => $hasta], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>
        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Inasistencias por empleado</h2>
            <div class="grid gap-2 sm:grid-cols-3">
                <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaInasistencias" vacio="Todas las oficinas" :opciones="$oficinas" />
                <input type="date" wire:model.live="desde" class="{{ $entrada }}" title="Desde" />
                <input type="date" wire:model.live="hasta" class="{{ $entrada }}" title="Hasta" />
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('inasistencias', ['oficina' => $oficinaInasistencias, 'desde' => $desde, 'hasta' => $hasta]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('inasistencias', ['oficina' => $oficinaInasistencias, 'desde' => $desde, 'hasta' => $hasta], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Cumpleaños del mes</h2>
            <select wire:model.live="mesCumple" class="{{ $entrada }} dark:bg-[#141c26]" aria-label="Mes">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ ucfirst(\Carbon\Carbon::create(2000, $m, 1)->locale('es')->translatedFormat('F')) }}</option>
                @endforeach
            </select>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('cumpleanios', ['mes' => $mesCumple]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('cumpleanios', ['mes' => $mesCumple], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Antigüedad del personal</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                <x-rrhh.select etiqueta="Situación laboral" wire:model.live="situacionAntiguedad" vacio="Todas las situaciones" :opciones="$situaciones" />
                <input type="date" wire:model.live="al" class="{{ $entrada }}" title="Al día" />
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('antiguedad', ['situacion' => $situacionAntiguedad, 'al' => $al]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('antiguedad', ['situacion' => $situacionAntiguedad, 'al' => $al], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>

        <section class="sirex-glass-card space-y-3 rounded-[14px] p-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">Listado de personal</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                <x-rrhh.select etiqueta="Oficina" wire:model.live="oficinaPersonal" vacio="Todas las oficinas" :opciones="$oficinas" />
                <select wire:model.live="estadoPersonal" class="{{ $entrada }} dark:bg-[#141c26]" aria-label="Estado">
                    <option value="activos">Activos</option>
                    <option value="pasivos">Pasivos</option>
                    <option value="todos">Todos</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $url('personal', ['oficina' => $oficinaPersonal, 'estado' => $estadoPersonal]) }}" target="_blank" class="{{ $boton }}">Ver / imprimir</a>
                <a href="{{ $url('personal', ['oficina' => $oficinaPersonal, 'estado' => $estadoPersonal], true) }}" class="{{ $botonCsv }}">Excel (CSV)</a>
            </div>
        </section>
    </div>
</div>
