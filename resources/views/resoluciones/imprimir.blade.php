{{-- Resolución lista para imprimir / "Guardar como PDF" (ver ImprimirResolucionController). Abre el diálogo de impresión al cargar. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resolución {{ $resolucion->numero_resolucion ?: '' }} — Expte. {{ $resolucion->numero_exp }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        html { background: #e5e8ec; }
        body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .acciones { position: fixed; top: 10px; right: 14px; display: flex; gap: 8px; z-index: 5; }
        .acciones button, .acciones a { font: 600 13px 'Segoe UI', Arial, sans-serif; background: #00519e; color: #fff; border: 0; border-radius: 8px; padding: 9px 16px; cursor: pointer; text-decoration: none; box-shadow: 0 1px 4px rgba(0, 0, 0, .25); }
        .acciones a { background: #fff; color: #00519e; }
        .pagina { max-width: 1250px; margin: 56px auto 32px; }
        .libre { width: 210mm; max-width: 100%; margin: 56px auto 32px; background: #fff; padding: 18mm 18mm; box-shadow: 0 2px 14px rgba(0, 0, 0, .18); font: 14px/1.5 'Times New Roman', Times, serif; text-align: justify; }
        @media print {
            html { background: #fff; }
            .acciones { display: none; }
            .pagina, .libre { margin: 0; max-width: none; width: auto; padding: 0; box-shadow: none; }
            /* El prototipo está pensado para 1200px de ancho: en la hoja se mantiene el armado de escritorio. */
            .documento-resolucion { background: #fff !important; padding: 0 !important; }
            .documento-resolucion .sheet { width: 100% !important; border: 0 !important; box-shadow: none !important; }
            .documento-resolucion .datos-principales { grid-template-columns: repeat(3, 1fr) !important; }
            .documento-resolucion .firmas { grid-template-columns: 1fr 1fr !important; }
        }
    </style>
</head>
<body>
    <div class="acciones">
        <a href="{{ route('resoluciones') }}">Volver a resoluciones</a>
        <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    @if ($conPrototipo)
        <div class="pagina">{!! $html !!}</div>
    @else
        <div class="libre">{!! $html !!}</div>
    @endif

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
