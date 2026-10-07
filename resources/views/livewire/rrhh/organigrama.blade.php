<div class="space-y-5">
    <x-rrhh.encabezado titulo="Organigrama"
        subtitulo="De quién depende cada oficina y quiénes son sus jefes. Un jefe ve a la gente de su oficina y de todas las que dependen de ella." />

    <x-rrhh.aviso-error :mostrar="$error" />

    @php($nombres = collect($arbol)->mapWithKeys(fn ($n) => [$n['oficina']->id => $n['oficina']->nombre]))

    @unless ($error)
        <div class="grid gap-4 lg:grid-cols-[1fr_22rem]">
            <ul class="sirex-glass-card divide-y divide-ipv-blue/10 rounded-[14px] text-sm dark:divide-white/10">
                @foreach ($arbol as $nodo)
                    @php($o = $nodo['oficina'])
                    <li wire:key="ofi-{{ $o->id }}"
                        class="flex flex-wrap items-center justify-between gap-2 py-2 pr-3 {{ $editando === $o->id ? 'bg-ipv-blue/5' : '' }}"
                        style="padding-left: {{ 0.75 + $nodo['nivel'] * 1.25 }}rem">
                        <span class="min-w-0">
                            <span class="{{ $nodo['nivel'] === 0 ? 'font-semibold' : '' }} text-ipv-ink dark:text-ipv-ink-dark">{{ trim($o->nombre) }}</span>
                            <span class="text-xs text-ipv-ink/45 dark:text-ipv-ink-dark/45">· {{ $personas[$o->id] ?? 0 }}</span>
                            @foreach ($jefes[$o->id] ?? [] as $j)
                                <span class="ml-1 rounded-full bg-ipv-gold/20 px-2 py-0.5 text-xs text-[#7a5200] dark:text-ipv-gold-light">{{ $j->user?->name }}</span>
                            @endforeach
                        </span>
                        @if ($editable)
                            <button type="button" wire:click="editar({{ $o->id }})" class="shrink-0 text-xs text-ipv-blue hover:underline dark:text-sky-300">Editar</button>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($editable && $editando)
                <aside class="sirex-glass-card h-fit space-y-4 rounded-[14px] p-4 text-sm lg:sticky lg:top-4">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ trim($nombres[$editando] ?? '') }}</h2>
                        <button type="button" wire:click="cerrar" class="text-ipv-ink/50 hover:text-ipv-ink" aria-label="Cerrar">✕</button>
                    </div>

                    <div class="space-y-2">
                        <x-rrhh.select etiqueta="Depende de" wire:model="padreId" vacio="Sin superior" :opciones="$nombres->except($editando)->map(fn ($n) => trim($n))" />
                        @error('padreId') <p class="text-xs text-ipv-magenta">{{ $message }}</p> @enderror
                        <button type="button" wire:click="guardarPadre" class="sirex-btn rounded-lg bg-ipv-blue px-4 py-1.5 font-medium text-white">Guardar dependencia</button>
                    </div>

                    <div class="space-y-2 border-t border-ipv-blue/10 pt-3 dark:border-white/10">
                        <p class="font-medium text-ipv-ink dark:text-ipv-ink-dark">Jefes</p>
                        <ul class="space-y-1">
                            @forelse ($jefes[$editando] ?? [] as $j)
                                <li class="flex items-center justify-between">
                                    <span>{{ $j->user?->name }}</span>
                                    <button type="button" wire:click="quitarJefe({{ $j->id }})" wire:confirm="¿Quitar a {{ $j->user?->name }} como jefe?" class="text-xs text-ipv-magenta hover:underline">Quitar</button>
                                </li>
                            @empty
                                <li class="text-ipv-ink/55 dark:text-ipv-ink-dark/55">Sin jefe asignado.</li>
                            @endforelse
                        </ul>
                        <x-rrhh.select etiqueta="Agregar jefe" wire:model="nuevoJefeId" vacio="Elegir usuario…" :opciones="$usuarios->mapWithKeys(fn ($u) => [$u->id => trim($u->apellido.', '.$u->nombre, ', ')])" />
                        @error('nuevoJefeId') <p class="text-xs text-ipv-magenta">{{ $message }}</p> @enderror
                        <button type="button" wire:click="agregarJefe" class="rounded-lg border border-ipv-blue/20 px-4 py-1.5 font-medium text-ipv-blue dark:border-white/15 dark:text-sky-300">Agregar jefe</button>
                    </div>
                </aside>
            @endif
        </div>
    @endunless
</div>
