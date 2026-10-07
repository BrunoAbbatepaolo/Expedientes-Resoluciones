<x-rrhh.impresion :titulo="$titulo" :subtitulo="$subtitulo">
    <table>
        <thead>
            <tr>
                @foreach ($columnas as $columna)
                    <th>{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    @foreach ($fila as $valor)
                        <td>{{ $valor }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columnas) }}">Sin datos para estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-rrhh.impresion>
