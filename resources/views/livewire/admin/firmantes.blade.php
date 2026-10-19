@php
    $entrada = 'w-full rounded-lg border border-ipv-blue/20 bg-white/80 px-3 py-2 text-sm text-ipv-ink dark:border-white/15 dark:bg-white/[0.06] dark:text-ipv-ink-dark';
@endphp
<div class="flex flex-col gap-4">
    <form wire:submit="guardar" class="sirex-glass-card grid gap-3 rounded-[14px] p-4 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
        <div class="md:col-span-4">
            <h2 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $editandoId ? 'Editar firmante' : 'Agregar firmante' }}</h2>
            <p class="text-sm text-ipv-ink/55 dark:text-ipv-ink-dark/55">Son los que aparecen para elegir al pie de cada resolución. Se escriben tal como salen impresos.</p>
        </div>
        <label class="flex flex-col gap-1 text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
            Nombre
            <input type="text" wire:model="nombre" placeholder="Arq. HUGO CABRAL" class="{{ $entrada }}" />
            @error('nombre') <span class="text-xs font-normal text-ipv-magenta">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-1 text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
            Cargo
            <input type="text" wire:model="cargo" placeholder="INTERVENTOR" class="{{ $entrada }}" />
            @error('cargo') <span class="text-xs font-normal text-ipv-magenta">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-1 text-sm font-medium text-ipv-ink/75 dark:text-ipv-ink-dark/80">
            Dependencia <span class="text-xs font-normal opacity-60">(opcional)</span>
            <input type="text" wire:model="dependencia" placeholder="I.P.V. y D.U." class="{{ $entrada }}" />
        </label>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-ipv-blue px-4 py-2 text-sm font-semibold text-white hover:bg-ipv-blue-dark">{{ $editandoId ? 'Guardar cambios' : 'Agregar' }}</button>
            @if ($editandoId)
                <button type="button" wire:click="cancelar" class="rounded-lg border border-ipv-blue/20 px-4 py-2 text-sm font-medium text-ipv-ink/80 dark:border-white/15 dark:text-ipv-ink-dark/85">Cancelar</button>
            @endif
        </div>
    </form>

    <ul class="sirex-glass-card divide-y divide-ipv-blue/10 rounded-[14px] dark:divide-white/10">
        @forelse ($firmantes as $f)
            <li wire:key="firmante-{{ $f->id }}" class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 {{ $f->activo ? '' : 'opacity-55' }}">
                <div class="min-w-0">
                    <div class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $f->nombre }}
                        @unless ($f->activo) <span class="ml-1 rounded-full bg-black/10 px-2 py-0.5 text-xs font-medium dark:bg-white/10">Inactivo</span> @endunless
                    </div>
                    <div class="text-sm text-ipv-ink/65 dark:text-ipv-ink-dark/65">{{ $f->cargo }}{{ $f->dependencia ? ' · '.$f->dependencia : '' }}</div>
                </div>
                <div class="flex items-center gap-1.5 text-sm">
                    <button type="button" wire:click="mover({{ $f->id }}, -1)" @disabled($loop->first) title="Subir en la lista" aria-label="Subir en la lista" class="rounded-lg px-2 py-1 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/10">↑</button>
                    <button type="button" wire:click="mover({{ $f->id }}, 1)" @disabled($loop->last) title="Bajar en la lista" aria-label="Bajar en la lista" class="rounded-lg px-2 py-1 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/10">↓</button>
                    <button type="button" wire:click="editar({{ $f->id }})" class="rounded-lg px-2 py-1 font-medium text-ipv-blue hover:underline dark:text-ipv-blue-light">Editar</button>
                    <button type="button" wire:click="alternarActivo({{ $f->id }})" class="rounded-lg px-2 py-1 font-medium text-ipv-ink/70 hover:underline dark:text-ipv-ink-dark/70">{{ $f->activo ? 'Desactivar' : 'Activar' }}</button>
                </div>
            </li>
        @empty
            <li class="px-4 py-10 text-center text-ipv-ink/55 dark:text-ipv-ink-dark/55">Todavía no hay firmantes. Agregá el primero arriba.</li>
        @endforelse
    </ul>
</div>
