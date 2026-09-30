@props(['filas' => 8, 'columnas' => 5])

{{-- Placeholder mientras carga un listado (ver #[Lazy] y wire:loading). --}}
<div {{ $attributes->merge(['class' => 'sirex-glass-card overflow-hidden rounded-[14px]']) }} aria-busy="true" aria-label="Cargando">
    <div class="flex gap-4 border-b border-ipv-blue/10 px-4 py-3 dark:border-white/10">
        @for ($c = 0; $c < $columnas; $c++)
            <div class="sirex-skeleton h-3 flex-1 rounded"></div>
        @endfor
    </div>
    @for ($f = 0; $f < $filas; $f++)
        <div class="flex gap-4 px-4 py-3.5">
            @for ($c = 0; $c < $columnas; $c++)
                <div class="sirex-skeleton h-3.5 flex-1 rounded" style="animation-delay: {{ ($f * 60) % 600 }}ms"></div>
            @endfor
        </div>
    @endfor
</div>
