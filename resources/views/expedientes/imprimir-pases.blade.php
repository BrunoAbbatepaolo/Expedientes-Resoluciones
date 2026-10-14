<x-documento titulo="Pases emitidos" tipo="Informe" orientacion="landscape"
    :subtitulo="($origen ? 'Desde '.$origen->nombre : 'Todas las oficinas de origen').' · '.\Carbon\Carbon::parse($desde)->format('d/m/Y').' al '.\Carbon\Carbon::parse($hasta)->format('d/m/Y')">
    <table>
        <thead><tr><th>Fecha</th><th>Expediente</th><th>Causante</th><th>Origen</th><th>Destino</th><th>Lo pasó</th></tr></thead>
        <tbody>
            @forelse ($filas as $f)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td>{{ $f->numero }}</td>
                    <td>{{ $f->causante }}</td>
                    <td>{{ $f->origen }}</td>
                    <td>{{ $f->destino ?? 'Sin especificar' }}</td>
                    <td>{{ trim(($f->apellido ?? '').' '.($f->nombre_empleado ?? '')) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No hubo pases en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="nota">{{ number_format($filas->count(), 0, ',', '.') }} pases. No incluye el alta de los expedientes.@if ($truncado) La lista quedó cortada por exceso de pases: achicá el período.@endif</p>
</x-documento>
