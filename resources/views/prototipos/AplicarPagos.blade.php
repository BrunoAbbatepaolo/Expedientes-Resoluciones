{{-- resources/views/prototipos/AplicarPagos.blade.php — los datos se editan en el documento (ver components/resolucion/campo). --}}
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
                    <div class="dato-value"><x-resolucion.campo campo="num_exp" placeholder="[NÚMERO_EXPEDIENTE]" /></div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">Fecha</div>
                    <div class="dato-value"><x-resolucion.campo campo="fecha_res" tipo="date" formato="fecha" /></div>
                </div>
                <div class="dato-card">
                    <div class="dato-label">Código</div>
                    <div class="dato-value"><x-resolucion.campo campo="codigo_pago" placeholder="[CÓDIGO]" maxlength="10" /></div>
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
                REF.: APLICAR PAGO - CODIGO (<x-resolucion.campo campo="codigo_pago" placeholder="[CÓDIGO]" maxlength="10" />)
            </div>

            <!-- CUERPO -->
            <div class="cuerpo">
                Remito las presentes actuaciones a fin que se proceda a aplicar al CODIGO (<x-resolucion.campo campo="codigo_pago" placeholder="[CÓDIGO]" maxlength="10" />) EL pago especial de <span class="cuerpo-valores">$ <x-resolucion.campo campo="monto" placeholder="[MONTO]" /></span> a las <span class="cuerpo-valores"><x-resolucion.campo campo="cuotas_adeudadas" placeholder="[CANTIDAD]" /> cuotas adeudadas</span> según ESTADO DE DEUDA emitido por el sistema que se acompaña a la presente.
                <br><br>
                Sirva de atento nota.-
            </div>

            <!-- FIRMAS -->
            <div class="firmas">
                <div class="firma">
                    <div class="firma-linea"></div>
                    <x-resolucion.firmante numero="1" predeterminado="MARTA DEL YAMESEN" clase-nombre="firma-nombre" clase-cargo="firma-cargo" />
                </div>
                <div class="firma">
                    <div class="firma-linea"></div>
                    <x-resolucion.firmante numero="2" predeterminado="C.P.N. FLAVIA PATRICIA CORVALAN" clase-nombre="firma-nombre" clase-cargo="firma-cargo" />
                </div>
            </div>
        </div>
    </div>
</div>
</x-resolucion.documento>
