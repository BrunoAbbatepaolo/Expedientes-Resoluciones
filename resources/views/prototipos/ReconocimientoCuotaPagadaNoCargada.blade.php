{{-- resources/views/prototipos/reconocimiento-cuota-pagada-no-cargada.blade.php --}}
<div class="documento-resolucion" x-data="{ codigo: '', fechaResolucion: '{{ \Carbon\Carbon::now()->format('d-m-Y') }}', lugarFecha: 'San Miguel de Tucumán, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y') }}', numeroTramite: '{{ $numero_tramite ?? '' }}', c2_quien: '{{ $quien_suscribe ?? '' }}', c2_dni: '{{ $dni_suscribe ?? '' }}', c2_barrio: '{{ $manzana ?? '' }}', c2_casa: '{{ $lote ?? '' }}', c2_cuota_sg: '{{ $cuota_sin_gastos ?? '' }}', c2_nro_ult: '{{ $nro_ultima_cuota ?? '' }}', c2_vto_ult: '{{ $vencimiento_ultima_cuota ?? '' }}', c2_plazo: '{{ $plazo_total_plan ?? '' }}',
t1: [
{cuota:'', vto:'', capital:'', pagado:'', fpago:'', entidad:''},
{cuota:'', vto:'', capital:'', pagado:'', fpago:'', entidad:''}
],
t2: [
{cuota:'', vto:'', capital:'', pagado:'', fpago:'', transferencia:''}
],
addRow(tbl) {
let row = tbl === 't1'
? {cuota:'', vto:'', capital:'', pagado:'', fpago:'', entidad:''}
: {cuota:'', vto:'', capital:'', pagado:'', fpago:'', transferencia:''};
this[tbl].push(row);
},
removeRow(tbl) {
if (this[tbl].length <= 1) return;
let last = this[tbl][this[tbl].length - 1];
let hasData = Object.values(last).some(v => v && v.toString().trim() !== '');
if (hasData && !confirm('¿Eliminar esta fila?')) return;
this[tbl].pop();
} }">
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
                <div class="header-title">Reconocimiento de Cuota Pagada y No Cargada</div>
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="content">
            <!-- DATOS PRINCIPALES -->
            <div class="datos-principales">
                <div class="dato-card">
                    <div class="dato-label">Trámite N°</div>
                    <input type="text" x-model="numeroTramite" class="dato-value" placeholder="[NÚMERO_TRÁMITE]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:inherit;font-weight:inherit;font-family:inherit;color:inherit;width:100%;" />
                </div>
                <div class="dato-card">
                    <div class="dato-label">Fecha</div>
                    <input type="text" x-model="fechaResolucion" class="dato-value" placeholder="[FECHA]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:inherit;font-weight:inherit;font-family:inherit;color:inherit;width:100%;" />
                </div>
                <div class="dato-card">
                    <div class="dato-label">Código</div>
                    <div class="dato-value">
                        <input type="text" x-model="codigo" maxlength="10" placeholder="[CÓDIGO]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:inherit;font-weight:inherit;font-family:inherit;color:inherit;width:10ch;padding:0;margin:0;" />
                    </div>
                </div>
            </div>

            <!-- CAUSANTE -->
            <div class="causante">
                <div class="causante-label">Causante</div>
                <div class="causante-value" contenteditable="true">{{ !empty($causante) ? $causante : 'DEPARTAMENTO RECURSOS FINANCIEROS' }}</div>
            </div>

            <!-- LUGAR Y FECHA -->
            <div class="lugar-fecha" x-text="lugarFecha"></div>

            <!-- DESTINATARIO -->
            <div class="destinatario" contenteditable="true">
                SR. JEFE DEPARTAMENTO COMPUTOS ING. {{ !empty($jefe_computos) ? $jefe_computos : 'FEDERICO CONRAD' }}:
            </div>

            <!-- REFERENCIA -->
            <div class="referencia" contenteditable="true">
                REF.: RECONOCIMIENTO DE CUOTA PAGADA Y NO CARGADA - CÓDIGO (<input type="text" x-model="codigo" maxlength="10" placeholder="[CÓDIGO]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;color:#1e40af;text-transform:uppercase;width:10ch;padding:0;margin:0;" />)
            </div>

            <!-- CUERPO -->
            <div class="cuerpo" contenteditable="true">
                Me dirijo a Ud. a los fines de que se realice el reconocimiento de la cuota pagada y NO CARGADA segun la planilla adjuntada, adjuntando copia de los comprobantes originales.
