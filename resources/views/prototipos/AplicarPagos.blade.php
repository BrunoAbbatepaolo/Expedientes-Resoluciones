{{-- resources/views/prototipos/aplicar-pagos.blade.php --}}
<div class="documento-resolucion" x-data="{ codigo: '', fechaResolucion: '{{ \Carbon\Carbon::now()->format('d-m-Y') }}', lugarFecha: 'San Miguel de Tucumán, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y') }}', numeroTramite: '{{ $numero_tramite ?? '' }}' }">
    <style>
        @include('prototipos._estilos')
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
                <div class="header-title">Aplicar Pago</div>
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
                REF.: APLICAR PAGO - CODIGO (<input type="text" x-model="codigo" maxlength="10" placeholder="[CÓDIGO]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:13px;font-weight:600;color:#1e40af;text-transform:uppercase;width:10ch;padding:0;margin:0;" />)
            </div>

            <!-- CUERPO -->
            <div class="cuerpo" contenteditable="true">
                Remito las presentes actuaciones a fin que se proceda a aplicar al CODIGO (<input type="text" x-model="codigo" maxlength="10" placeholder="[CÓDIGO]" onfocus="this.select()" style="background:transparent;border:none;outline:none;font-size:14px;font-weight:700;color:#111827;width:10ch;padding:0;margin:0;" />) EL pago especial de <span class="cuerpo-valores">$ {{ !empty($monto) ? $monto : '[MONTO]' }}</span> a las <span class="cuerpo-valores">{{ !empty($cuotas_adeudadas) ? $cuotas_adeudadas : '[CANTIDAD]' }} cuotas adeudadas</span> según ESTADO DE DEUDA emitido por el sistema que se acompaña a la presente.
                <br><br>
                Sirva de atento nota.-
            </div>

            <!-- FIRMAS -->
            <div class="firmas">
                <div class="firma">
                    <div class="firma-linea"></div>
                    <div class="firma-nombre" contenteditable="true">{{ !empty($firma_1_nombre) ? $firma_1_nombre : 'MARTA DEL YAMESEN' }}</div>
                    <div class="firma-cargo" contenteditable="true">{{ !empty($firma_1_cargo) ? $firma_1_cargo : 'DPTO. REC. FINANCIEROS' }}</div>
                    <div class="firma-cargo" contenteditable="true">I.P.V. y D.U.</div>
                </div>
                <div class="firma">
                    <div class="firma-linea"></div>
                    <div class="firma-nombre" contenteditable="true">{{ !empty($firma_2_nombre) ? $firma_2_nombre : 'C.P.N. FLAVIA PATRICIA CORVALAN' }}</div>
                    <div class="firma-cargo" contenteditable="true">{{ !empty($firma_2_cargo) ? $firma_2_cargo : 'JEFA (Int.) DEPTO. RECURSOS FINANC.' }}</div>
                    <div class="firma-cargo" contenteditable="true">I.P.V. y D.U.</div>
                </div>
            </div>
        </div>
    </div>

    
</div>