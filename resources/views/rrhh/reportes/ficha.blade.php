<x-rrhh.impresion titulo="Ficha personal" :subtitulo="$empleado->apellido.', '.$empleado->nombre.' · Legajo '.$empleado->legajo">
    @foreach (\App\Services\Rrhh\CamposLegajo::grupos($empleado, $nivel) as $grupo)
        <h2>{{ $grupo['titulo'] }}{{ $grupo['sensible'] ? ' (confidencial)' : '' }}</h2>
        <dl>
            @foreach ($grupo['campos'] as $etiqueta => $valor)
                <dt>{{ $etiqueta }}</dt><dd>{{ $valor ?? '—' }}</dd>
            @endforeach
        </dl>
    @endforeach

    @if ($familia->isNotEmpty())
        <h2>Grupo familiar</h2>
        <table>
            <thead><tr><th>Apellido y nombre</th><th>Parentesco</th><th>Nacimiento</th><th>DNI</th></tr></thead>
            <tbody>
                @foreach ($familia as $f)
                    <tr>
                        <td>{{ $f->apellido_nombre }}</td>
                        <td>{{ $f->parentesco }}</td>
                        <td>{{ $f->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($f->fecha_nacimiento)->format('d/m/Y') : '' }}</td>
                        <td>{{ $f->numero_documento }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($fojas->isNotEmpty())
        <h2>Foja de servicio</h2>
        <table>
            <thead><tr><th>Fecha</th><th>Resolución / decreto</th><th>Detalle</th></tr></thead>
            <tbody>
                @foreach ($fojas as $foja)
                    <tr>
                        <td>{{ $foja->fecha?->format('d/m/Y') }}</td>
                        <td>{{ trim($foja->resolucion_decreto.' '.$foja->nro_res_dec) }}</td>
                        <td>{{ $foja->observacion }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Vacaciones pendientes</h2>
    <p>
        {{ $saldo->sum('saldo') }} días
        @if ($saldo->where('saldo', '>', 0)->isNotEmpty())
            ({{ $saldo->where('saldo', '>', 0)->map(fn ($s) => $s->anio.': '.$s->saldo)->implode(' · ') }})
        @endif
    </p>

    <h2>Licencias del año</h2>
    <table>
        <thead><tr><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Días</th></tr></thead>
        <tbody>
            @forelse ($licencias as $lic)
                <tr>
                    <td>{{ $lic->tipo?->descripcion }}</td>
                    <td>{{ $lic->desde->format('d/m/Y') }}</td>
                    <td>{{ $lic->hasta->format('d/m/Y') }}</td>
                    <td>{{ $lic->desde->diffInDays($lic->hasta) + 1 }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin licencias este año.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-rrhh.impresion>
