@php($exp = $ficha['expediente'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expediente {{ $exp->numero }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; color: #111; margin: 0; padding: 32px; font-size: 13px; }
        header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #00519e; padding-bottom: 10px; margin-bottom: 16px; }
        h1 { margin: 0; font-size: 22px; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .05em; color: #00519e; margin: 22px 0 8px; }
        dl { display: grid; grid-template-columns: 140px 1fr; gap: 6px 12px; margin: 0; }
        dt { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #eef3f9; }
        .acciones { position: fixed; top: 12px; right: 12px; }
        .acciones button { font: 600 13px system-ui, sans-serif; background: #00519e; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; cursor: pointer; }
        footer { margin-top: 24px; font-size: 11px; color: #555; }
        @media print { .acciones { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="acciones"><button type="button" onclick="window.print()">Imprimir / Guardar PDF</button></div>

    <header>
        <div>
            <div>Instituto Provincial de Vivienda y Desarrollo Urbano</div>
            <h1>Expediente N° {{ $exp->numero }}</h1>
        </div>
        <div>Iniciado: {{ $exp->fecha?->format('d/m/Y') ?? '—' }}</div>
    </header>

    <h2>Carátula</h2>
    <dl>
        <dt>Causante</dt><dd>{{ $exp->causante?->nombre ?? '—' }} @if ($exp->causante?->tipo) ({{ $exp->causante->tipo->nombre }}) @endif</dd>
        <dt>Asunto</dt><dd>{{ $exp->tipoAsunto?->nombre }} {{ $exp->asunto }}</dd>
        <dt>Folios / tomo</dt><dd>{{ $exp->folio ?? '—' }} / {{ $exp->tomo ?? '—' }}</dd>
        @if ($exp->resolucion)<dt>Resolución</dt><dd>{{ $exp->resolucion }}</dd>@endif
        @if ($exp->observacion)<dt>Observación</dt><dd>{{ $exp->observacion }}</dd>@endif
        <dt>Ubicación actual</dt><dd>{{ $ficha['ubicacion']->oficina ?? 'Sin pases' }}</dd>
    </dl>

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

    <footer>Generado por SiRex el {{ now()->format('d/m/Y H:i') }} — {{ auth()->user()->name }}</footer>
</body>
</html>
