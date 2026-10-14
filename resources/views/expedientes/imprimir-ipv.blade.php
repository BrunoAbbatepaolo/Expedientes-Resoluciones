@php($exp = $ficha['expediente'])
<x-documento :titulo="'N° '.$exp->numero" tipo="Expediente" :subtitulo="'Iniciado el '.($exp->fecha?->format('d/m/Y') ?? '—')" :firmas="true">
    <h2>Carátula</h2>
    <dl>
        <dt>Causante</dt><dd>{{ $exp->causante?->nombre ?? '—' }} @if ($exp->causante?->tipo) ({{ $exp->causante->tipo->nombre }}) @endif</dd>
        <dt>Asunto</dt><dd>{{ $exp->tipoAsunto?->nombre }} {{ $exp->asunto }}</dd>
        <dt>Folios / tomo</dt><dd>{{ $exp->folio ?? '—' }} / {{ $exp->tomo ?? '—' }}</dd>
        @if ($exp->resolucion)<dt>Resolución</dt><dd>{{ $exp->resolucion }}</dd>@endif
        @if ($exp->observacion)<dt>Observación</dt><dd>{{ $exp->observacion }}</dd>@endif
        <dt>Ubicación actual</dt><dd>{{ $ficha['ubicacion']->oficina ?? 'Sin pases' }}</dd>
    </dl>

    @foreach (['cabeceras' => 'Agregado a', 'agregados' => 'Expedientes agregados'] as $clave => $titulo)
        @if ($ficha[$clave]->isNotEmpty())
            <h2>{{ $titulo }}</h2>
            <table>
                <thead><tr><th>N°</th><th>Causante</th><th>Asunto</th></tr></thead>
                <tbody>
                    @foreach ($ficha[$clave] as $adj)
                        <tr><td>{{ str_repeat('— ', $adj->nivel - 1) }}{{ $adj->numero }}</td><td>{{ $adj->causante }}</td><td>{{ $adj->asunto }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <h2>Historial de pases</h2>
    <table>
        <thead><tr><th>#</th><th>Fecha</th><th>Oficina</th><th>Pasó</th><th>Folios</th><th>Días</th><th>Observación</th></tr></thead>
        <tbody>
            @forelse ($ficha['pases'] as $pase)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pase->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td>{{ $pase->oficina }}</td>
                    <td>{{ $pase->empleado }}</td>
                    <td>{{ $pase->folio }}</td>
                    <td>{{ $pase->dias }}</td>
                    <td>{{ $pase->observacion }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Sin pases registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-documento>
