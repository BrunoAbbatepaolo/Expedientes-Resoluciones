{{--
    Hoja A4 institucional para imprimir o guardar como PDF (Imprimir → "Guardar como PDF").
    La usan la carátula de expedientes IPV y los reportes de RRHH. En pantalla se ve como una hoja sobre fondo gris;
    al imprimir, el encabezado y el pie se repiten en cada página y el número de página sale en el margen.

    Dentro del slot se usan etiquetas simples: h2 (sección), dl/dt/dd (datos), table (listados), .nota, .destacado.
--}}
@props([
    'titulo',
    'tipo' => null,
    'subtitulo' => null,
    'dependencia' => null,
    'orientacion' => 'portrait',
    'firmas' => false,
])
@php
    $emite = trim((auth()->user()?->nombre ?? '').' '.(auth()->user()?->apellido ?? ''));
    $emitido = now()->timezone(config('app.timezone'));
    $horizontal = $orientacion === 'landscape';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tipo ? $tipo.' '.$titulo : $titulo }}</title>
    <style>
        :root { --azul: #00519e; --azul-osc: #003d7a; --oro: #ffb81a; --oro-tinta: #7a5200; --tinta: #1f2937; --suave: #5b6675; --linea: #d5dbe3; --fondo-tabla: #f3f6fa; }
        * { box-sizing: border-box; }
        @page { size: A4 {{ $orientacion }}; margin: 14mm 15mm 18mm; @bottom-right { content: "Página " counter(page) " de " counter(pages); font: 9px 'Segoe UI', Arial, sans-serif; color: #5b6675; } }

        html { background: #e5e8ec; }
        body { margin: 0; font: 11.5px/1.45 'Segoe UI', Arial, Helvetica, sans-serif; color: var(--tinta); -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* En pantalla: la hoja A4 centrada. */
        .hoja { width: {{ $horizontal ? '297mm' : '210mm' }}; max-width: 100%; margin: 56px auto 32px; background: #fff; padding: 14mm 15mm 16mm; box-shadow: 0 2px 14px rgba(0, 0, 0, .18); }
        .marco { width: 100%; border-collapse: collapse; }
        .marco > thead > tr > td, .marco > tbody > tr > td, .marco > tfoot > tr > td { padding: 0; border: 0; background: none; }

        .acciones { position: fixed; top: 10px; right: 14px; display: flex; gap: 8px; z-index: 5; }
        .acciones button { font: 600 13px 'Segoe UI', Arial, sans-serif; background: var(--azul); color: #fff; border: 0; border-radius: 8px; padding: 9px 16px; cursor: pointer; box-shadow: 0 1px 4px rgba(0, 0, 0, .25); }
        .acciones button:hover { background: var(--azul-osc); }

        /* Membrete */
        .membrete { display: flex; align-items: center; justify-content: space-between; gap: 12mm; padding-bottom: 3.5mm; }
        .membrete img { display: block; height: 15mm; width: auto; }
        .filete { height: 3px; background: linear-gradient(90deg, var(--azul) 0 70%, var(--oro) 70% 100%); margin-bottom: 1.2mm; }
        .filete-fino { height: 1px; background: var(--linea); margin-bottom: 4mm; }
        .dependencia { font-size: 9.5px; letter-spacing: .14em; text-transform: uppercase; color: var(--suave); text-align: right; padding-bottom: 2mm; }

        /* Título */
        .titulo { display: flex; justify-content: space-between; align-items: flex-end; gap: 8mm; margin: 3mm 0 5mm; }
        .tipo { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--oro-tinta); }
        h1 { margin: 1px 0 0; font: 700 21px/1.2 Georgia, 'Times New Roman', serif; color: var(--azul-osc); }
        .subtitulo { margin-top: 2px; font-size: 12.5px; color: var(--suave); }
        .emitido { text-align: right; font-size: 10px; color: var(--suave); white-space: nowrap; }

        /* Contenido */
        h2 { margin: 6mm 0 2.5mm; padding: 1.2mm 0 1.2mm 3mm; font: 700 11px 'Segoe UI', Arial, sans-serif; letter-spacing: .1em; text-transform: uppercase; color: var(--azul-osc); border-left: 3px solid var(--oro); background: var(--fondo-tabla); break-after: avoid; }
        dl { display: grid; grid-template-columns: 38mm 1fr; margin: 0; border: 1px solid var(--linea); }
        dt, dd { margin: 0; padding: 1.6mm 2.6mm; border-bottom: 1px solid var(--linea); }
        dt { background: var(--fondo-tabla); font-weight: 600; color: var(--azul-osc); }
        dt:last-of-type, dt:last-of-type + dd { border-bottom: 0; }
        dl, table.listado, table:not(.marco) { break-inside: auto; }
        table:not(.marco) { width: 100%; border-collapse: collapse; border: 1px solid var(--linea); font-size: 10.5px; }
        table:not(.marco) th { background: var(--azul); color: #fff; font-weight: 600; text-align: left; padding: 1.8mm 2.2mm; border: 1px solid var(--azul); }
        table:not(.marco) td { padding: 1.6mm 2.2mm; border: 1px solid var(--linea); vertical-align: top; }
        table:not(.marco) tbody tr:nth-child(even) td { background: #f8fafc; }
        table:not(.marco) thead { display: table-header-group; }
        table:not(.marco) tr { break-inside: avoid; }
        .nota { margin-top: 4mm; font-size: 10px; color: var(--suave); }
        .destacado { border: 1px solid var(--linea); border-left: 3px solid var(--azul); padding: 2.5mm 3.5mm; background: var(--fondo-tabla); }

        /* Firmas */
        .firmas { display: flex; justify-content: space-around; gap: 14mm; margin-top: 22mm; break-inside: avoid; }
        .firmas div { flex: 1; max-width: 62mm; border-top: 1px solid var(--tinta); padding-top: 1.5mm; text-align: center; font-size: 10px; color: var(--suave); }

        /* Pie */
        .pie { margin-top: 6mm; padding-top: 2mm; border-top: 1px solid var(--linea); display: flex; justify-content: space-between; gap: 8mm; font-size: 9px; color: var(--suave); }

        @media (max-width: 800px) { .hoja { margin: 52px 0 0; padding: 14px; box-shadow: none; } dl { grid-template-columns: 1fr; } .titulo { flex-direction: column; align-items: flex-start; } .emitido { text-align: left; } }
        @media print {
            html { background: #fff; }
            .acciones { display: none; }
            .hoja { width: auto; margin: 0; padding: 0; box-shadow: none; }
            .marco > thead { display: table-header-group; }
            .marco > tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>
    <div class="acciones">
        <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    <div class="hoja">
        <table class="marco">
            {{-- thead: se repite en cada hoja impresa --}}
            <thead>
                <tr><td>
                    <div class="membrete">
                        <img src="{{ asset('images/Logo-ipv.png') }}" alt="Instituto Provincial de Vivienda y Desarrollo Urbano">
                        <img src="{{ asset('images/GOBIERNO_DE_TUCUMÁN_LOGO HORIZONTAL_CURVAS-01.png') }}" alt="Gobierno de Tucumán">
                    </div>
                    <div class="filete"></div>
                    <div class="filete-fino"></div>
                </td></tr>
            </thead>

            <tbody>
                <tr><td>
                    @if ($dependencia)
                        <div class="dependencia">{{ $dependencia }}</div>
                    @endif

                    <div class="titulo">
                        <div>
                            @if ($tipo)<div class="tipo">{{ $tipo }}</div>@endif
                            <h1>{{ $titulo }}</h1>
                            @if ($subtitulo)<div class="subtitulo">{{ $subtitulo }}</div>@endif
                        </div>
                        <div class="emitido">Emitido el {{ $emitido->format('d/m/Y') }}<br>a las {{ $emitido->format('H:i') }} hs</div>
                    </div>

                    {{ $slot }}

                    @if ($firmas)
                        <div class="firmas">
                            <div>Firma y aclaración</div>
                            <div>Sello de la dependencia</div>
                        </div>
                    @endif
                </td></tr>
            </tbody>

            <tfoot>
                <tr><td>
                    <div class="pie">
                        <span>Documento emitido por SiRex{{ $emite !== '' ? ' · '.$emite : '' }} · {{ $emitido->format('d/m/Y H:i') }}</span>
                        <span>Instituto Provincial de Vivienda y Desarrollo Urbano · Tucumán</span>
                    </div>
                </td></tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
