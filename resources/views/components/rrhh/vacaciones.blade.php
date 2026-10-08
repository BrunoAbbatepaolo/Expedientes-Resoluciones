{{-- Vacaciones del período en curso ($v: ver Contracts\Licencias::vacaciones()). --}}
@props(['v', 'detalle' => false])

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
        <div class="rounded-lg bg-black/[0.03] px-3 py-2 dark:bg-white/5">
            <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Corresponden {{ $v->periodo }}</p>
            <p class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $v->corresponden }}</p>
            <p class="text-[11px] text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $v->antiguedad }} {{ $v->antiguedad === 1 ? 'año' : 'años' }} de antigüedad</p>
        </div>
        <div class="rounded-lg bg-black/[0.03] px-3 py-2 dark:bg-white/5">
            <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Tomadas</p>
            <p class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $v->tomadas }}</p>
        </div>
        <div class="rounded-lg bg-black/[0.03] px-3 py-2 dark:bg-white/5">
            <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Anticipadas</p>
            <p class="text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">{{ $v->anticipadas }}</p>
        </div>
        <div class="rounded-lg px-3 py-2 {{ $v->pendientes < 0 ? 'bg-ipv-magenta/10' : 'bg-emerald-500/10' }}">
            <p class="text-xs text-ipv-ink/55 dark:text-ipv-ink-dark/55">Pendientes</p>
            <p class="text-lg font-semibold {{ $v->pendientes < 0 ? 'text-ipv-magenta' : 'text-emerald-800 dark:text-emerald-300' }}">{{ $v->pendientes }}</p>
            @if ($v->pendientes < 0)<p class="text-[11px] text-ipv-magenta">excedido</p>@endif
        </div>
    </div>
    <p class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">Días hábiles. Las vacaciones del período {{ $v->periodo }} se toman durante {{ $v->periodo + (int) config('sirex.rrhh_vacaciones_desfase') }} y vencen al terminar ese año.</p>

    @if ($detalle && $v->detalle->isNotEmpty())
        <ul class="divide-y divide-ipv-blue/10 text-sm dark:divide-white/10">
            @foreach ($v->detalle as $a)
                <li wire:key="vac-{{ $a->id }}" class="flex flex-wrap justify-between gap-2 py-1.5">
                    <span class="text-ipv-ink dark:text-ipv-ink-dark">{{ $a->tipo->descripcion ?? 'Vacaciones' }}</span>
                    <span class="text-ipv-ink/60 dark:text-ipv-ink-dark/60">{{ $a->desde->format('d/m/Y') }} – {{ $a->hasta->format('d/m/Y') }} · {{ $a->dias_habiles }} hábiles</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
