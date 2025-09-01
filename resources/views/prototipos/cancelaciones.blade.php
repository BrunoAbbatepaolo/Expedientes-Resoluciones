{{-- resources/views/prototipos/cancelaciones.blade.php --}}
<div class="documento-resolucion">
    <style>
        .documento-resolucion {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm;
            background: white;
        }
        .documento-resolucion p {
            margin-bottom: 1em;
            text-align: justify;
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
        .documento-resolucion .uppercase {
            text-transform: uppercase;
        }
        .documento-resolucion .mt-4 {
            margin-top: 1.5em;
        }
        .documento-resolucion .header-fecha {
            text-align: right;
            margin-bottom: 2em;
            font-size: 14px;
        }
        .documento-resolucion .expediente {
            margin-bottom: 1em;
            font-size: 14px;
        }
        .documento-resolucion .numero-resolucion {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 2em;
        }
        .documento-resolucion .seccion-visto {
            margin-bottom: 2em;
        }
        .documento-resolucion .seccion-considerando {
            margin-bottom: 2em;
        }
        .documento-resolucion .encabezado-resuelve {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2em 0;
            line-height: 1.4;
            font-size: 14px;
        }
        .documento-resolucion .articulo {
            margin-bottom: 1.5em;
        }
        .documento-resolucion .articulo-titulo {
            font-weight: bold;
            display: inline;
        }
        .documento-resolucion .firmas {
            margin-top: 3em;
            page-break-inside: avoid;
        }
        .documento-resolucion .linea-firma {
            margin-bottom: 1.5em;
            border-bottom: 1px solid #000;
            width: 300px;
            display: inline-block;
        }
        .documento-resolucion .campo-vacio {
            color: #999;
            font-style: italic;
        }
        @media print {
            .documento-resolucion {
                padding: 1cm;
                box-shadow: none;
            }
        }
    </style>

    <div class="header-fecha">
        S.M. de Tucumán{{ !empty($fecha_res) ? ', ' . $this->formatearFecha($fecha_res) : '' }}
    </div>

    <div class="expediente">
        Expte. N° {{ !empty($num_exp) ? $num_exp : '[Número de Expediente]' }}
    </div>

    <div class="numero-resolucion">
        RESOLUCIÓN N° {{ !empty($num_res) ? $num_res : '[Número de Resolución]' }}
    </div>

    <div class="seccion-visto">
        <p>
            <span class="font-bold">VISTO</span>, las presentes actuaciones mediante las cuales se solicita la Cancelación Definitiva y Levantamiento de Hipoteca en 1º Grado, correspondiente a la vivienda identificada como 
            <span class="font-bold">
                MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} - 
                LOTE {{ !empty($lote) ? $lote : '[LOTE]' }} 
                DEL BARRIO {{ !empty($nombre_barrio) ? strtoupper($nombre_barrio) : '[NOMBRE DEL BARRIO]' }}
            </span>; y
        </p>
    </div>

    <div class="seccion-considerando">
        <p><span class="font-bold">CONSIDERANDO:</span></p>

        <p>
            Que, a fs. {{ !empty($num_foja_solicitud) ? $num_foja_solicitud : '[FOJA]' }} obra solicitud de Cancelación y Levantamiento de Hipoteca de la vivienda.
        </p>

        <p>
            Que, a fs. {{ !empty($num_foja_informe) ? $num_foja_informe : '[FOJA]' }} obra informe del Departamento Recursos Financieros donde consta que la vivienda se encuentra cancelada con la conformidad dada por la Dirección de Área de Recupero y Regularización Dominial en fecha {{ !empty($fecha_cancelacion) ? $this->formatearFechaLarga($fecha_cancelacion) : '[FECHA]' }} a fs. {{ !empty($num_foja_darrd) ? $num_foja_darrd : '[FOJA]' }}.
        </p>

        <p>
            Que, a fs. {{ !empty($num_foja_dictamen) ? $num_foja_dictamen : '[FOJA]' }} glosa Dictamen Legal N° 3136/23, en el cual no se ofrecen objeciones legales para la procedencia del presente trámite.
        </p>

        <p>Es por lo considerado que:</p>
    </div>

    <div class="encabezado-resuelve">
        LA INTERVENCIÓN<br>
        DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO<br>
        DE TUCUMÁN<br>
        RESUELVE
    </div>

    <div class="articulo">
        <p>
            <span class="articulo-titulo">Art. 1º.-</span> APROBAR la Cancelación Definitiva de la vivienda identificada como 
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
        <p>
            <span class="articulo-titulo">Art. 2º.-</span> DISPONER el Levantamiento de Hipoteca en 1º Grado sobre el inmueble referido, oportunamente instrumentada en la Escritura Pública N° {{ !empty($num_escritura) ? $num_escritura : '[NÚMERO]' }} de fecha {{ !empty($fecha_escritura) ? $this->formatearFechaLarga($fecha_escritura) : '[FECHA]' }}, pasada por ante 
            @if(!empty($nombre_escribano))
                el/la Escribano/a <span class="font-bold">{{ strtoupper($nombre_escribano) }}</span>
            @else
                [NOMBRE DEL ESCRIBANO]
            @endif
            , Titular del Registro de Gobierno de la Provincia de Tucumán.
        </p>
    </div>

    <div class="articulo">
        <p>
            <span class="articulo-titulo">Art. 3º.-</span> DEJAR ACLARADO que los titulares deberán presentarse en el Departamento Regularización Dominial de este Organismo, en el horario de 07:00 a 13:30 a los efectos de gestionar el levantamiento de Hipoteca del inmueble oportunamente otorgado.
        </p>
    </div>

    <div class="articulo">
        <p>
            <span class="articulo-titulo">Art. 4º.-</span> COMUNICAR a los interesados; Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección del Área de Recupero y Regularización Dominial, a sus efectos.
        </p>
    </div>

    <div class="firmas">
        <p class="font-bold">FIRMAS:</p>
        <p>1. <span class="linea-firma"></span></p>
        <p>2. <span class="linea-firma"></span></p>
        <p>3. <span class="linea-firma"></span></p>
    </div>
</div>