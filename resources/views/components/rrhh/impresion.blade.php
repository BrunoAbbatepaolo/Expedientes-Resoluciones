{{-- Hoja para imprimir / guardar como PDF (mismo estilo que la impresión de expedientes IPV). --}}
@props(['titulo', 'subtitulo' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; color: #111; margin: 0; padding: 32px; font-size: 13px; }
        header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; border-bottom: 2px solid #00519e; padding-bottom: 10px; margin-bottom: 16px; }
        h1 { margin: 0; font-size: 22px; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .05em; color: #00519e; margin: 22px 0 8px; }
        dl { display: grid; grid-template-columns: 170px 1fr; gap: 6px 12px; margin: 0; }
        dt { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #eef3f9; }
        .acciones { position: fixed; top: 12px; right: 12px; }
        .acciones button { font: 600 13px system-ui, sans-serif; background: #00519e; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; cursor: pointer; }
        footer { margin-top: 24px; font-size: 11px; color: #555; }
        @media (max-width: 640px) { body { padding: 16px; } dl { grid-template-columns: 1fr; } }
        @media print { .acciones { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="acciones"><button type="button" onclick="window.print()">Imprimir / Guardar PDF</button></div>

    <header>
        <div>
            <div>Instituto Provincial de Vivienda y Desarrollo Urbano · Recursos Humanos</div>
            <h1>{{ $titulo }}</h1>
            @if ($subtitulo)<div>{{ $subtitulo }}</div>@endif
        </div>
        <div>{{ \App\Services\Rrhh\Hora::ahora()->format('d/m/Y H:i') }}</div>
    </header>

    {{ $slot }}

    <footer>Generado por SiRex con datos del sistema de personal. Emitido por {{ auth()->user()?->name }}.</footer>
</body>
</html>
