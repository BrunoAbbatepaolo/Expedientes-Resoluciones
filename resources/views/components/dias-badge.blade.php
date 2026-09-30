@props(['dias' => null])

{{-- Días que lleva un expediente en la oficina actual: verde < 15, ámbar < 30, magenta >= 30. --}}
@if ($dias === null)
    <span class="text-xs text-ipv-ink/40 dark:text-ipv-ink-dark/40">—</span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums '.match (true) {
        $dias >= 30 => 'bg-ipv-magenta/12 text-ipv-magenta dark:text-ipv-magenta-light',
        $dias >= 15 => 'bg-ipv-gold/20 text-ipv-gold-ink dark:text-ipv-gold-light',
        default => 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300',
    }]) }}
        title="{{ $dias }} días en la oficina actual">
        {{ $dias === 0 ? 'hoy' : $dias.' '.($dias === 1 ? 'día' : 'días') }}
    </span>
@endif
