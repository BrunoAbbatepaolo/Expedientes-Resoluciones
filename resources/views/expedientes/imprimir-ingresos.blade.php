<x-documento titulo="Ingresos a oficina" tipo="Informe" orientacion="landscape"
    :subtitulo="$oficina->nombre.' · '.\Carbon\Carbon::parse($desde)->format('d/m/Y').' al '.\Carbon\Carbon::parse($hasta)->format('d/m/Y')">
    <table>
        <thead><tr><th>Expediente</th><th>Causante</th><th>Asunto</th><th>Primer ingreso</th><th>Viene de</th><th>Veces</th></tr></thead>
        <tbody>
            @forelse ($filas as $f)
                <tr>
                    <td>{{ $f->numero }}</td>
                    <td>{{ $f->causante }}</td>
                    <td>{{ $f->asunto }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td>{{ $f->viene_de ?? '—' }}</td>
                    <td>{{ $f->veces }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No entró ningún expediente en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="nota">{{ number_format($filas->count(), 0, ',', '.') }} expedientes. Cada expediente cuenta una sola vez.@if ($truncado) La lista quedó cortada por exceso de pases: achicá el período.@endif</p>
</x-documento>
