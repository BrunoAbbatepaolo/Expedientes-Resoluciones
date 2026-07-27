{{-- resources/views/prototipos/rectificacion.blade.php --}}
<div class="documento-resolucion">
    <style>
        .documento-resolucion {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.35;
            color: #000;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: transparent;
            box-sizing: border-box;
        }
        .documento-resolucion * {
            box-sizing: border-box;
        }
        .pagina {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            page-break-after: always;
            position: relative;
        }
        .pagina .indicador-pagina {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 11px;
            color: #999;
            font-style: italic;
        }
        .page {
            padding: 50px 60px;
            min-height: 1100px;
            position: relative;
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
        .documento-resolucion .pl-12 { padding-left: 3em; }
        .documento-resolucion .space-y-3 > * + * { margin-top: 0.75em; }
        .documento-resolucion .space-y-2 > * + * { margin-top: 0.5em; }
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
        .documento-resolucion .articulo-sangria {
            padding-left: 2em;
            margin-bottom: 0.75em;
        }
        .documento-resolucion .articulo-donde-dice {
            margin-top: 0.5em;
        }
        .documento-resolucion .recuadro-adjudicatarios table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .documento-resolucion .recuadro-adjudicatarios th,
        .documento-resolucion .recuadro-adjudicatarios td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: center;
            height: 22px;
        }
        .documento-resolucion .recuadro-adjudicatarios th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .documento-resolucion .recuadro-adjudicatarios td {
            background-color: #fff;
        }
        .documento-resolucion .recuadro-pagina2 {
            margin-top: 1em;
        }
        .documento-resolucion .recuadro-pagina2 table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .documento-resolucion .recuadro-pagina2 th,
        .documento-resolucion .recuadro-pagina2 td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
        }
        .documento-resolucion .recuadro-pagina2 th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .documento-resolucion .header-fecha-pagina2 {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5em;
            margin-bottom: 1em;
        }
        .documento-resolucion .seccion-debe-decir {
            margin-top: 1em;
        }
        .documento-resolucion .recuadro-adjudicatarios {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
            font-size: 12px;
        }
        .documento-resolucion .recuadro th,
        .documento-resolucion .recuadro td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .documento-resolucion .recuadro th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .documento-resolucion .logo-provincia {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
        }
        .documento-resolucion .logo-provincia img {
            width: 130px;
        }
        .documento-resolucion .logo-provincia-pagina1 {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
        .documento-resolucion .logo-provincia-pagina1 img {
            width: 100px;
        }
        .documento-resolucion .firmas {
            margin-top: 3em;
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

    <!-- PÁGINA 1 -->
    <div class="pagina">
        <div class="indicador-pagina">Página 1</div>
        <div class="page">
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
            <h1><span class="underline" contenteditable="true">RESOLUCIÓN N° {{ !empty($num_res) ? $num_res : '[NÚMERO]' }}</span></h1>
        </div>

        <div class="seccion-visto pl-8">
            <span class="font-bold" contenteditable="true">VISTO, </span>
            <span contenteditable="true">las presentes actuaciones mediante las cuales se solicita la Cancelación Definitiva correspondiente a la unidad habitacional identificada como </span>
            <span class="font-bold" contenteditable="true">
                MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} CASA {{ !empty($lote) ? $lote : '[CASA]' }}
                DEL PLAN "{{ !empty($nombre_plan) ? strtoupper($nombre_plan) : '[NOMBRE DEL PLAN]' }}"
            </span>
            <span contenteditable="true">en el marco de la Reglamentación de Cancelación Definitiva de Viviendas No Escrituradas, aprobada mediante Resolución N° {{ !empty($res_reglamentacion) ? $res_reglamentacion : '185/2017' }} y su modificatoria {{ !empty($res_modificatoria) ? $res_modificatoria : '779/2017' }}; y,</span>
        </div>

        <div class="seccion-considerando mt-4">
            <p><span class="font-bold" contenteditable="true">CONSIDERANDO:</span></p>

            <p class="mt-2" contenteditable="true">
                Que, a fs. {{ !empty($num_foja_solicitud) ? $num_foja_solicitud : '[FOJA]' }} obra solicitud de Cancelación y Escrituración de la vivienda.
            </p>

            <p contenteditable="true">
                Que, a fs. {{ !empty($num_foja_darrd) ? $num_foja_darrd : '[FOJA]' }} el Departamento Promoción Social solicita la rectificación de Resolución N° {{ !empty($num_res_adjudicacion) ? $num_res_adjudicacion : '[NÚMERO]' }} de fecha {{ !empty($fecha_res_adjudicacion) ? formatearFecha($fecha_res_adjudicacion) : '[FECHA]' }} ({{ !empty($num_foja_resolucion) ? $num_foja_resolucion : '[FOJA]' }}), ya que se observó un error involuntario en lo que respecta al Número de Documento del Co-Titular, se adjunta D.N.I. ({{ !empty($num_foja_dni) ? $num_foja_dni : '[FOJA]' }}) para identificar correctamente el número.
            </p>

            <p contenteditable="true">
                Que, a fs. {{ !empty($num_foja_informe) ? $num_foja_informe : '[FOJA]' }} obra informe del Departamento Recursos Financieros donde consta que la vivienda se encuentra cancelada con la conformidad dada por la Dirección de Área de Recupero y Regularización Dominial en fecha {{ !empty($fecha_cancelacion) ? formatearFechaLarga($fecha_cancelacion) : '[FECHA]' }} a fs. {{ !empty($num_foja_darrd_conf) ? $num_foja_darrd_conf : '[FOJA]' }}.
            </p>

            <p contenteditable="true">
                Que, de acuerdo al Dictamen Legal N° {{ !empty($num_dictamen) ? $num_dictamen : '[NÚMERO]' }} a fs. {{ !empty($num_foja_dictamen) ? $num_foja_dictamen : '[FOJA]' }} y a lo solicitado por el Departamento Promoción Social, corresponde dictar el instrumento legal respective rectificando el número de documento del Co-Titular y aprobando la cancelación de la vivienda.
            </p>

            <p class="mt-3" contenteditable="true">Es por lo considerado que</p>
        </div>

        <div class="bloque-intervencion">
            <div class="font-bold" contenteditable="true">LA INTERVENCION</div>
            <div class="font-bold" contenteditable="true">DEL INSTITUTO PROVINCIAL DE VIVIENDA Y DESARROLLO URBANO</div>
            <div class="font-bold" contenteditable="true">DE TUCUMAN</div>
            <div class="font-bold mt-1"><span class="underline" contenteditable="true">R E S U E L V E</span></div>
        </div>

        <div class="space-y-2">
            <div class="articulo">
                <p contenteditable="true">
                    <span class="font-bold underline">Art. 1º.- </span><span contenteditable="true">RECTIFICAR</span>: el Artículo 1° de la Resolución N° {{ !empty($num_res_adjudicacion) ? $num_res_adjudicacion : '[NÚMERO]' }} de fecha {{ !empty($fecha_res_adjudicacion) ? formatearFecha($fecha_res_adjudicacion) : '[FECHA]' }} Orden {{ !empty($orden) ? $orden : '[ORDEN]' }} en lo que respecta al número de documento del Co-Titular.
                </p>
            </div>

            <div class="articulo-donde-dice">
                <p class="font-bold mb-1" contenteditable="true">DONDE DICE:</p>
                <div class="articulo-sangria">
                    <p contenteditable="true">
                        <span class="font-bold underline">Art. 1º.- </span><span contenteditable="true">ADJUDICAR</span>:
                        @if(!empty($texto_donde_dice))
                            {{ $texto_donde_dice }}
                        @else
                            {{ !empty($cantidad_viviendas) ? $cantidad_viviendas : '[CANTIDAD]' }} ({{ !empty($cantidad_letras) ? $cantidad_letras : '[CANTIDAD EN LETRAS]' }}) Unidades habitacionales Plan: "{{ !empty($nombre_plan) ? strtoupper($nombre_plan) : '[NOMBRE DEL PLAN]' }}", fundado en causales tales como: por haber dado cumplimiento con los requisitos preestablecidos en la Ley FO.NA.VI N° 21581/77, Sus Resoluciones Reglamentarias y demás Resoluciones I.P.V.D.U y por lo dictaminado por el Área Legal, según es siguiente detalle:
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="recuadro-adjudicatarios" contenteditable="true">
            <table>
                <tr>
                    <th style="width: 40px;">Ord.</th>
                    <th style="width: 180px;">Apellidos y Nombres</th>
                    <th style="width: 90px;">Documento</th>
                    <th style="width: 40px;">MZ.</th>
                    <th style="width: 40px;">Lote</th>
                    <th style="width: 80px;">Fecha Nac.</th>
                    <th style="width: 40px;">Cupo</th>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="logo-provincia-pagina1">
            <img src="{{ asset('images/logo-provincia.png') }}" alt="Provincia" style="width: 100px;" />
        </div>
        </div>
    </div>

    <!-- PÁGINA 2 -->
    <div class="pagina">
        <div class="indicador-pagina">Página 2</div>
        <div class="page">
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
            <h1><span class="underline" contenteditable="true">RESOLUCIÓN N° {{ !empty($num_res) ? $num_res : '[NÚMERO]' }}</span></h1>
        </div>

        <div class="seccion-debe-decir mt-4">
            <p class="font-bold mb-2" contenteditable="true">DEBE DECIR:</p>

            <div class="articulo">
                <p contenteditable="true">
                    <span class="font-bold underline">Art. 1º.- </span><span contenteditable="true">ADJUDICAR</span>:
                    @if(!empty($cantidad_viviendas))
                        {{ $cantidad_viviendas }} ({{ !empty($cantidad_letras) ? $cantidad_letras : '[CANTIDAD EN LETRAS]' }}) Unidades habitacionales Plan: "{{ !empty($nombre_plan) ? strtoupper($nombre_plan) : '[NOMBRE DEL PLAN]' }}", fundado en causales tales como: por haber dado cumplimiento con los requisitos preestablecidos en la Ley FO.NA.VI N° 21581/77, Sus Resoluciones Reglamentarias y demás Resoluciones I.P.V.D.U. y por lo dictaminado por el Área Legal, según es siguiente detalle:
                    @else
                        [CANTIDAD] ([CANTIDAD EN LETRAS]) Unidades habitacionales Plan: "{{ !empty($nombre_plan) ? strtoupper($nombre_plan) : '[NOMBRE DEL PLAN]' }}", fundado en causales tales como: por haber dado cumplimiento con los requisitos preestablecidos en la Ley FO.NA.VI N° 21581/77, Sus Resoluciones Reglamentarias y demás Resoluciones I.P.V.D.U. y por lo dictaminado por el Área Legal, según es siguiente detalle:
                    @endif
                </p>
            </div>

            <div class="recuadro-pagina2" contenteditable="true">
                <table>
                    <tr>
                        <th style="width: 40px;">Ord</th>
                        <th style="width: 180px;">Apellidos y Nombres</th>
                        <th style="width: 90px;">Documento</th>
                        <th style="width: 40px;">Mz</th>
                        <th style="width: 40px;">Lote</th>
                        <th style="width: 80px;">Fecha Nac</th>
                        <th style="width: 40px;">Cupo</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="space-y-3 mt-4">
            <div class="articulo">
                <p contenteditable="true">
                    <span class="font-bold underline">Art. 2º.- </span>APROBAR la Cancelación Definitiva, de la vivienda identificada como MANZANA {{ !empty($manzana) ? $manzana : '[MANZANA]' }} LOTE {{ !empty($lote) ? $lote : '[LOTE]' }} del PLAN "{{ !empty($nombre_plan) ? strtoupper($nombre_plan) : '[NOMBRE DEL PLAN]' }}" cuyos titulares son los Señores
                    @if(!empty($nombre_titular_anterior) && !empty($dni_titular_anterior))
                        {{ strtoupper($nombre_titular_anterior) }} DNI N° {{ $dni_titular_anterior }}
                    @else
                        [NOMBRE TITULAR] DNI N° [DNI]
                    @endif
                    @if(!empty($nombre_cotitular_anterior) && !empty($dni_cotitular_anterior))
                        y {{ strtoupper($nombre_cotitular_anterior) }} DNI N° {{ $dni_cotitular_anterior }}
                    @endif
                    según surge de la Resolución N° {{ !empty($num_res_adjudicacion) ? $num_res_adjudicacion : '[NÚMERO]' }}/2012 en un todo de acuerdo a informes obrantes en auto.
                </p>
            </div>

            <div class="articulo">
                <p contenteditable="true">
                    <span class="font-bold underline">Art. 3º.- </span>DEJAR ACLARADO que los titulares deberán presentarse en el Departamento Regularización Dominial de este Organismo, en el horario de 07,30 a 13,30 a los efectos de gestionar la Escritura del inmueble oportunamente adjudicado.-
                </p>
            </div>

            <div class="articulo">
                <p contenteditable="true">
                    <span class="font-bold underline">Art. 4º.- </span>COMUNICAR a los interesados; Áreas y Departamentos de este Instituto, a los fines que a cada uno les competa. Cumplido, PASE a la Dirección de Área de Recupero y Regularización Dominial, a sus efectos.-
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
        </div>
    </div>
</div>