<br>
                Atte-
            </div>
            <!-- CUERPO 2 -->
            <div class="cuerpo-2" contenteditable="true">
             

                <p><strong>El/la que suscribe:</strong> <input type="text" x-model="c2_quien" placeholder="[NOMBRE]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:350px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p><strong>DNI N°:</strong> <input type="text" x-model="c2_dni" placeholder="[DNI]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:200px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p>en su carácter de adjudicatario de la unidad identificada como:</p>

                <p>
                    <strong>C° Barrio:</strong> <input type="text" x-model="c2_barrio" placeholder="[BARRIO]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:120px;font-size:14px;font-family:inherit;color:#111827;" />
                    – <strong>C° Casa:</strong> <input type="text" x-model="c2_casa" placeholder="[CASA]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:120px;font-size:14px;font-family:inherit;color:#111827;" />
                </p>

                <p>cuyo plan de pagos vigente responde al siguiente esquema:</p>

                <p><strong>Cuota sin Gastos Administrativos:</strong> $<input type="text" x-model="c2_cuota_sg" placeholder="[MONTO]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:120px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p><strong>N° Última Cuota Emitida:</strong> <input type="text" x-model="c2_nro_ult" placeholder="[NRO]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:120px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p><strong>Vencimiento Última cuota Emitida:</strong> <input type="text" x-model="c2_vto_ult" placeholder="dd/mm/aaaa" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:150px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p><strong>Plazo Total del Plan:</strong> <input type="text" x-model="c2_plazo" placeholder="[PLAZO]" style="background:transparent;border:none;border-bottom:1px solid #999;outline:none;width:120px;font-size:14px;font-family:inherit;color:#111827;" /></p>

                <p>Adjunta a la presente, copia pagada y no cargadas.- Las detallamos a continuación:</p>

                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px;">
                    <thead>
                        <tr style="background:#1e3a5f; color:white;">
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Cuota N°</th>
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Fecha Vto.</th>
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Importe Capital</th>
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Importe Pagado</th>
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Fecha de Pago</th>
                            <th style="border:1px solid #ccc; padding:6px; text-align:center;">Pagada en</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in t1" :key="i">
                            <tr>
                                <td style="border:1px solid #ccc; padding:4px;"><input type="text" x-model="row.cuota" style="width:100%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                                <td style="border:1px solid #ccc; padding:4px;"><input type="text" x-model="row.vto" style="width:100%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                                <td style="border:1px solid #ccc; padding:4px;">$<input type="text" x-model="row.capital" style="width:80%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                                <td style="border:1px solid #ccc; padding:4px;">$<input type="text" x-model="row.pagado" style="width:80%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                                <td style="border:1px solid #ccc; padding:4px;"><input type="text" x-model="row.fpago" style="width:100%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                                <td style="border:1px solid #ccc; padding:4px;"><input type="text" x-model="row.entidad" style="width:100%;text-align:center;background:transparent;border:none;outline:none;font-family:inherit;font-size:13px;color:#111827;" /></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div class="flex justify-center gap-2 mt-2 mb-4" contenteditable="false">
                    <button type="button" @click.stop="addRow('t1')" class="w-9 h-9 rounded-full bg-emerald-500 text-white text-xl font-bold leading-none shadow-sm hover:shadow-md hover:bg-emerald-600 hover:scale-110 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-300 flex items-center justify-center" style="cursor:pointer;">+</button>
                    <button type="button" @click.stop="removeRow('t1')" class="w-9 h-9 rounded-full bg-rose-500 text-white text-xl font-bold leading-none shadow-sm hover:shadow-md hover:bg-rose-600 hover:scale-110 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-rose-300 flex items-center justify-center" style="cursor:pointer;">−</button>
                </div>



               
            </div>
        </div>
    </div>

    
</div>
