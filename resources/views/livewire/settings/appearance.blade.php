<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Apariencia')" :subheading="__('Actualizá la configuración de apariencia de tu cuenta')">
        {{-- Tres tarjetas con una vista previa de cada tema; la elegida lleva el tilde. Se guarda en este navegador. --}}
        <div x-data="{ tema: temaActual() }" x-on:theme-changed.window="tema = $event.detail.tema"
            role="radiogroup" aria-label="Tema" class="grid gap-3 sm:grid-cols-3">
            @foreach ([
                'light' => [__('Claro'), 'Fondo blanco, ideal con mucha luz'],
                'dark' => [__('Oscuro'), 'Descansa la vista de noche'],
                'system' => [__('Sistema'), 'Sigue el tema de tu dispositivo'],
            ] as $value => [$label, $detalle])
                <button type="button" role="radio" @click="aplicarTema('{{ $value }}')"
                    :aria-checked="(tema === '{{ $value }}').toString()"
                    :class="tema === '{{ $value }}' ? 'border-ipv-blue ring-2 ring-ipv-blue/30 dark:border-ipv-blue-light dark:ring-ipv-blue-light/30' : 'border-ipv-blue/15 hover:border-ipv-blue/40 dark:border-white/12 dark:hover:border-white/30'"
                    class="group relative flex flex-col gap-2.5 rounded-xl border bg-white/50 p-2.5 text-left transition dark:bg-white/[0.03]">

                    {{-- Vista previa --}}
                    <span class="relative block h-20 overflow-hidden rounded-lg border border-black/10 dark:border-white/10" aria-hidden="true">
                        @if ($value === 'system')
                            <span class="absolute inset-0 bg-[#f4f7fb]"></span>
                            <span class="absolute inset-y-0 right-0 w-1/2 bg-[#0f1720]"></span>
                        @else
                            <span class="absolute inset-0 {{ $value === 'dark' ? 'bg-[#0f1720]' : 'bg-[#f4f7fb]' }}"></span>
                        @endif
                        <span class="absolute inset-y-0 left-0 w-1/4 {{ $value === 'dark' ? 'bg-[#141c26]' : 'bg-white' }} {{ $value === 'system' ? 'bg-white' : '' }}"></span>
                        <span class="absolute left-[32%] top-2.5 h-2 w-1/3 rounded-full {{ $value === 'dark' ? 'bg-white/70' : 'bg-[#1f2937]/70' }} {{ $value === 'system' ? 'bg-[#1f2937]/70' : '' }}"></span>
                        <span class="absolute left-[32%] top-7 h-6 w-[58%] rounded-md {{ $value === 'dark' ? 'bg-white/10' : 'bg-white shadow-sm' }} {{ $value === 'system' ? 'bg-white/90 shadow-sm' : '' }}"></span>
                        <span class="absolute bottom-2 left-[32%] h-2 w-8 rounded-full bg-ipv-gold"></span>
                        <span class="absolute left-2 top-2.5 h-2 w-2 rounded bg-ipv-gold"></span>
                    </span>

                    <span class="flex items-start justify-between gap-2 px-0.5">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $label }}</span>
                            <span class="block text-[11.5px] leading-snug text-ipv-ink/55 dark:text-ipv-ink-dark/55">{{ $detalle }}</span>
                        </span>
                        <span x-show="tema === '{{ $value }}'" x-cloak
                            class="sirex-chip-icono flex size-5 shrink-0 items-center justify-center rounded-full bg-ipv-blue text-white dark:bg-ipv-blue-light dark:text-ipv-blue-dark">
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path class="sirex-trazo" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    </span>
                </button>
            @endforeach
        </div>
    </x-settings.layout>
</div>
