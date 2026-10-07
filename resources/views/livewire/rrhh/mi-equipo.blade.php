<div class="space-y-5" @if ($vista === 'hoy') wire:poll.60s @endif>
    <x-rrhh.encabezado titulo="Mi equipo" subtitulo="Asistencia y licencias de tu oficina y de las oficinas que dependen de ella.">
        <label class="block">
            <span class="sr-only">Oficina</span>
            <select wire:model.live="oficinaId"
                class="rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink focus:outline-none focus:ring-2 focus:ring-ipv-blue/30 dark:border-white/15 dark:bg-[#141c26] dark:text-ipv-ink-dark">
                @if ($vista !== 'mes')
                    <option value="">Todas mis oficinas ({{ $oficinas->count() }})</option>
                @endif
                @foreach ($oficinas as $ofi)
                    <option value="{{ $ofi->id }}">{{ $ofi->nombre }}</option>
                @endforeach
            </select>
        </label>
    </x-rrhh.encabezado>

    <nav class="flex gap-1" aria-label="Vistas">
        @foreach (\App\Livewire\Rrhh\MiEquipo::VISTAS as $clave => $etiqueta)
            <button type="button" wire:click="$set('vista', '{{ $clave }}')"
                @if ($vista === $clave) aria-current="page" @endif
                class="rounded-lg px-3 py-1.5 text-sm transition {{ $vista === $clave ? 'bg-ipv-blue font-semibold text-white' : 'text-ipv-ink/70 hover:bg-black/[0.04] dark:text-ipv-ink-dark/70 dark:hover:bg-white/5' }}">
                {{ $etiqueta }}
            </button>
        @endforeach
    </nav>

    <x-rrhh.aviso-error :mostrar="$error" />

    @unless ($error)
        <section class="sirex-glass-card rounded-[14px] p-4" wire:loading.class="opacity-60">
            @if ($vista === 'mes')
                <div class="mb-3 flex justify-end"><x-rrhh.selector-mes :mes="$mesActual" /></div>
                <x-rrhh.grilla :grilla="$grilla ?? ['dias' => [], 'filas' => []]" />
            @elseif ($vista === 'gente')
                <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
                    @forelse ($gente ?? [] as $e)
                        <li wire:key="gente-{{ $e->id }}">
                            <a href="{{ route('rrhh.ficha', $e->id) }}" wire:navigate class="flex flex-wrap items-baseline justify-between gap-2 py-2.5 hover:text-ipv-blue">
                                <span class="font-medium text-ipv-ink dark:text-ipv-ink-dark">{{ $e->apellido }}, {{ $e->nombre }}</span>
                                <span class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Legajo {{ $e->legajo }} · {{ $e->oficinaRrhh?->nombre }} @if ($e->funcion) · {{ $e->funcion }} @endif</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-2 text-ipv-ink/55 dark:text-ipv-ink-dark/55">No hay empleados activos.</li>
                    @endforelse
                </ul>
            @else
                <p class="mb-3 text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Se actualiza solo cada minuto. "Sin fichar" todavía no es una falta: RRHH la registra después.</p>
                <x-rrhh.hoy :filas="$hoy ?? collect()" :agrupar="$oficinaId === null" />
            @endif
        </section>
    @endunless
</div>
