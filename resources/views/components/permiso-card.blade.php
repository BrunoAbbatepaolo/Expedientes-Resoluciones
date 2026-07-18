{{-- resources/views/components/permiso-card.blade.php --}}
@props(['permiso', 'title', 'description', 'icon'])

<label x-data="{ checked: @entangle('permisos.' . $permiso) }"
    :class="checked
        ?
        'bg-ipv-blue/10 border-ipv-blue ring-2 ring-ipv-blue/40 text-ipv-blue dark:bg-ipv-blue-light/15 dark:border-ipv-blue-light dark:ring-ipv-blue-light/30 dark:text-ipv-blue-light' :
        'bg-white/40 border-ipv-blue/15 hover:bg-white/70 dark:bg-white/[0.04] dark:border-white/10 dark:hover:bg-white/[0.08]'"
    class="flex flex-col items-center justify-center p-4 border rounded-lg shadow-sm cursor-pointer transition-all duration-300 hover:scale-105 group">

    <input type="checkbox" x-model="checked" wire:model.defer="permisos.{{ $permiso }}" id="{{ $permiso }}"
        class="sr-only" aria-describedby="{{ $permiso }}_desc">

    <!-- Icono dinámico -->
    <div class="relative mb-3">
        <div class="absolute -inset-2 rounded-full bg-ipv-gold opacity-0 transition-opacity duration-300 group-hover:opacity-20"
            x-show="checked"></div>
        <svg class="size-8 relative z-10 transition-transform duration-200 group-hover:scale-110" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>
    </div>

    <span class="mb-1 text-center font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $title }}</span>
    <p id="{{ $permiso }}_desc"
        class="text-center text-sm text-ipv-ink/50 transition-colors duration-200 dark:text-ipv-ink-dark/50">
        {{ $description }}
    </p>

    <!-- Indicador de estado -->
    <div class="mt-2 flex items-center gap-2">
        <div class="h-2 w-2 rounded-full transition-colors duration-200"
            :class="checked ? 'bg-ipv-blue dark:bg-ipv-blue-light' : 'bg-gray-300'"></div>
        <span class="text-xs font-medium" :class="checked ? 'text-ipv-blue dark:text-ipv-blue-light' : 'text-ipv-ink/40 dark:text-ipv-ink-dark/40'"
            x-text="checked ? 'Activo' : 'Inactivo'"></span>
    </div>
</label>
