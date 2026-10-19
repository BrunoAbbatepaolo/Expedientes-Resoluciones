{{-- resources/views/prototipos/Resciciones.blade.php — los datos se editan en el documento (ver components/resolucion/campo). --}}
@php
    $editable = $editable ?? false;
    $datos = $datos ?? [];
@endphp
<x-resolucion.documento :editable="$editable" :datos="$datos">
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
        .documento-resolucion .text-right {
            text-align: right;
        }
        .documento-resolucion .text-center {
            text-align: center;
        }
        .documento-resolucion .font-bold {
            font-weight: bold;
        }
        .documento-resolucion .underline {
            text-decoration: underline;
        }
        .documento-resolucion .uppercase {
            text-transform: uppercase;
        }
        .documento-resolucion .mb-1 { margin-bottom: 0.25em; }
        .documento-resolucion .mb-2 { margin-bottom: 0.5em; }
        .documento-resolucion .mb-4 { margin-bottom: 1em; }
        .documento-resolucion .mb-6 { margin-bottom: 1.5em; }
        .documento-resolucion .mt-1 { margin-top: 0.25em; }
        .documento-resolucion .mt-2 { margin-top: 0.5em; }
        .documento-resolucion .mt-3 { margin-top: 0.75em; }
        .documento-resolucion .mt-4 { margin-top: 1em; }
        .documento-resolucion .mt-6 { margin-top: 1.5em; }
        .documento-resolucion .mt-16 { margin-top: 4em; }
        .documento-resolucion .pl-8 { padding-left: 2em; }
        .documento-resolucion .space-y-3 > * + * { margin-top: 0.75em; }
        .documento-resolucion .flex { display: flex; }
        .documento-resolucion .justify-between { justify-content: space-between; }
        .documento-resolucion .justify-end { justify-content: flex-end; }
        .documento-resolucion .items-start { align-items: flex-start; }
        .documento-resolucion .items-end { align-items: flex-end; }
        .documento-resolucion .gap-4 { gap: 1em; }
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
        .documento-resolucion .campo-vacio {
            color: #999;
            font-style: italic;
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
        <span>estas actuaciones relacionadas con la vivienda identificada como </span>
        <span class="font-bold">
            MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> - CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" />
            DEL EMPRENDIMIENTO "<x-resolucion.campo campo="nombre_emprendimiento" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />"
            <x-resolucion.opcional si="departamento">- <x-resolucion.campo campo="departamento" placeholder="[DEPARTAMENTO]" mayusculas /></x-resolucion.opcional>
        </span>
        <span>; y</span>
    </div>

    <div class="seccion-considerando mt-4">
        <p><span class="font-bold">CONSIDERANDO:</span></p>

        <p class="mt-2">
            Que, la vivienda mencionada fue otorgada a
            <x-resolucion.segun si="nombre_cotitular" con="los Señores" sin="el/la Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE TITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular" placeholder="[DNI TITULAR]" /><x-resolucion.opcional si="nombre_cotitular"> y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular" placeholder="[NOMBRE COTITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular" placeholder="[DNI COTITULAR]" /></x-resolucion.opcional>
            mediante Acta de Tenencia y Uso Provisorio Intransferible Vivienda Única Social y Resolución de Adjudicación N° <x-resolucion.campo campo="num_res_adjudicacion" placeholder="[NÚMERO]" />.
        </p>

        <p>
            Que, del informe obrante a fs. <x-resolucion.campo campo="num_foja_informe" placeholder="[FOJA]" /> se constata que los titulares, tras sendas intimaciones no regularizan la deuda que pesa sobre la unidad de referencia, monto que a la fecha <x-resolucion.campo campo="fecha_deuda" placeholder="[FECHA]" tipo="date" formato="fecha" /> se actualiza a $ <x-resolucion.campo campo="monto_deuda" placeholder="[MONTO]" formato="moneda" /> (<x-resolucion.derivado campo="monto_deuda" placeholder="[MONTO EN LETRAS]" />), por lo que la falta de pago por parte de los
            <x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE TITULAR]" mayusculas />
                <x-resolucion.opcional si="nombre_cotitular">/<x-resolucion.campo campo="nombre_cotitular" placeholder="[NOMBRE COTITULAR]" mayusculas /></x-resolucion.opcional>
            se encuadra en el incumplimiento de la cláusula Novena del contrato suscripto configurándose el supuesto de la cláusula Décimo Primera, facultando al Instituto de Vivienda de la cláusula Décimo Primera, dar por finalizado el mentado contrato y exigir el inmediato desalojo de la unidad, quedando obligados los adjudicatarios a restituir el inmueble.
        </p>

        <p>
            Que, en consecuencia, la Falta de Pago por parte de los titulares se encuadra en el incumplimiento que habilita a dejar sin efecto el Acta de Tenencia y Uso Provisorio Intransferible de Vivienda Única Social y revocar la Resolución de Adjudicación por la causal falta de pago.
        </p>

        <p>
            Que, a fs. <x-resolucion.campo campo="num_foja_dictamen" placeholder="[FOJA]" /> <x-resolucion.campo campo="departamento" placeholder="[DEPARTAMENTO]" /> de Recupero de Viviendas en fecha <x-resolucion.campo campo="fecha_dictamen" placeholder="[FECHA]" tipo="date" formato="fecha" /> emite Dictamen Legal N° <x-resolucion.campo campo="num_dictamen" placeholder="[NÚMERO]" /> en donde no se ofrecen objeciones legales para la procedencia del presente trámite.
        </p>

        <p class="mt-3">Por ello:</p>
    </div>

    <div class="bloque-intervencion">
        <div class="font-bold">LA INTERVENCION</div>
        <div class="font-bold">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
        <div class="font-bold">DE TUCUMAN</div>
        <div class="font-bold mt-1"><span class="underline">R E S U E L V E :</span></div>
    </div>

    <div class="space-y-3">
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 1º.- </span>RESCINDIR el Acta de Tenencia y Uso Provisorio Intransferible de Vivienda Única Social suscrita entre este Organismo y los
                <x-resolucion.segun si="nombre_cotitular" con="Señores" sin="Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE TITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular" placeholder="[DNI TITULAR]" /><x-resolucion.opcional si="nombre_cotitular"> y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular" placeholder="[NOMBRE COTITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular" placeholder="[DNI COTITULAR]" /></x-resolucion.opcional>
                por la causal Falta de Pago, de la vivienda identificada como
                <span class="font-bold">
                    MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> – CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" />
                    DEL EMPRENDIMIENTO "<x-resolucion.campo campo="nombre_emprendimiento" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />"
                </span>; y por lo expuesto en los considerandos de la presente Resolución.
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 2º.- </span>REVOCAR: la Resolución de Adjudicación N° <x-resolucion.campo campo="num_res_adjudicacion" placeholder="[NÚMERO]" /> en lo que respecta a los
                <x-resolucion.segun si="nombre_cotitular" con="Señores" sin="Señor/a" /> <span class="font-bold"><x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE TITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_titular" placeholder="[DNI TITULAR]" /><x-resolucion.opcional si="nombre_cotitular"> y <span class="font-bold"><x-resolucion.campo campo="nombre_cotitular" placeholder="[NOMBRE COTITULAR]" mayusculas /></span> DNI N° <x-resolucion.campo campo="dni_cotitular" placeholder="[DNI COTITULAR]" /></x-resolucion.opcional>
                por la causal Falta de Pago de la vivienda identificada como
                <span class="font-bold">
                    MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> – CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" />
                    DEL EMPRENDIMIENTO "<x-resolucion.campo campo="nombre_emprendimiento" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />"
                </span>; y por lo expuesto en los considerandos de la presente Resolución.
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 3º.- </span>INTIMAR a los
                <x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE TITULAR]" mayusculas />
                    <x-resolucion.opcional si="nombre_cotitular">/<x-resolucion.campo campo="nombre_cotitular" placeholder="[NOMBRE COTITULAR]" mayusculas /></x-resolucion.opcional>
                y/o cualesquiera que ocupare la vivienda para que en el plazo perentorio e improrrogable de 10 (diez) días, proceda a desalojar el inmueble libre de todo objeto y ocupantes, bajo apercibimiento de iniciar las acciones legales tendientes a recuperar el mismo.
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 4º.- </span>AUTORIZAR a la Dirección de Área de Regularización Dominial de este Organismo, a iniciar las acciones legales pertinentes para recuperar el inmueble en caso de así corresponder.
            </p>
        </div>

        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 5º.- </span>COMUNICAR a los interesados, Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección del Área de Recupero y Regularización Dominial a sus efectos.
            </p>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-box">
            <div class="linea-firma"></div>
            <div class="font-bold">MARIA GABRIELA LAZARTE</div>
            <div>CONTADORA FISCAL</div>
            <div>TRIBUNAL DE CUENTAS</div>
        </div>
        <div class="firma-box center">
            <img src="{{ asset('images/logo-provincia.png') }}" alt="Provincia" />
        </div>
        <div class="firma-box">
            <div class="linea-firma"></div>
            <div class="font-bold">Arq. HUGO CABRAL</div>
            <div>INTERVENTOR</div>
            <div>I.P.V.y D.U.</div>
        </div>
    </div>
</div>
</x-resolucion.documento>
