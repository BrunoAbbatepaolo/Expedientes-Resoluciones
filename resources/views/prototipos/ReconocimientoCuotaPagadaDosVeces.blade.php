{{-- resources/views/prototipos/ReconocimientoCuotaPagadaDosVeces.blade.php — los datos se editan en el documento (ver components/resolucion/campo). --}}
@php
    $editable = $editable ?? false;
    $datos = $datos ?? [];
    try {
        $fechaLarga = \Carbon\Carbon::parse(($datos['fecha_res'] ?? '') ?: now())->locale('es')->translatedFormat('j \d\e F \d\e Y');
    } catch (\Throwable $e) {
        $fechaLarga = '';
    }
@endphp
<x-resolucion.documento :editable="$editable" :datos="$datos" :marcadores="$marcadores ?? false">
<div class="documento-resolucion">
    <style>
        @include('prototipos._estilos')

        /* CUERPO 2 (específico de este prototipo) */
        .cuerpo-2 {
            font-size: 14px;
            text-align: justify;
            line-height: 1.7;
            color: #374151;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
        }
        .cuerpo-2 p {
            margin-bottom: 0.5em;
        }
        .cuerpo-2 table {
            font-family: 'Times New Roman', Times, serif;
        }
        .cuerpo-2 th {
            font-size: 12px;
        }
    </style>

    <div class="sheet">
        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <div class="logos-container">
                    <img src="{{ asset('images/Logo-ipv.png') }}" alt="IPV" class="logo-ipv" />
                    <img src="{{ asset('images/GOBIERNO_DE_TUCUMÁN_LOGO HORIZONTAL_CURVAS-01.png') }}" alt="Gobierno de Tucumán" class="logo-gobierno" />
                </div>
                <div class="brand">
                    <div class="brand-title">INSTITUTO PROVINCIAL DE VIVIENDA</div>
                    <div class="brand-subtitle">Y DESARROLLO URBANO</div>
                </div>
            </div>
            <div class="header-right">
                <div class="header-title">Reconocimiento de Cuota Pagada Dos Veces</div>
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="content">
            <!-- DATOS PRINCIPALES -->
            <div class="datos-principales">
                <div class="dato-card">
                    <div class="dato-label">Trámite N°</div>
                    <div class="dato-value"><x-resolucion.campo campo="num_exp" placeholder="[NÚMERO_EXPEDIENTE]" /></div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">Fecha</div>
                    <div class="dato-value"><x-resolucion.campo campo="fecha_res" tipo="date" formato="fecha" /></div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">Código</div>
                    <div class="dato-value">
                        <x-resolucion.campo campo="codigo_pago" placeholder="[CÓDIGO]" maxlength="10" />
                    </div>
                </div>
            </div>

            <!-- CAUSANTE -->
            <div class="causante">
                <div class="causante-label">Causante</div>
                <div class="causante-value"><x-resolucion.campo campo="causante" placeholder="[CAUSANTE]" predeterminado="DEPARTAMENTO RECURSOS FINANCIEROS" /></div>
            </div>

            <!-- LUGAR Y FECHA -->
            @if ($editable)
                <div class="lugar-fecha" x-text="'San Miguel de Tucumán, ' + (() => { const f = $wire.datos['fecha_res']; const d = f ? new Date(f + 'T00:00:00') : new Date(); return isNaN(d) ? '' : d.toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' }); })()">San Miguel de Tucumán, {{ $fechaLarga }}</div>
            @else
                <div class="lugar-fecha">San Miguel de Tucumán, {{ $fechaLarga }}</div>
            @endif

            <!-- DESTINATARIO -->
            <div class="destinatario">
                SR. JEFE DEPARTAMENTO COMPUTOS ING. <x-resolucion.campo campo="jefe_computos" placeholder="[JEFE CÓMPUTOS]" predeterminado="FEDERICO CONRAD" />:
            </div>

            <!-- REFERENCIA -->
            <div class="referencia">
                REF.: RECONOCIMIENTO DE CUOTA PAGADA DOS VECES - CÓDIGO (<x-resolucion.campo campo="codigo_pago" placeholder="[CÓDIGO]" maxlength="10" />)
            </div>

            <!-- CUERPO -->
            <div class="cuerpo">
                Me dirijo a Ud. a los fines de que se realice el reconocimiento de la cuota pagada DOBLEMENTE segun la planilla adjuntada.
