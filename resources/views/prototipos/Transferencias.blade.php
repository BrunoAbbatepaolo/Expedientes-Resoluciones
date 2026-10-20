{{-- resources/views/prototipos/Transferencias.blade.php — los datos se editan en el documento (ver components/resolucion/campo). --}}
@php
    $editable = $editable ?? false;
    $datos = $datos ?? [];
@endphp
<x-resolucion.documento :editable="$editable" :datos="$datos" :marcadores="$marcadores ?? false">
<div class="documento-resolucion">
    <style>
        .documento-resolucion {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.35;
            color: #000;
            width: 100%;
            max-width: 900px;
            min-height: 1100px;
            margin: 0 auto;
            padding: 50px 60px;
            background: white;
            box-sizing: border-box;
        }
        .documento-resolucion * {
            box-sizing: border-box;
        }
        .documento-resolucion p {
            margin-bottom: 0.5em;
            text-align: justify;
            font-size: 14px;
        }
        .documento-resolucion .text-right { text-align: right; }
        .documento-resolucion .text-center { text-align: center; }
        .documento-resolucion .font-bold { font-weight: bold; }
        .documento-resolucion .underline { text-decoration: underline; }
        .documento-resolucion .uppercase { text-transform: uppercase; }
        .documento-resolucion .mb-1 { margin-bottom: 0.25em; }
        .documento-resolucion .mb-2 { margin-bottom: 0.5em; }
        .documento-resolucion .mb-4 { margin-bottom: 1em; }
        .documento-resolucion .mt-1 { margin-top: 0.25em; }
        .documento-resolucion .mt-2 { margin-top: 0.5em; }
        .documento-resolucion .mt-3 { margin-top: 0.75em; }
        .documento-resolucion .mt-4 { margin-top: 1em; }
        .documento-resolucion .mt-6 { margin-top: 1.5em; }
        .documento-resolucion .mt-16 { margin-top: 4em; }
        .documento-resolucion .pl-8 { padding-left: 2em; }
        .documento-resolucion .space-y-3 > * + * { margin-top: 0.75em; }
        .documento-resolucion .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5em;
        }
        .documento-resolucion .header-logos {
            display: flex;
            gap: 1em;
            align-items: flex-start;
        }
        .documento-resolucion .header-fecha {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5em;
        }
        .documento-resolucion .expediente {
            margin-top: 1em;
            font-size: 14px;
        }
        .documento-resolucion .numero-resolucion {
            text-align: center;
            margin: 1.5em 0;
        }
        .documento-resolucion .numero-resolucion h1 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .documento-resolucion .seccion-visto {
            margin-bottom: 1em;
        }
        .documento-resolucion .seccion-considerando {
            margin-bottom: 1em;
        }
        .documento-resolucion .bloque-intervencion {
            text-align: center;
            margin: 1.5em 0;
        }
        .documento-resolucion .articulo {
            margin-bottom: 0.75em;
        }
        .documento-resolucion .firmas {
            margin-top: 4em;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .documento-resolucion .firma-box {
            text-align: center;
            font-size: 12px;
            width: 200px;
        }
        .documento-resolucion .firma-box .linea-firma {
            border-bottom: 1px solid #000;
            height: 60px;
            margin-bottom: 0.5em;
        }
        .documento-resolucion .firma-box.center .linea-firma {
            border: none;
        }
        .documento-resolucion .firma-box.center img {
            width: 130px;
        }
    </style>

    <div class="header-container">
        <div>
            <img src="{{ asset('images/Logo-ipv.png') }}" alt="IPV" style="width: 150px;" />
        </div>
        <div class="header-logos">
            <img src="{{ asset('images/logo-despacho.png') }}" alt="Despacho" style="width: 90px;" />
            <img src="{{ asset('images/GOBIERNO_DE_TUCUMÁN_LOGO HORIZONTAL_CURVAS-01.png') }}" alt="Gobierno de Tucumán" style="width: 150px;" />
        </div>
    </div>

    <div class="header-fecha">
        <div>
            <div>S.M. de Tucumán</div>
            <div><x-resolucion.campo campo="fecha_res" tipo="date" formato="fecha" /></div>
        </div>
    </div>

    <div class="expediente">
        <span class="underline">Expte. N° <x-resolucion.campo campo="num_exp" placeholder="[NÚMERO]" /></span>
    </div>

    <div class="numero-resolucion">
        <h1><span class="underline">RESOLUCION N° <x-resolucion.campo campo="num_res" placeholder="[NÚMERO]" /></span></h1>
    </div>

    <div class="seccion-visto pl-8">
        <span class="font-bold">VISTO, </span>
        <span>estas actuaciones mediante las cuales se solicita autorización de transferencia de la unidad habitacional identificada como </span>
        <span class="font-bold">
            MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> – CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" />
            DEL <x-resolucion.campo campo="nombre_plan" placeholder="[NOMBRE DEL PLAN]" mayusculas />
        </span>
        <span>; y,</span>
    </div>

    <div class="seccion-considerando mt-4">
        <p><span class="font-bold">CONSIDERANDO:</span></p>

        <p class="mt-2">
            Que, la vivienda antes descripta fue asignada mediante Acta de Tenencia de Uso adjudicada por Resolución N° <x-resolucion.campo campo="num_res_adjudicacion" placeholder="[NÚMERO]" /> a los
            <x-resolucion.segun si="nombre_cotitular_anterior" con="Señores" sin="Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular_anterior" placeholder="[NOMBRE TITULAR ANTERIOR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular_anterior" placeholder="[DNI TITULAR ANTERIOR]" /><x-resolucion.opcional si="nombre_cotitular_anterior" espacio> y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular_anterior" placeholder="[NOMBRE COTITULAR ANTERIOR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular_anterior" placeholder="[DNI COTITULAR ANTERIOR]" /></x-resolucion.opcional>
        </p>

        <p>
            Que, a fs. <x-resolucion.campo campo="num_foja_boleto" placeholder="[FOJA]" /> se encuentra adjuvant el Boleto de Venta debidamente certificado por Escribano Público entre los
            <x-resolucion.segun si="nombre_cotitular_anterior" con="Señores" sin="Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular_anterior" placeholder="[NOMBRE TITULAR ANTERIOR]" mayusculas /></span><x-resolucion.opcional si="nombre_cotitular_anterior">/<span class="font-bold"><x-resolucion.campo campo="nombre_cotitular_anterior" placeholder="[NOMBRE COTITULAR ANTERIOR]" mayusculas /></span></x-resolucion.opcional>
            a favor de
            la Señora <span class="font-bold"><x-resolucion.campo campo="nombre_titular_nuevo" placeholder="[NOMBRE TITULAR NUEVO]" mayusculas /></span>.
        </p>

        <p>
            Que, a fs. <x-resolucion.campo campo="num_foja_plan" placeholder="[FOJA]" /> obra Plan de Readecuación de Pagos de Unidades Habitacionales el cual detalla el nuevo plan de amortización y Convenio de Recaudación de viviendas transferidas.
        </p>

        <p>
            Que, en virtud de lo informado por el Departamento Recursos Financieros a fs. <x-resolucion.campo campo="num_foja_recursos" placeholder="[FOJA]" />, lo solicitado por el Departamento Promoción Social a fs. <x-resolucion.campo campo="num_foja_promocion" placeholder="[FOJA]" />, lo informado por la Dirección de Área de Recupero y Regularización Dominial a fs. <x-resolucion.campo campo="num_foja_regularizacion" placeholder="[FOJA]" /> vuelta y al Dictamen Legal N° <x-resolucion.campo campo="num_dictamen" placeholder="[NÚMERO]" /> de fecha <x-resolucion.campo campo="fecha_dictamen" placeholder="[FECHA]" tipo="date" formato="fecha" /> a fs. <x-resolucion.campo campo="num_foja_dictamen" placeholder="[FOJA]" /> y su ratificación a fs. <x-resolucion.campo campo="num_foja_ratificacion" placeholder="[FOJA]" />, corresponde dar curso a lo solicitado.
        </p>

        <p class="mt-3">Por ello:</p>
    </div>

    <div class="bloque-intervencion">
        <div class="font-bold">LA INTERVENCION</div>
        <div class="font-bold">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
        <div class="font-bold">DE TUCUMAN</div>
        <div class="font-bold mt-1"><span class="underline">R E S U E L V E</span></div>
    </div>

    <div class="space-y-3">
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 1º.- </span>APROBAR la transferencia de la unidad habitacional identificada como
                <span class="font-bold">
                    MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> – CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" />
                    DEL <x-resolucion.campo campo="nombre_plan" placeholder="[NOMBRE DEL PLAN]" mayusculas />
                </span>
                que fuera adjudicada según Resolución N° <x-resolucion.campo campo="num_res_adjudicacion" placeholder="[NÚMERO]" /> a los
                <x-resolucion.segun si="nombre_cotitular_anterior" con="Señores" sin="Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular_anterior" placeholder="[NOMBRE TITULAR ANTERIOR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular_anterior" placeholder="[DNI TITULAR ANTERIOR]" /><x-resolucion.opcional si="nombre_cotitular_anterior" espacio> y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular_anterior" placeholder="[NOMBRE COTITULAR ANTERIOR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular_anterior" placeholder="[DNI COTITULAR ANTERIOR]" /></x-resolucion.opcional>
                a favor de los Señores
                <span class="font-bold"><x-resolucion.campo campo="nombre_titular_nuevo" placeholder="[NOMBRE TITULAR NUEVO]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular_nuevo" placeholder="[DNI TITULAR NUEVO]" />
                    <x-resolucion.opcional si="fecha_nacimiento_titular">(Fecha de Nacimiento <x-resolucion.campo campo="fecha_nacimiento_titular" placeholder="[FECHA NACIMIENTO TITULAR]" tipo="date" formato="fecha" />)</x-resolucion.opcional>
                <x-resolucion.opcional si="nombre_cotitular_nuevo">y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular_nuevo" placeholder="[NOMBRE COTITULAR NUEVO]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular_nuevo" placeholder="[DNI COTITULAR NUEVO]" />
                    <x-resolucion.opcional si="fecha_nacimiento_cotitular">(Fecha de Nacimiento <x-resolucion.campo campo="fecha_nacimiento_cotitular" placeholder="[FECHA NACIMIENTO COTITULAR]" tipo="date" formato="fecha" />)</x-resolucion.opcional></x-resolucion.opcional>
                <x-resolucion.opcional si="nombre_titular_nuevo">cónyuges en primeras nupcias</x-resolucion.opcional>
                , en virtud del Instrumento Privado de fecha <x-resolucion.campo campo="fecha_instrumento" placeholder="[FECHA]" tipo="date" formato="fecha" /> en un todo de acuerdo a informes de autos.-
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 2º.- </span>APROBAR el Convenio de Readecuación de Pagos de Unidades Habitacionales Transferidas, el cual está compuesto por <x-resolucion.campo campo="num_cuotas" placeholder="[NÚMERO]" /> (<x-resolucion.derivado campo="num_cuotas" placeholder="[NÚMERO]" />) cuotas y el importe total de la cuota asciende a la suma de $ <x-resolucion.campo campo="monto_cuota" placeholder="[MONTO]" formato="moneda" /> (<x-resolucion.derivado campo="monto_cuota" placeholder="[MONTO]" />), más concepto de Fondo de Reserva y Gastos Administrativos. Este convenio considera una Tasa de Interés Anual del <x-resolucion.campo campo="tasa_interes" placeholder="[TASA]" />% (<x-resolucion.derivado campo="tasa_interes" placeholder="[TASA]" />%) de fecha <x-resolucion.campo campo="fecha_tasa" placeholder="[FECHA]" tipo="date" formato="fecha" /> en un todo de acuerdo a lo informado por el Departamento Recursos Financieros a fs. <x-resolucion.campo campo="num_foja_recursos" placeholder="[FOJA]" />.-
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 3º.- </span>AUTORIZAR al Departamento Cómputos a generar las boletas de amortización mensual de la vivienda mencionada.-
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 4º.- </span>COMUNICAR a los interesados; Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección Adjudicaciones, a sus efectos.-
            </p>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-box">
            <div class="linea-firma"></div>
            <x-resolucion.firmante numero="1" predeterminado="MARIA GABRIELA LAZARTE" clase-nombre="font-bold" />
        </div>
        <div class="firma-box center">
            <img src="{{ asset('images/logo-provincia.png') }}" alt="Provincia" />
        </div>
        <div class="firma-box">
            <div class="linea-firma"></div>
            <x-resolucion.firmante numero="2" predeterminado="Arq. HUGO CABRAL" clase-nombre="font-bold" />
        </div>
    </div>
</div>
</x-resolucion.documento>
