{{-- resources/views/prototipos/resicion.blade.php --}}
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
        <span contenteditable="true">estas actuaciones relacionadas con la vivienda identificada como </span>
        <span class="font-bold" contenteditable="true">
            MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} - CASA {{ !empty($lote) ? $lote : '[CASA]' }}
            DEL EMPRENDIMIENTO "{{ !empty($nombre_emprendimiento) ? strtoupper($nombre_emprendimiento) : '[NOMBRE DEL EMPRENDIMIENTO]' }}"
            @if(!empty($departamento))
                - {{ strtoupper($departamento) }}
            @endif
        </span>
        <span contenteditable="true">; y</span>
    </div>

    <div class="seccion-considerando mt-4">
        <p><span class="font-bold" contenteditable="true">CONSIDERANDO:</span></p>

        <p class="mt-2" contenteditable="true">
            Que, la vivienda mencionada fue otorgada a
            @if(!empty($nombre_titular) && !empty($dni_titular))
                @if(!empty($nombre_cotitular) && !empty($dni_cotitular))
                    los Señores <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }} y <span class="font-bold">{{ strtoupper($nombre_cotitular) }}</span> DNI N° {{ $dni_cotitular }}
                @else
                    el/la Señor/a <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }}
                @endif
            @else
                [NOMBRE Y DNI DEL/LOS TITULAR/ES]
            @endif
            mediante Acta de Tenencia y Uso Provisorio Intransferible Vivienda Única Social y Resolución de Adjudicación N° {{ !empty($num_res_adjudicacion) ? $num_res_adjudicacion : '[NÚMERO]' }}.
        </p>

        <p contenteditable="true">
            Que, del informe obrante a fs. {{ !empty($num_foja_informe) ? $num_foja_informe : '[FOJA]' }} se constata que los titulares, tras sendas intimaciones no regularizan la deuda que pesa sobre la unidad de referencia, monto que a la fecha {{ !empty($fecha_deuda) ? formatearFecha($fecha_deuda) : '[FECHA]' }} se actualiza a $ {{ !empty($monto_deuda) ? formatearMoneda($monto_deuda) : '[MONTO]' }} ({{ !empty($monto_deuda) ? num2letras($monto_deuda) : '[MONTO EN LETRAS]' }}), por lo que la falta de pago por parte de los
            @if(!empty($nombre_titular))
                {{ strtoupper($nombre_titular) }}
                @if(!empty($nombre_cotitular))
                    /{{ strtoupper($nombre_cotitular) }}
                @endif
            @else
                [TITULARES]
            @endif
            se encuadra en el incumplimiento de la cláusula Novena del contrato suscripto configurándose el supuesto de la cláusula Décimo Primera, facultando al Instituto de Vivienda de la cláusula Décimo Primera, dar por finalizado el mentado contrato y exigir el inmediato desalojo de la unidad, quedando obligados los adjudicatarios a restituir el inmueble.
        </p>

        <p contenteditable="true">
            Que, en consecuencia, la Falta de Pago por parte de los titulares se encuadra en el incumplimiento que habilita a dejar sin efecto el Acta de Tenencia y Uso Provisorio Intransferible de Vivienda Única Social y revocar la Resolución de Adjudicación por la causal falta de pago.
        </p>

        <p contenteditable="true">
            Que, a fs. {{ !empty($num_foja_dictamen) ? $num_foja_dictamen : '[FOJA]' }} {{ !empty($departamento) ? $departamento : '[DEPARTAMENTO]' }} de Recupero de Viviendas en fecha {{ !empty($fecha_dictamen) ? formatearFecha($fecha_dictamen) : '[FECHA]' }} emite Dictamen Legal N° {{ !empty($num_dictamen) ? $num_dictamen : '[NÚMERO]' }} en donde no se ofrecen objeciones legales para la procedencia del presente trámite.
        </p>

        <p class="mt-3" contenteditable="true">Por ello:</p>
    </div>

    <div class="bloque-intervencion">
        <div class="font-bold" contenteditable="true">LA INTERVENCION</div>
        <div class="font-bold" contenteditable="true">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
        <div class="font-bold" contenteditable="true">DE TUCUMAN</div>
        <div class="font-bold mt-1"><span class="underline" contenteditable="true">R E S U E L V E :</span></div>
    </div>

    <div class="space-y-3">
        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 1º.- </span>RESCINDIR el Acta de Tenencia y Uso Provisorio Intransferible de Vivienda Única Social suscrita entre este Organismo y los
                @if(!empty($nombre_titular) && !empty($dni_titular))
                    @if(!empty($nombre_cotitular) && !empty($dni_cotitular))
                        Señores <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }} y <span class="font-bold">{{ strtoupper($nombre_cotitular) }}</span> DNI N° {{ $dni_cotitular }}
                    @else
                        Señor/a <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }}
                    @endif
                @else
                    [NOMBRE Y DNI DEL/LOS TITULAR/ES]
                @endif
                por la causal Falta de Pago, de la vivienda identificada como
                <span class="font-bold">
                    MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} – CASA {{ !empty($lote) ? $lote : '[CASA]' }}
                    DEL EMPRENDIMIENTO "{{ !empty($nombre_emprendimiento) ? strtoupper($nombre_emprendimiento) : '[NOMBRE DEL EMPRENDIMIENTO]' }}"
                </span>; y por lo expuesto en los considerandos de la presente Resolución.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 2º.- </span>REVOCAR: la Resolución de Adjudicación N° {{ !empty($num_res_adjudicacion) ? $num_res_adjudicacion : '[NÚMERO]' }} en lo que respecta a los
                @if(!empty($nombre_titular) && !empty($dni_titular))
                    @if(!empty($nombre_cotitular) && !empty($dni_cotitular))
                        Señores <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }} y <span class="font-bold">{{ strtoupper($nombre_cotitular) }}</span> DNI N° {{ $dni_cotitular }}
                    @else
                        Señor/a <span class="font-bold">{{ strtoupper($nombre_titular) }}</span> DNI N° {{ $dni_titular }}
                    @endif
                @else
                    [NOMBRE Y DNI DEL/LOS TITULAR/ES]
                @endif
                por la causal Falta de Pago de la vivienda identificada como
                <span class="font-bold">
                    MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} – CASA {{ !empty($lote) ? $lote : '[CASA]' }}
                    DEL EMPRENDIMIENTO "{{ !empty($nombre_emprendimiento) ? strtoupper($nombre_emprendimiento) : '[NOMBRE DEL EMPRENDIMIENTO]' }}"
                </span>; y por lo expuesto en los considerandos de la presente Resolución.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 3º.- </span>INTIMAR a los
                @if(!empty($nombre_titular))
                    {{ strtoupper($nombre_titular) }}
                    @if(!empty($nombre_cotitular))
                        /{{ strtoupper($nombre_cotitular) }}
                    @endif
                @else
                    [TITULARES]
                @endif
                y/o cualesquiera que ocupare la vivienda para que en el plazo perentorio e improrrogable de 10 (diez) días, proceda a desalojar el inmueble libre de todo objeto y ocupantes, bajo apercibimiento de iniciar las acciones legales tendientes a recuperar el mismo.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 4º.- </span>AUTORIZAR a la Dirección de Área de Regularización Dominial de este Organismo, a iniciar las acciones legales pertinentes para recuperar el inmueble en caso de así corresponder.
            </p>
        </div>

        <div class="articulo">
            <p contenteditable="true">
                <span class="font-bold underline">Art. 5º.- </span>COMUNICAR a los interesados, Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección del Área de Recupero y Regularización Dominial a sus efectos.
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