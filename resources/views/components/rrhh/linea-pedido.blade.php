@props(['estado'])

{{-- Línea de tiempo del pedido: empleado → jefe → RRHH. Cada paso hecho se llena; el actual late. --}}
@php
    use App\Enums\EstadoPedido;

    // Avance 0-3: 1 = pedido enviado, 2 = aprobó el jefe, 3 = confirmó RRHH.
    $avance = match ($estado) {
        EstadoPedido::PendienteJefe => 1,
        EstadoPedido::AprobadoJefe => 2,
        EstadoPedido::Confirmado => 3,
        EstadoPedido::RechazadoJefe => 1,
        EstadoPedido::RechazadoRrhh => 2,
        EstadoPedido::Cancelado => 1,
    };
    $rechazadoEn = match ($estado) {
        EstadoPedido::RechazadoJefe => 2,
        EstadoPedido::RechazadoRrhh => 3,
        default => null,
    };
    $actual = ($estado === EstadoPedido::PendienteJefe || $estado === EstadoPedido::AprobadoJefe) ? $avance + 1 : null;
    $pasos = [1 => 'Pedido', 2 => 'Jefe', 3 => 'RRHH'];
@endphp
<ol class="sirex-linea" aria-label="Avance del pedido">
    @foreach ($pasos as $n => $nombre)
        @php
            $estadoPaso = $rechazadoEn === $n ? 'no' : ($n <= $avance ? 'ok' : ($actual === $n ? 'ahora' : 'falta'));
        @endphp
        <li class="sirex-linea-paso" data-estado="{{ $estadoPaso }}" style="--i: {{ $n }}">
            <span class="sirex-linea-punto">
                @if ($estadoPaso === 'ok')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path class="sirex-trazo" d="M5 13l4 4L19 7" /></svg>
                @elseif ($estadoPaso === 'no')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"><path class="sirex-trazo" d="M6 18L18 6M6 6l12 12" /></svg>
                @endif
            </span>
            <span class="sirex-linea-nombre">{{ $nombre }}</span>
        </li>
    @endforeach
</ol>
