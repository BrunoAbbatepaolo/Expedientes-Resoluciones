{{-- resources/views/prototipos/cancelaciones.blade.php --}}
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
        .documento-resolucion [contenteditable="true"] {
            outline: 1px dashed #ccc;
            padding: 2px 4px;
            border-radius: 2px;
            transition: outline 0.2s;
        }
        .documento-resolucion [contenteditable="true"]:hover {
            outline: 1px dashed #999;
            background-color: rgba(255, 255, 255, 0.5);
        }
        .documento-resolucion [contenteditable="true"]:focus {
            outline: 2px solid #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
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
            <div contenteditable="true">S.M. de Tucumán</div>
            <div contenteditable="true">{{ !empty($fecha_res) ? formatearFecha($fecha_res) : 'dd MMM YYYY' }}</div>
        </div>
    </div>

    <div class="expediente">
        <span class="underline" contenteditable="true">Expte. N° {{ !empty($num_exp) ? $num_exp : '[NÚMERO]' }}</span>
    </div>

    <div class="numero-resolucion">
        <h1><span class="underline" contenteditable="true">RESOLUCION N° {{ !empty($num_res) ? $num_res : '[NÚMERO]' }}</span></h1>
    </div>

    <div class="seccion-visto pl-8">
        <span class="font-bold" contenteditable="true">VISTO, </span>
        <span contenteditable="true">las presentes actuaciones mediante las cuales se solicita la Cancelación Definitiva y Levantamiento de Hipoteca en 1º Grado, correspondiente a la vivienda identificada como </span>
        <span class="font-bold" contenteditable="true">
            MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} -
            LOTE {{ !empty($lote) ? $lote : '[LOTE]' }}
            DEL BARRIO {{ !empty($nombre_barrio) ? strtoupper($nombre_barrio) : '[NOMBRE DEL BARRIO]' }}
        </span>
    </div>

    <div class="seccion-considerando mt-4">
        <p><span class="font-bold" contenteditable="true">CONSIDERANDO:</span></p>

        <p class="mt-2" contenteditable="true">
            Que, a fs. {{ !empty($num_foja_solicitud) ? $num_foja_solicitud : '[FOJA]' }} obra solicitud de Cancelación y Levantamiento de Hipoteca de la vivienda.
        </p>

        <p contenteditable="true">
            Que, a fs. {{ !empty($num_foja_informe) ? $num_foja_informe : '[FOJA]' }} obra informe del Departamento Recursos Financieros donde consta que la vivienda se encuentra cancelada con la conformidad dada por la Dirección de Área de Recupero y Regularización Dominial en fecha {{ !empty($fecha_cancelacion) ? formatearFechaLarga($fecha_cancelacion) : '[FECHA]' }} a fs. {{ !empty($num_foja_darrd) ? $num_foja_darrd : '[FOJA]' }}.
        </p>

        <p contenteditable="true">
            Que, a fs. {{ !empty($num_foja_dictamen) ? $num_foja_dictamen : '[FOJA]' }} glosa Dictamen Legal N° 3136/23, en el cual no se ofrecen objeciones legales para la procedencia del presente trámite.
        </p>

        <p class="mt-3" contenteditable="true">Es por lo considerado que:</p>
    </div>

    <div class="bloque-intervencion">
        <div class="font-bold" contenteditable="true">LA INTERVENCION</div>
        <div class="font-bold" contenteditable="true">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
        <div class="font-bold" contenteditable="true">DE TUCUMAN</div>
        <div class="font-bold mt-1"><span class="underline" contenteditable="true">RESUELVE</span></div>
    </div>

    <div class="space-y-3">
        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 1º.- </span>APROBAR la Cancelación Definitiva de la vivienda identificada como
                <span class="font-bold">
                    MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} -
                    LOTE {{ !empty($lote) ? $lote : '[LOTE]' }}
                    DEL BARRIO {{ !empty($nombre_barrio) ? strtoupper($nombre_barrio) : '[NOMBRE DEL BARRIO]' }}
                </span>, que fuera otorgada a
                @if(!empty($nombre_titular) && !empty($dni_titular))
                    @if(!empty($nombre_cotitular) && !empty($dni_cotitular))
                        los Señores <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }} y <span class="font-bold">{{ strtoupper($nombre_cotitular) }}</span> DNI N° {{ $dni_cotitular }}
                    @else
                        el/la Señor/a <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }}
                    @endif
                @else
                    [NOMBRE Y DNI DEL/LOS TITULAR/ES]
                @endif
                , en un todo de acuerdo a lo informado por el Departamento Recursos Financieros.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 2º.- </span>DISPONER el Levantamiento de Hipoteca en 1º Grado sobre el inmueble referido, oportunamente instrumentada en la Escritura Pública N° {{ !empty($num_escritura) ? $num_escritura : '[NÚMERO]' }} de fecha {{ !empty($fecha_escritura) ? formatearFechaLarga($fecha_escritura) : '[FECHA]' }}, pasada por ante {{ !empty($nombre_escribano) ? strtoupper($nombre_escribano) : '[NOMBRE DEL ESCRIBANO]' }}, Titular del Registro de Gobierno de la Provincia de Tucumán.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 3º.- </span>DEJAR ACLARADO que los titulares deberán presentarse en el Departamento Regularización Dominial de este Organismo, en el horario de 07:00 a 13:30 a los efectos de gestionar el levantamiento de Hipoteca del inmueble oportunamente otorgado.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 4º.- </span>COMUNICAR a los interesados; Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección del Área de Recupero y Regularización Dominial, a sus efectos.
            </p>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-box">
            <div class="linea-firma"></div>
            <div class="font-bold" contenteditable="true">MARIA GABRIELA LAZARTE</div>
            <div contenteditable="true">CONTADORA FISCAL</div>
            <div contenteditable="true">TRIBUNAL DE CUENTAS</div>
        </div>
        <div class="firma-box center">
            <img src="{{ asset('images/logo-provincia.png') }}" alt="Provincia" />
        </div>
        <div class="firma-box">
            <div class="linea-firma"></div>
            <div class="font-bold" contenteditable="true">Arq. HUGO CABRAL</div>
            <div contenteditable="true">INTERVENTOR</div>
            <div contenteditable="true">I.P.V.y D.U.</div>
        </div>
    </div>
</div>