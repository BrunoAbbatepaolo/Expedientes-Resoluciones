@props(['dias' => null])

{{-- Tiempo que lleva un expediente en la oficina actual: verde < 15 días, ámbar < 30, magenta >= 30.
     Se muestra en días, meses o años según la magnitud (el total exacto queda en el title). --}}
@if ($dias === null)
    <span class="text-xs text-ipv-ink/40 dark:text-ipv-ink-dark/40">—</span>
@else
    @php
        $texto = match (true) {
            $dias === 0 => 'hoy',
            $dias === 1 => '1 día',
            $dias < 60 => $dias.' días',
            $dias < 730 => intdiv($dias, 30).' meses',
            default => intdiv($dias, 365).' años',
        };
    @endphp
    <span {{ $attributes->merge(['class' => 'inline-flex items-center whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums '.match (true) {
        $dias >= 30 => 'bg-ipv-magenta/12 text-ipv-magenta dark:text-ipv-magenta-light',
        $dias >= 15 => 'bg-ipv-gold/20 text-ipv-gold-ink dark:text-ipv-gold-light',
        default => 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300',
    }]) }}
        title="{{ number_format($dias, 0, ',', '.') }} días en la oficina actual">
        {{ $texto }}
    </span>
@endif
