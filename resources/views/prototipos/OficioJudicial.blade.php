{{-- resources/views/prototipos/OficioJudicial.blade.php — los datos se editan en el documento (ver components/resolucion/campo). --}}
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
        <span>el Expediente del rubro; y</span>
    </div>

    <div class="seccion-considerando mt-4">
        <p><span class="font-bold">CONSIDERANDO:</span></p>

        <p class="mt-2">Que, mediante oficio librado por <x-resolucion.campo campo="oficina_judicial" placeholder="[OFICINA / JUZGADO QUE LIBRA EL OFICIO]" />, autos caratulados como: <x-resolucion.campo campo="caratula" placeholder="[CARÁTULA DE LOS AUTOS]" /> - EXPTE. Nº <x-resolucion.campo campo="expte_judicial" placeholder="[Nº EXPTE. JUDICIAL]" /> - <x-resolucion.campo campo="juzgado" placeholder="[JUZGADO]" /> se solicita a este Organismo dar cumplimiento con la Resolución que a continuación se transcribe…<x-resolucion.campo campo="fecha_resolucion_judicial" placeholder="[FECHA]" tipo="date" formato="fecha_larga" />…mediante el cual se pone en conocimiento el Convenio de Adjudicación de derechos y acciones sobre el bien inmueble aquí homologado mediante el cual <span class="font-bold"><x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE DEL CEDENTE]" /></span> DNI Nº <x-resolucion.campo campo="dni_titular" placeholder="[DNI DEL CEDENTE]" /> cede y transfiere el <x-resolucion.campo campo="porcentaje" placeholder="[%]" predeterminado="100" />% de los derechos y acciones sobre la unidad habitacional identificada como <span class="font-bold">SECTOR <x-resolucion.campo campo="sector" placeholder="[SECTOR]" /> - MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> - CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" /></span>, del Emprendimiento <x-resolucion.campo campo="nombre_plan" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />, a favor de <span class="font-bold"><x-resolucion.campo campo="nombre_titular_nuevo" placeholder="[NOMBRE DEL CESIONARIO]" /></span> DNI Nº <x-resolucion.campo campo="dni_titular_nuevo" placeholder="[DNI DEL CESIONARIO]" />.</p>

        <p>Que, a fs. <x-resolucion.campo campo="num_foja_adjudicaciones" placeholder="[FOJA]" /> obra informe de Departamento Adjudicaciones, según el cual por Resolución Nº <x-resolucion.campo campo="num_res_adjudicacion" placeholder="[NÚMERO]" /> de fecha <x-resolucion.campo campo="fecha_res_adjudicacion" placeholder="[FECHA]" tipo="date" formato="fecha" /> en su orden <x-resolucion.campo campo="orden" placeholder="[ORDEN]" /> se consignan los datos de los Señores <span class="font-bold"><x-resolucion.campo campo="nombre_titular" placeholder="[NOMBRE DEL CEDENTE]" /></span> DNI Nº <x-resolucion.campo campo="dni_titular" placeholder="[DNI DEL CEDENTE]" /> y <span class="font-bold"><x-resolucion.campo campo="nombre_titular_nuevo" placeholder="[NOMBRE DEL CESIONARIO]" /></span> DNI Nº <x-resolucion.campo campo="dni_titular_nuevo" placeholder="[DNI DEL CESIONARIO]" />, como Adjudicatarios de la mencionada vivienda.</p>

        <p>Que, a fs. <x-resolucion.campo campo="num_foja_informe" placeholder="[FOJA]" /> obra informe del Departamento Recursos Financieros donde consta que la vivienda se encuentra cancelada con la conformidad dada por la Dirección de Área de Recupero y Regularización Dominial, en fecha <x-resolucion.campo campo="fecha_cancelacion" placeholder="[FECHA]" tipo="date" formato="fecha" />, fs. <x-resolucion.campo campo="num_foja_darrd" placeholder="[FOJA]" />.</p>

        <p>Que, de acuerdo al Dictamen Legal N° <x-resolucion.campo campo="num_dictamen" placeholder="[NÚMERO DE DICTAMEN]" />, fs. <x-resolucion.campo campo="num_foja_dictamen" placeholder="[FOJA]" /> corresponde dictar el instrumento legal respectivo tomando razón de la mencionada Resolución y aprobar el Cambio de Titularidad de la vivienda y la Cancelación Definitiva.</p>

        <p class="mt-3">Por ello:</p>
    </div>

    <div class="bloque-intervencion">
        <div class="font-bold">LA INTERVENCION</div>
        <div class="font-bold">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
        <div class="font-bold">DE TUCUMAN</div>
        <div class="font-bold mt-1"><span class="underline">RESUELVE</span></div>
    </div>

    <div class="space-y-3">
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 1º.- </span>TOMAR RAZON de la Resolución de fecha <x-resolucion.campo campo="fecha_resolucion_judicial" placeholder="[FECHA]" tipo="date" formato="fecha_larga" /> recaída en el juicio caratulado como: “<x-resolucion.campo campo="caratula" placeholder="[CARÁTULA DE LOS AUTOS]" /> - EXPTE. Nº <x-resolucion.campo campo="expte_judicial" placeholder="[Nº EXPTE. JUDICIAL]" />”; <x-resolucion.campo campo="juzgado" placeholder="[JUZGADO]" />.
            </p>
        </div>
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 2º.- </span>APROBAR el Cambio de Titularidad de la vivienda identificada como: <span class="font-bold">SECTOR <x-resolucion.campo campo="sector" placeholder="[SECTOR]" /> - MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> - CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" /></span> del Emprendimiento “<x-resolucion.campo campo="nombre_plan" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />” a favor de <span class="font-bold"><x-resolucion.campo campo="nombre_titular_nuevo" placeholder="[NOMBRE DEL CESIONARIO]" /></span> DNI Nº <x-resolucion.campo campo="dni_titular_nuevo" placeholder="[DNI DEL CESIONARIO]" /> del <x-resolucion.campo campo="porcentaje" placeholder="[%]" predeterminado="100" />% de dicho inmueble, en un todo de acuerdo a lo expuesto en los considerandos de la presente Resolución.
            </p>
        </div>
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 3º.- </span>APROBAR la Cancelación Definitiva de la unidad identificada como <span class="font-bold">SECTOR <x-resolucion.campo campo="sector" placeholder="[SECTOR]" /> - MANZANA <x-resolucion.campo campo="manzana" placeholder="[MANZANA]" /> - CASA <x-resolucion.campo campo="lote" placeholder="[CASA]" /></span> DEL EMPRENDIMIENTO “<x-resolucion.campo campo="nombre_plan" placeholder="[NOMBRE DEL EMPRENDIMIENTO]" mayusculas />”, en un todo de acuerdo a lo informado por el Departamento Recursos Financieros en el marco de la Reglamentación de Cancelación Definitiva de Viviendas No Escrituradas, aprobada mediante Resolución Nº <x-resolucion.campo campo="res_reglamentacion" placeholder="[RESOLUCIÓN]" predeterminado="185/2017" /> y su modificatoria <x-resolucion.campo campo="res_modificatoria" placeholder="[RESOLUCIÓN]" predeterminado="779/2017" />.
            </p>
        </div>
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 4º.- </span>DEJAR ACLARADO que el/la titular deberá presentarse en el Departamento Regularización Dominial de este Organismo, en el horario de 07:00 a 13:30 hs. a los efectos de gestionar la Escritura del inmueble oportunamente otorgado.
            </p>
        </div>
        <div class="articulo">
            <p>
                <span class="font-bold underline">Art. 5º.- </span>COMUNICAR a: el/la interesado/a; Juzgado Oficiante, Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección de Adjudicaciones, a sus efectos.
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
