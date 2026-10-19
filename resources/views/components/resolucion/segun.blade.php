{{-- Texto fijo que cambia según haya o no un dato (p. ej. "los Señores" con cotitular, "el/la Señor/a" sin). --}}
@aware(['editable' => false, 'datos' => []])
@props(['si', 'con', 'sin'])
@if ($editable)
    <span x-text="$wire.datos['{{ $si }}'] ? @js($con) : @js($sin)">{{ trim((string) ($datos[$si] ?? '')) !== '' ? $con : $sin }}</span>@else{{ trim((string) ($datos[$si] ?? '')) !== '' ? $con : $sin }}@endif
