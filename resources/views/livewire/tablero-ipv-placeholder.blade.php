<div class="grid gap-4 lg:grid-cols-3" aria-busy="true" aria-label="Cargando tablero">
    <div class="space-y-4 lg:col-span-2">
        <div class="grid grid-cols-3 gap-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="sirex-glass-card space-y-2 rounded-[14px] p-4">
                    <div class="sirex-skeleton h-3 w-2/3 rounded"></div>
                    <div class="sirex-skeleton h-7 w-1/2 rounded"></div>
                </div>
            @endfor
        </div>
        <x-skeleton.tabla :filas="5" :columnas="3" />
    </div>
    <x-skeleton.tabla :filas="5" :columnas="2" />
</div>
