<?php

use App\Services\Resoluciones\AnalizadorResolucion;

function analizar(string $texto): array
{
    return (new AnalizadorResolucion)->analizar($texto);
}

test('Cancelaciones: detecta el tipo y rescata los datos principales', function () {
    $r = analizar("S.M. de Tucumán, 20 de mayo de 2026\n Expte. N° 1234/440-2026\n RESOLUCION N° 55/2026\n VISTO,\n las presentes actuaciones mediante las cuales se solicita la Cancelación Definitiva y Levantamiento de Hipoteca en 1º Grado, correspondiente a la vivienda identificada como\n MANZANA 12 -\n LOTE 7\n DEL BARRIO Los Aromos\n CONSIDERANDO:\n Que, a fs. 3 obra solicitud de Cancelación y Levantamiento de Hipoteca de la vivienda.\n Que, a fs. 9 obra informe del Departamento Recursos Financieros.\n Que, a fs. 14 glosa Dictamen Legal N° 3136/23, en el cual no se ofrecen objeciones.\n Art. 1º.- APROBAR la Cancelación Definitiva, que fuera otorgada a\n los Señores PÉREZ JUAN DNI N° 20.123.456 y GÓMEZ ANA DNI N° 21.654.321\n , en un todo de acuerdo.\n Art. 2º.- DISPONER el Levantamiento de Hipoteca, instrumentada en la Escritura Pública N° 88 de fecha 3 de marzo de 2010, pasada por ante RAMOS LUIS, Titular del Registro de Gobierno.");

    expect($r['tipo'])->toBe('Cancelaciones')
        ->and($r['datos'])->toMatchArray([
            'num_exp' => '1234/440-2026',
            'num_res' => '55/2026',
            'fecha_res' => '2026-05-20',
            'manzana' => '12',
            'lote' => '7',
            'nombre_barrio' => 'Los Aromos',
            'num_foja_solicitud' => '3',
            'num_foja_informe' => '9',
            'num_foja_dictamen' => '14',
            'nombre_titular' => 'PÉREZ JUAN',
            'dni_titular' => '20.123.456',
            'nombre_cotitular' => 'GÓMEZ ANA',
            'dni_cotitular' => '21.654.321',
            'num_escritura' => '88',
            'nombre_escribano' => 'RAMOS LUIS',
        ]);
});

test('Rescisiones se distingue por la falta de pago y el acta de tenencia', function () {
    $r = analizar('RESOLUCION N° 70/2026 VISTO, estas actuaciones relacionadas con la vivienda MANZANA 3 - CASA 9 CONSIDERANDO: Que, la falta de pago por parte de los titulares se encuadra en el incumplimiento que habilita a dejar sin efecto el Acta de Tenencia y Uso Provisorio y exigir el inmediato desalojo de la unidad.');

    expect($r['tipo'])->toBe('Resciciones')
        ->and($r['datos']['num_res'])->toBe('70/2026');
});

test('Transferencia y Transferencia-Cancelación no se confunden', function () {
    $transferencia = analizar('VISTO, estas actuaciones mediante las cuales se solicita autorización de transferencia de la unidad habitacional. Que, a fs. 5 se encuentra adjunto el Boleto de Venta. Art. 1º.- APROBAR la transferencia de la unidad.');
    $conCancelacion = analizar('VISTO, estas actuaciones mediante las cuales se solicita autorización de Transferencia y aprobación de Cancelación de la unidad habitacional. Art. 1º.- APROBAR la Transferencia de la unidad.');

    expect($transferencia['tipo'])->toBe('Transferencias')
        ->and($conCancelacion['tipo'])->toBe('Transferencia-Cancelacion');
});

test('Transferencias: separa titular anterior y titular nuevo', function () {
    $r = analizar('RESOLUCION N° 9/2026 se solicita autorización de transferencia de la unidad habitacional. Que, fue asignada a los Señores LOPEZ MARIA DNI N° 11.111.111 y DIAZ PEDRO DNI N° 22.222.222 Que, el Boleto de Venta a favor de la Señora SUAREZ ROSA DNI N° 33.333.333. Art. 1º.- APROBAR la transferencia.');

    expect($r['tipo'])->toBe('Transferencias')
        ->and($r['datos'])->toMatchArray([
            'nombre_titular_anterior' => 'LOPEZ MARIA',
            'dni_titular_anterior' => '11.111.111',
            'nombre_cotitular_anterior' => 'DIAZ PEDRO',
            'nombre_titular_nuevo' => 'SUAREZ ROSA',
            'dni_titular_nuevo' => '33.333.333',
        ]);
});

