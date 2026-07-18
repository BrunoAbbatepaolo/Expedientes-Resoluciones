<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Apariencia')" :subheading="__('Actualizá la configuración de apariencia de tu cuenta')">
        <div x-data class="inline-flex rounded-lg border border-ipv-blue/15 bg-white/70 p-1 dark:border-white/15 dark:bg-white/[0.06]">
            @foreach ([
                'light' => __('Claro'),
                'dark' => __('Oscuro'),
                'system' => __('Sistema'),
            ] as $value => $label)
                <button type="button" @click="$flux.appearance = '{{ $value }}'"
                    :class="$flux.appearance === '{{ $value }}' ? 'bg-ipv-blue text-white' : 'text-ipv-ink/70 dark:text-ipv-ink-dark/75'"
                    class="rounded-md px-3.5 py-1.5 text-sm font-medium transition-colors">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </x-settings.layout>
</div>
