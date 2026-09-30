{{-- Placeholder de la ficha de expediente mientras consulta el sistema IPV. --}}
<div class="space-y-4" aria-busy="true" aria-label="Cargando expediente">
    <div class="sirex-skeleton h-8 w-64 rounded-lg"></div>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="sirex-glass-card space-y-3 rounded-[14px] p-5 lg:col-span-2">
            @for ($i = 0; $i < 5; $i++)
                <div class="sirex-skeleton h-4 rounded" style="width: {{ 90 - $i * 12 }}%"></div>
            @endfor
        </div>
        <div class="sirex-glass-card space-y-3 rounded-[14px] p-5">
            <div class="sirex-skeleton h-4 w-1/2 rounded"></div>
            <div class="sirex-skeleton h-10 rounded-lg"></div>
        </div>
    </div>
    <div class="sirex-glass-card space-y-4 rounded-[14px] p-5">
        @for ($i = 0; $i < 4; $i++)
            <div class="flex items-center gap-3">
                <div class="sirex-skeleton size-3 rounded-full"></div>
                <div class="sirex-skeleton h-4 flex-1 rounded"></div>
            </div>
        @endfor
    </div>
</div>