test('Aplicar pago: toma monto, cuotas y código', function () {
    $r = analizar('INSTITUTO PROVINCIAL DE VIVIENDA Aplicar Pago SR. JEFE DEPARTAMENTO COMPUTOS ING. GARCIA JORGE: REF.: APLICAR PAGO - CODIGO (4567) Remito las presentes actuaciones a fin que se proceda a aplicar al CODIGO (4567) EL pago especial de $ 12.345,67 a las 6 cuotas adeudadas según ESTADO DE DEUDA.');

    expect($r['tipo'])->toBe('AplicarPagos')
        ->and($r['datos'])->toMatchArray(['codigo_pago' => '4567', 'monto' => '12345.67', 'cuotas_adeudadas' => '6', 'jefe_computos' => 'GARCIA JORGE']);
});

test('Reconocimiento de cuota: distingue pagada dos veces de pagada y no cargada', function () {
    $dos = analizar('Reconocimiento de Cuota Pagada Dos Veces. Me dirijo a Ud. a los fines de que se realice el reconocimiento de la cuota pagada DOBLEMENTE. El/la que suscribe: ROMERO LUCIA DNI N°: 30.555.666 en su carácter de adjudicatario');
    $noCargada = analizar('Reconocimiento de Cuota Pagada y No Cargada. Me dirijo a Ud. a los fines de que se realice el reconocimiento de la cuota pagada y NO CARGADA segun la planilla.');

    expect($dos['tipo'])->toBe('ReconocimientoCuotaPagadaDosVeces')
        ->and($dos['datos'])->toMatchArray(['quien_suscribe' => 'ROMERO LUCIA', 'dni_suscribe' => '30.555.666'])
        ->and($noCargada['tipo'])->toBe('ReconocimientoCuotaPagadaNoCargada');
});

test('un texto que no es ninguna resolución conocida no devuelve tipo ni datos', function () {
    $r = analizar('Receta de empanadas tucumanas: carne cortada a cuchillo, cebolla de verdeo, comino y pimentón.');

    expect($r)->toBe(['tipo' => null, 'datos' => []]);
});

test('lee una cancelación real: palabras cortadas, manzana con letra, Señora y barrio entre comillas', function () {
    $texto = "S.M.de Tucumán,\nExpte. N° 10241/440-2025.-\nR E S O L U C I O N N ° .\n"
        ."VISTO, la Cance-\nlación Definitiva y Levantamiento de Hipoteca en 2º Grado, correspondiente a la vivienda identificada como Manzana C - UNIDAD 03 DEL BARRIO “200 VIVIENDAS EN YERBA BUENA ”; y\nCONSIDERANDO:\n"
        ."Art.1°.-APROBAR la Cancelación Definitiva de la vivienda identificada como MANZANA C - UNIDAD 03 que fuera otorgada a la Se-\nñora MARIA LAURA APUD DNI Nº 21.745.752, en un todo de acuerdo a lo informado.-\n"
        ."Art.2°.-Resolución Nº 185/2017 citada.";

    $r = (new AnalizadorResolucion)->analizar($texto);

    expect($r['tipo'])->toBe('Cancelaciones')
        ->and($r['datos']['num_exp'])->toBe('10241/440-2025')
        ->and($r['datos']['manzana'])->toBe('C')
        ->and($r['datos']['nombre_barrio'])->toBe('200 VIVIENDAS EN YERBA BUENA')
        ->and($r['datos']['nombre_titular'])->toBe('MARIA LAURA APUD')
        ->and($r['datos']['dni_titular'])->toBe('21.745.752')
        ->and($r['datos'])->not->toHaveKey('num_res'); // el número del encabezado está en blanco: la 185/2017 es otra resolución
});
