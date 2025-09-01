{{-- resources/views/components/permiso-card.blade.php --}}
@props(['permiso', 'title', 'description', 'icon'])

<label x-data="{ checked: @entangle('permisos.' . $permiso) }"
    :class="checked
        ?
        'bg-blue-100 border-blue-500 ring-2 ring-blue-500 text-blue-800 dark:bg-blue-900/50 dark:border-blue-400 dark:ring-blue-400 dark:text-blue-200' :
        'bg-gray-100 border-gray-300 hover:bg-gray-200 dark:bg-zinc-800 dark:border-zinc-700 dark:hover:bg-zinc-700'"
    class="flex flex-col items-center justify-center p-4 border rounded-lg shadow-sm cursor-pointer transition-all duration-300 hover:scale-105 group">

    <input type="checkbox" x-model="checked" wire:model.defer="permisos.{{ $permiso }}" id="{{ $permiso }}"
        class="sr-only" aria-describedby="{{ $permiso }}_desc">

    <!-- Icono dinámico -->
    <div class="relative mb-3">
        <div class="absolute -inset-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full opacity-0 group-hover:opacity-20 transition-opacity duration-300"
            x-show="checked"></div>
        <svg class="size-8 relative z-10 transition-transform duration-200 group-hover:scale-110" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>
    </div>

    <span class="font-semibold text-center mb-1">{{ $title }}</span>
    <p id="{{ $permiso }}_desc"
        class="text-sm text-center text-gray-500 dark:text-gray-400 transition-colors duration-200">
        {{ $description }}
    </p>

    <!-- Indicador de estado -->
    <div class="mt-2 flex items-center gap-2">
        <div class="w-2 h-2 rounded-full transition-colors duration-200"
            :class="checked ? 'bg-blue-500' : 'bg-gray-300'"></div>
        <span class="text-xs font-medium" :class="checked ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'"
            x-text="checked ? 'Activo' : 'Inactivo'"></span>
    </div>
</label>