<br>
                Atte-
            </div>
            <!-- CUERPO 2 -->
            <div class="cuerpo-2">
                <div style="text-align:center; font-weight:bold; font-size:16px; margin-bottom:20px;">
                    RECONOCIMIENTO DE CUOTAS PAGADAS DOBLEMENTE
                </div>

                <p><strong>El/la que suscribe:</strong> <x-resolucion.campo campo="quien_suscribe" placeholder="[NOMBRE]" mayusculas /></p>

                <p><strong>DNI N°:</strong> <x-resolucion.campo campo="dni_suscribe" placeholder="[DNI]" /></p>

                <p>en su carácter de adjudicatario del crédito identificado como</p>

                <p>
                    <strong>C° Barrio:</strong> <x-resolucion.campo campo="manzana" placeholder="[BARRIO]" />
                    – <strong>C° Casa:</strong> <x-resolucion.campo campo="lote" placeholder="[CASA]" />
                </p>

                <p>cuyo plan de pagos vigente responde al siguiente esquema:</p>

                <p><strong>Cuota sin Gastos Administrativos:</strong> $<x-resolucion.campo campo="cuota_sin_gastos" placeholder="[MONTO]" /></p>

                <p><strong>N° Última Cuota Emitida:</strong> <x-resolucion.campo campo="nro_ultima_cuota" placeholder="[NRO]" /></p>

                <p><strong>Vencimiento Última cuota Emitida:</strong> <x-resolucion.campo campo="vencimiento_ultima_cuota" tipo="date" formato="fecha" /></p>

                <p><strong>Plazo Total del Plan:</strong> <x-resolucion.campo campo="plazo_total_plan" placeholder="[PLAZO]" /></p>

                <p>Adjunta a la presente, copias de boletas pagadas dos veces por error.- Las detallamos a continuación:</p>

                <x-resolucion.tabla campo="tabla_cuotas_pagadas" :columnas="['cuota' => 'Cuota N°', 'vto' => 'Fecha Vto.', 'capital' => ['Importe Capital', '$'], 'pagado' => ['Importe Pagado', '$'], 'fpago' => 'Fecha de Pago', 'entidad' => 'Pagada en']" />

                <p>SOLICITA EL RECONOCIMIENTO DE LA CUOTA PAGADA EN EXCESO, EMITIENDO UNA CUOTA ADICIONAL ANTICIPADA A FIN DE QUE SE APLIQUE EL PAGO.- DEBERA CARGARSE EL PAGO, ASIGNANDO COMO N° DE TRANSFERENCIA EL QUE CORRESPONDE AL PRESENTE EXPEDIENTE:</p>

                <x-resolucion.tabla campo="tabla_cuotas_a_reconocer" :columnas="['cuota' => 'Cuota N°', 'vto' => 'Fecha Vto.', 'capital' => ['Importe Capital', '$'], 'pagado' => ['Importe Pagado', '$'], 'fpago' => 'Fecha de Pago', 'transferencia' => 'Transferencia N°']" />

                <p style="margin-bottom:10px;">Presto mi conformidad para aplicar las pautas precedentes</p>

                <p>En San Miguel de Tucumán, <x-resolucion.campo campo="fecha_res" tipo="date" formato="fecha_larga" />, se firman dos ejemplares de un mismo tenor y a un solo efecto.-</p>
            </div>
        </div>
    </div>
</div>
</x-resolucion.documento>
