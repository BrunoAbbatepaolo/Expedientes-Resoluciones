<?php

use App\Livewire\Admin\Firmantes;
use App\Livewire\CrearResolucion;
use App\Livewire\Resoluciones;
use App\Models\Firmante;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Models\User;
use App\Services\Resoluciones\Articulos;
use App\Services\Resoluciones\Documento;
use App\Services\Resoluciones\Tipos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mews\Purifier\Facades\Purifier;
use Tests\Support\MitivFalso;

// Toda resolución se vincula a un expediente real de mitivdb: estos números existen.
beforeEach(function () {
    MitivFalso::crear();
    MitivFalso::expediente('1234/440-2026', 'GARCIA, ANA');
    MitivFalso::expediente('1235/440-2026', 'LOPEZ, PEDRO');
});

function editorDeResoluciones(): User
{
    $user = User::factory()->create();
    Permiso::setOficinaAsignada($user->id, Oficina::factory()->create()->id);
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_editar']);

    return $user;
}

test('modo personalizado: no guarda si quedan marcadores de la plantilla sin completar', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'personalizado')
        ->set('plantilla', '<p>Resolución Nº [NÚMERO_RESOLUCIÓN] del expediente [NÚMERO_EXPEDIENTE]. Se resuelve cancelar el plan.</p>')
        ->call('guardarPersonalizado')
        ->assertHasErrors('plantilla')
        ->assertSee('Todavía hay datos sin completar: [NÚMERO_RESOLUCIÓN], [NÚMERO_EXPEDIENTE]')
        ->assertDispatched('toast', type: 'error');

    expect(Resolucion::count())->toBe(0);
});

test('modo personalizado: no guarda una resolución vacía', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'personalizado')
        ->set('plantilla', '<p><br></p>')
        ->call('guardarPersonalizado')
        ->assertHasErrors('plantilla');

    expect(Resolucion::count())->toBe(0);
});

test('modo personalizado: guarda con datos reales y avisa con un cartel de éxito', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'personalizado')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('plantilla', '<p>Resolución Nº 123/2026. Se resuelve cancelar el plan del titular Juan Pérez.</p>')
        ->call('guardarPersonalizado')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success')
        ->assertSet('modo', '');

    expect(Resolucion::count())->toBe(1);
});

test('plantilla completa: con todo vacío no guarda y muestra qué falta', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'plantilla')
        ->call('guardarPlantilla')
        ->assertHasErrors(['datos.num_exp', 'datos.nombre_titular', 'datos.dni_titular'])
        ->assertHasNoErrors('datos.num_res')
        ->assertSee('Revisá lo siguiente:')
        ->assertSee('El número de expediente es obligatorio')
        ->assertDispatched('toast', type: 'error');

    expect(Resolucion::count())->toBe(0);
});

test('Rectificación y Transferencias no exigen un titular que su formulario no tiene', function () {
    $rectificacion = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('modo', 'plantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.num_res', '55/2026')->set('datos.num_dictamen', '1874/26')
        ->call('guardarPlantilla')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    expect(Resolucion::count())->toBe(1);

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Transferencias'])
        ->set('modo', 'plantilla')
        ->set('datos.num_exp', '1235/440-2026')
        ->set('datos.num_res', '56/2026')
        ->call('guardarPlantilla')
        ->assertHasErrors(['datos.nombre_titular_nuevo', 'datos.dni_titular_nuevo'])
        ->assertHasNoErrors(['datos.nombre_titular', 'datos.dni_titular']);
});

test('Rectificación: las tablas de adjudicatarios se completan y se pueden quitar', function () {
    $fila = ['ord' => '1', 'nombre' => 'PEREZ JUAN', 'documento' => '20.123.456', 'mz' => 'A', 'lote' => '5', 'fnac' => '', 'cupo' => ''];

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('modo', 'plantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.num_res', '55/2026')->set('datos.num_dictamen', '1874/26')
        ->set('datos.tabla_donde_dice', [$fila])
        ->set('datos.sin_tabla_debe_decir', true)
        ->call('guardarPlantilla')
        ->assertHasNoErrors();

    $resolucion = Resolucion::first();
    $html = Documento::html($resolucion->tipo, $resolucion->datos['campos']);

    expect($html)->toContain('PEREZ JUAN')
        ->and(substr_count($html, 'tabla-resolucion'))->toBe(1);
});

/** PDF mínimo con una línea de texto por elemento (texto ASCII). */
function pdfConTexto(array $lineas): string
{
    $contenido = "BT /F1 11 Tf 40 760 Td 14 TL\n";
    foreach ($lineas as $linea) {
        $contenido .= '('.addcslashes($linea, '\\()').") '\n";
    }
    $contenido .= 'ET';

    $objetos = [
        '<</Type/Catalog/Pages 2 0 R>>',
        '<</Type/Pages/Kids[3 0 R]/Count 1>>',
        '<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>',
        '<</Length '.strlen($contenido).">>\nstream\n{$contenido}\nendstream",
        '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>',
    ];

    $pdf = "%PDF-1.4\n";
    $posiciones = [];
    foreach ($objetos as $i => $objeto) {
        $posiciones[] = strlen($pdf);
        $pdf .= ($i + 1)." 0 obj\n{$objeto}\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objetos) + 1)."\n0000000000 65535 f \n";
    foreach ($posiciones as $posicion) {
        $pdf .= sprintf("%010d 00000 n \n", $posicion);
    }

    return $pdf.'trailer<</Root 1 0 R/Size '.(count($objetos) + 1).">>\nstartxref\n{$xref}\n%%EOF";
}

function pdfDeCancelacion(): UploadedFile
{
    return UploadedFile::fake()->createWithContent('resolucion.pdf', pdfConTexto([
        'S.M. de Tucuman, 20 de mayo de 2026',
        'Expte. N 1234/440-2026',
        'RESOLUCION N 55/2026',
        'VISTO, las presentes actuaciones mediante las cuales se solicita la Cancelacion Definitiva y Levantamiento de Hipoteca en 1 Grado,',
        'MANZANA 12 - LOTE 7 DEL BARRIO Los Aromos CONSIDERANDO:',
        'Art. 1.- APROBAR la Cancelacion Definitiva, que fuera otorgada a los Senores PEREZ JUAN DNI N 20.123.456 y GOMEZ ANA DNI N 21.654.321',
    ]));
}

test('subir un PDF del mismo tipo precarga el formulario, lo adjunta y avisa para revisar', function () {
    Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', pdfDeCancelacion())
        ->assertHasNoErrors()
        ->assertSet('modo', 'plantilla')
        ->assertSet('datos.num_exp', '1234/440-2026')
        ->assertSet('datos.num_res', '55/2026')
        ->assertSet('datos.fecha_res', '2026-05-20')
        ->assertSet('datos.manzana', '12')
        ->assertSet('datos.nombre_titular', 'PEREZ JUAN')
        ->assertSet('datos.dni_cotitular', '21.654.321')
        ->assertCount('archivosPDF', 1)
        ->assertDispatched('toast', type: 'info');

    expect(Resolucion::count())->toBe(0); // solo sugiere: no guarda nada
});

test('subir un PDF de otro tipo sugiere cambiar de formulario y los datos viajan por sesión', function () {
    Storage::fake('local');
    $user = editorDeResoluciones();

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('documentoAnalizar', pdfDeCancelacion())
        ->assertSet('sugerencia', ['tipo' => 'Cancelaciones', 'nombre' => 'Cancelaciones'])
        ->assertSet('datos.num_exp', '')
        ->assertSee('Este documento parece ser una resolución de tipo')
        ->call('aplicarSugerencia')
        ->assertRedirect(route('resoluciones.crear', ['tipo' => 'Cancelaciones']));

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->assertSet('modo', 'plantilla')
        ->assertSet('datos.num_exp', '1234/440-2026')
        ->assertSet('datos.nombre_barrio', 'Los Aromos');
});

test('un PDF que no es una resolución conocida avisa y no toca el formulario', function () {
    Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', UploadedFile::fake()->createWithContent('receta.pdf', pdfConTexto(['Receta de empanadas tucumanas con carne cortada a cuchillo y cebolla de verdeo'])))
        ->assertSet('modo', '')
        ->assertSet('sugerencia', null)
        ->assertSet('datos.num_exp', '')
        ->assertDispatched('toast', type: 'warning');
});

test('un archivo que no es PDF ni Word se rechaza con un mensaje claro', function () {
    Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', UploadedFile::fake()->create('programa.exe', 10))
        ->assertHasErrors('documentoAnalizar')
        ->assertSee('Ese archivo no se puede leer. Subí un PDF o un Word (.docx).')
        ->assertSet('documentoAnalizar', null);
});

test('en la plantilla completa se puede adjuntar un PDF a la resolución', function () {
    Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'plantilla')
        ->assertSee('Agregar PDF')
        ->set('tempArchivos', [UploadedFile::fake()->create('dni.pdf', 20, 'application/pdf')])
        ->assertHasNoErrors()
        ->assertCount('archivosPDF', 1)
        ->assertSee('dni.pdf');
});

test('no se guarda una resolución de un expediente que no existe (plantilla completa y modo personalizado)', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('modo', 'plantilla')
        ->set('datos.num_exp', '9999/440-2026')
        ->set('datos.num_res', '55/2026')
        ->call('guardarPlantilla')
        ->assertHasErrors('datos.num_exp')
        ->assertSee('no existe');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'personalizado')
        ->set('datos.num_exp', '9999/440-2026')
        ->set('plantilla', '<p>Resolución Nº 123/2026. Se resuelve cancelar el plan del titular Juan Pérez.</p>')
        ->call('guardarPersonalizado')
        ->assertHasErrors('datos.num_exp');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'personalizado')
        ->set('plantilla', '<p>Resolución Nº 123/2026. Se resuelve cancelar el plan del titular Juan Pérez.</p>')
        ->call('guardarPersonalizado')
        ->assertHasErrors('datos.num_exp');

    expect(Resolucion::count())->toBe(0);
});

test('un expediente puede tener más de una resolución', function () {
    foreach (['55/2026', '56/2026'] as $numero) {
        Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
            ->set('modo', 'plantilla')
            ->set('datos.num_exp', '1234/440-2026')
            ->set('datos.num_res', $numero)->set('datos.num_dictamen', '1874/26')
            ->call('guardarPlantilla')
            ->assertHasNoErrors();
    }

    expect(Resolucion::where('numero_exp', '1234/440-2026')->count())->toBe(2);
});

test('el campo de expediente confirma que existe y avisa si no', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'plantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->assertSee('Expediente encontrado: GARCIA, ANA')
        ->set('datos.num_exp', '1/1-1')
        ->assertSee('No existe ese expediente');
});

test('quitar un artículo en el documento renumera los siguientes y también lo guardado', function () {
    $componente = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'plantilla')
        ->assertSee('Art. 4º.-', false)
        ->call('quitarArticulo', 2)
        ->assertSet('articulosQuitados', [2])
        ->assertDontSee('DISPONER el Levantamiento')
        ->assertDontSee('Art. 4º.-', false)
        ->assertSee('Quitaste 1 artículo');

    $componente->set('datos.num_exp', '1234/440-2026')->set('datos.num_res', '938/2026')->set('datos.num_dictamen', '1874/26')
        ->set('datos.nombre_titular', 'ANA GARCIA')->set('datos.dni_titular', '1')
        ->call('guardarPlantilla')->assertHasNoErrors();

    $html = Resolucion::first()->plantilla;
    expect($html)->not->toContain('DISPONER el Levantamiento')
        ->and($html)->toContain('Art. 3º.- ')->not->toContain('Art. 4º.-')
        ->and($html)->not->toContain('sirex-art-quitar');
});

test('se pueden restaurar los artículos y no se puede quitar el último', function () {
    $c = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'plantilla')
        ->call('quitarArticulo', 1)->call('quitarArticulo', 2)->call('quitarArticulo', 3)
        ->call('quitarArticulo', 4)
        ->assertSet('articulosQuitados', [1, 2, 3])
        ->call('quitarArticulo', 99)->call('quitarArticulo', 0)
        ->assertSet('articulosQuitados', [1, 2, 3])
        ->call('restaurarArticulos')
        ->assertSet('articulosQuitados', []);
});

test('Articulos renumera de corrido y respeta los artículos citados (data-art-fijo)', function () {
    $html = '<div class="articulo"><p><span>Art. 1º.- </span>A</p></div><div class="articulo" data-art-fijo><p><span>Art. 1º.- </span>CITADO</p></div>'
        .'<div class="articulo"><p><span>Art. 2º.- </span>B</p></div><div class="articulo"><p><span>Art. 3º.- </span>C</p></div>';

    expect(Articulos::contar($html))->toBe(3);

    $r = Articulos::procesar($html, [2], false);

    expect($r)->toContain('Art. 1º.- </span>A')->toContain('Art. 1º.- </span>CITADO')->toContain('Art. 2º.- </span>C')
        ->not->toContain('>B<')->not->toContain('Art. 3º');
});

function aplicarPagosCompleto(User $user)
{
    return Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'AplicarPagos'])
        ->call('usarPlantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.codigo_pago', '778899')
        ->set('datos.monto', '15000.50')
        ->set('datos.cuotas_adeudadas', '3');
}

test('plantilla completa: cada dato del documento es un input ligado a datos (los repetidos comparten el mismo dato)', function () {
    $html = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'AplicarPagos'])
        ->call('usarPlantilla')
        ->assertSet('modo', 'plantilla')
        ->instance()->documentoEditable();
    $html = html_entity_decode($html, ENT_QUOTES); // el navegador decodifica &#039; en los atributos

    expect(substr_count($html, 'x-model="$wire.datos[\'codigo_pago\']"'))->toBe(3)
        ->and($html)->toContain('x-model="$wire.datos[\'num_exp\']"')
        ->and($html)->toContain('x-model="$wire.datos[\'monto\']"')
        ->and($html)->not->toContain('contenteditable="true"');
});

test('Aplicar pagos: guarda con los datos del documento, sin pedir número de resolución ni titular', function () {
    $c = aplicarPagosCompleto(editorDeResoluciones())
        ->call('guardarPlantilla')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    $resolucion = Resolucion::sole();
    expect($c->get('resolucionGuardadaId'))->toBe($resolucion->id)
        ->and($resolucion->tipo)->toBe('AplicarPagos')
        ->and($resolucion->datos['campos']['codigo_pago'])->toBe('778899')
        ->and(substr_count($resolucion->plantilla, '778899'))->toBe(3)
        ->and($resolucion->plantilla)->toContain('15000.50')->not->toContain('<input')->not->toContain('[MONTO]')
        ->and(session('resolucion_guardada.id'))->toBe($resolucion->id);

    // Otro clic en "Guardar" no la duplica.
    $c->call('guardarPlantilla');
    expect(Resolucion::count())->toBe(1);
});

test('Aplicar pagos: si faltan datos no guarda, avisa qué falta y conserva lo cargado', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'AplicarPagos'])
        ->call('usarPlantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.codigo_pago', '778899')
        ->call('guardarPlantilla')
        ->assertHasErrors(['datos.monto', 'datos.cuotas_adeudadas'])
        ->assertHasNoErrors(['datos.num_res', 'datos.nombre_titular', 'datos.codigo_pago'])
        ->assertSee('El monto es obligatorio')
        ->assertSet('modo', 'plantilla')
        ->assertSet('datos.codigo_pago', '778899')
        ->assertSet('datos.num_exp', '1234/440-2026');

    expect(Resolucion::count())->toBe(0);
});

test('Aplicar pagos: el PDF imprimible arma el documento con su diseño y los datos guardados', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    aplicarPagosCompleto($user)->call('guardarPlantilla')->assertHasNoErrors();

    $this->actingAs($user)->get(route('resoluciones.imprimir', Resolucion::sole()))
        ->assertOk()
        ->assertSee('class="sheet"', false)
        ->assertSee('778899')
        ->assertSee('DEPARTAMENTO RECURSOS FINANCIEROS')
        ->assertSee('window.print()', false)
        ->assertDontSee('<input', false);
});

test('el PDF imprimible respeta la oficina de la resolución', function () {
    aplicarPagosCompleto(editorDeResoluciones())->call('guardarPlantilla')->assertHasNoErrors();

    $otro = editorDeResoluciones();
    Permiso::create(['user_id' => $otro->id, 'nombre' => 'resolucion_ver']);

    $this->actingAs($otro)->get(route('resoluciones.imprimir', Resolucion::sole()))->assertForbidden();
});

test('subir la nota de Aplicar pagos en PDF completa el código, el monto y las cuotas en la plantilla', function () {
    Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'AplicarPagos'])
        ->set('documentoAnalizar', UploadedFile::fake()->createWithContent('nota.pdf', pdfConTexto([
            'Expte. N 1234/440-2026',
            'SR. JEFE DEPARTAMENTO COMPUTOS ING. FEDERICO CONRAD:',
            'REF.: APLICAR PAGO - CODIGO (445566)',
            'Remito las presentes actuaciones a fin que se proceda a aplicar al CODIGO (445566) EL pago especial de $ 15.000,50 a las 3 cuotas adeudadas',
        ])))
        ->assertHasNoErrors()
        ->assertSet('modo', 'plantilla')
        ->assertSet('datos.codigo_pago', '445566')
        ->assertSet('datos.monto', '15000.50')
        ->assertSet('datos.cuotas_adeudadas', '3');
});

test('cada tipo: los datos obligatorios están en su plantilla y completándolos se guarda e imprime', function (string $tipo) {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);

    $c = Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => $tipo])->call('usarPlantilla');
    $html = html_entity_decode($c->instance()->documentoEditable(), ENT_QUOTES);
    expect($html)->not->toContain('No se pudo mostrar')
        ->and($html)->not->toContain('contenteditable="true"');

    $c->set('datos.fecha_res', '')->call('guardarPlantilla');
    $faltantes = array_map(fn ($k) => substr($k, strlen('datos.')), array_keys($c->errors()->toArray()));
    expect($faltantes)->toContain('num_exp', 'fecha_res');

    foreach ($faltantes as $campo) {
        // Sin input para un dato obligatorio la resolución no se podría guardar nunca.
        expect($html)->toContain("x-model=\"\$wire.datos['{$campo}']\"");
        $c->set("datos.{$campo}", match ($campo) {
            'num_exp' => '1234/440-2026',
            'fecha_res', 'fecha_fallecimiento' => '2026-10-19',
            default => 'A1',
        });
    }
    $c->call('guardarPlantilla')->assertHasNoErrors();

    $resolucion = Resolucion::sole();
    expect($resolucion->tipo)->toBe($tipo)->and($resolucion->plantilla)->not->toContain('<input');

    $this->actingAs($user)->get(route('resoluciones.imprimir', $resolucion))
        ->assertOk()
        ->assertSee('1234/440-2026')
        ->assertDontSee('<input', false)
        ->assertDontSee('x-model', false);
})->with(Tipos::TODOS);

test('firmantes: se eligen de la lista, la resolución guarda su copia y el PDF no cambia si después se edita el firmante', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $interventor = Firmante::where('nombre', 'Arq. HUGO CABRAL')->sole();

    $c = Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])->call('usarPlantilla');
    $html = html_entity_decode($c->instance()->documentoEditable(), ENT_QUOTES);
    expect($html)->toContain('data-campo="firma_1_nombre"')->toContain('data-campo="firma_2_nombre"')
        ->toContain('<option value="0">Arq. HUGO CABRAL</option>');

    // Lo que hace la lista al elegir: copia nombre, cargo y dependencia.
    $c->set('datos.firma_1_nombre', 'MARIA GABRIELA LAZARTE')->set('datos.firma_1_cargo', 'CONTADORA FISCAL')->set('datos.firma_1_dependencia', 'TRIBUNAL DE CUENTAS')
        ->set('datos.firma_2_nombre', $interventor->nombre)->set('datos.firma_2_cargo', $interventor->cargo)->set('datos.firma_2_dependencia', $interventor->dependencia)
        ->set('datos.num_exp', '1234/440-2026')->set('datos.num_res', '1/2026')->set('datos.num_dictamen', '2108/26')
        ->set('datos.nombre_titular', 'ANA')->set('datos.dni_titular', '1')
        ->call('guardarPlantilla')->assertHasNoErrors();

    $interventor->update(['nombre' => 'OTRA PERSONA']);

    $this->actingAs($user)->get(route('resoluciones.imprimir', Resolucion::sole()))
        ->assertOk()->assertSee('Arq. HUGO CABRAL')->assertSee('INTERVENTOR')->assertDontSee('OTRA PERSONA')
        ->assertSeeText('Dictamen Legal N° 2108/26');
});

test('el número de dictamen ya no viene fijo (3136/23) y es obligatorio en las resoluciones', function () {
    $c = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])->call('usarPlantilla');

    expect($c->instance()->documentoEditable())->not->toContain('3136/23')
        ->and($c->instance()->obligatorios())->toContain('num_dictamen', 'num_exp');

    $c->call('guardarPlantilla')->assertHasErrors('datos.num_dictamen')
        ->assertDispatched('campos-con-error', fn ($evento, $params) => in_array('num_dictamen', $params['campos'], true));
});

test('Administración → Firmantes: agregar, editar, ordenar y desactivar', function () {
    $admin = User::factory()->create();
    Permiso::create(['user_id' => $admin->id, 'nombre' => 'admin_ver']);

    Livewire::actingAs($admin)->test(Firmantes::class)
        ->call('guardar')->assertHasErrors(['nombre', 'cargo'])
        ->set('nombre', 'Lic. NUEVA FIRMA')->set('cargo', 'DIRECTORA')->call('guardar')->assertHasNoErrors();

    $nueva = Firmante::where('nombre', 'Lic. NUEVA FIRMA')->sole();
    expect($nueva->activo)->toBeTrue()->and($nueva->orden)->toBe(Firmante::max('orden'));

    Livewire::actingAs($admin)->test(Firmantes::class)
        ->call('mover', $nueva->id, -1)
        ->call('editar', $nueva->id)->set('cargo', 'DIRECTORA GENERAL')->call('guardar')
        ->call('alternarActivo', $nueva->id);

    $nueva->refresh();
    expect($nueva->cargo)->toBe('DIRECTORA GENERAL')->and($nueva->activo)->toBeFalse()
        ->and(Firmante::paraElegir()->pluck('nombre'))->not->toContain('Lic. NUEVA FIRMA')
        ->and(Firmante::orderBy('orden')->pluck('nombre')->get(Firmante::count() - 2))->toBe('Lic. NUEVA FIRMA');
});

test('Administración → Firmantes requiere admin_ver', function () {
    Livewire::actingAs(editorDeResoluciones())->test(Firmantes::class)->assertForbidden();
});

test('lista: "Modificar datos" guarda sin fecha de ingreso y el PDF imprimible muestra el número nuevo', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    aplicarPagosCompleto($user)->set('datos.num_res', '10/2026')->call('guardarPlantilla')->assertHasNoErrors();
    $res = Resolucion::sole();

    Livewire::actingAs($user)->test(Resoluciones::class)
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_resolucion', '999/2026')
        ->call('guardarEdicion')->assertHasNoErrors();

    expect($res->fresh()->numero_resolucion)->toBe('999/2026')
        ->and($res->fresh()->datos['campos']['num_res'])->toBe('999/2026');
});

test('lista: busca por número y filtra por tipo; la fecha sale dd/mm/aaaa y la fila abre el PDF', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    aplicarPagosCompleto($user)->set('datos.fecha_res', '2026-03-07')->call('guardarPlantilla');
    $res = Resolucion::sole();

    Livewire::actingAs($user)->test(Resoluciones::class)
        ->assertSee('07/03/2026')->assertDontSee('2026-03-07')
        ->assertSee(route('resoluciones.imprimir', $res), false)
        ->set('buscar', '1234/440')->assertSee('Aplicar pagos')
        ->set('buscar', 'no-existe')->assertSee('No hay resoluciones que coincidan')
        ->call('limpiarFiltros')->set('tipo', 'Cancelaciones')->assertSee('No hay resoluciones que coincidan')
        ->set('tipo', 'AplicarPagos')->assertDontSee('No hay resoluciones que coincidan');
});

test('Personalizado: arranca con el documento de la plantilla (con diseño, acentos bien y marcadores en lo que falta)', function () {
    $c = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('datos.nombre_titular', 'NUÑEZ JOSÉ')
        ->call('usarPersonalizado');

    $doc = $c->get('plantilla');
    expect($doc)->toContain('class="documento-resolucion"')
        ->toContain('Cancelación Definitiva')->toContain('NUÑEZ JOSÉ')
        ->not->toContain('Ã')->not->toContain('<style')
        ->toContain('<span class="marcador-dato dato-num_dictamen">[NÚMERO DE DICTAMEN]</span>')
        ->and($c->instance()->estilosPersonalizado())->toContain('<style');
});

test('Personalizado: no guarda con marcadores pendientes; guardado conserva el diseño, sanea el HTML y se imprime', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $c = Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])->call('usarPersonalizado');

    $c->set('datos.num_exp', '1234/440-2026')->call('guardarPersonalizado')->assertHasErrors('plantilla');
    expect(Resolucion::count())->toBe(0);

    // Lo que manda el navegador: el documento editado a mano, sin marcadores y con algo malicioso.
    $editado = preg_replace('#<span class="marcador-dato[^"]*">\[[^\]]*\]</span>#u', 'XX', $c->get('plantilla'));
    $editado = str_replace('Es por lo considerado que', 'TEXTO CAMBIADO A MANO<script>alert(1)</script><img src="x" onerror="alert(2)">', $editado);

    $c->set('plantilla', $editado)->set('datos.num_res', '77/2026')->call('guardarPersonalizado')->assertHasNoErrors();

    $res = Resolucion::sole();
    expect($res->tipo)->toBe('Cancelaciones')->and($res->datos)->toBe(['personalizado' => true])
        ->and($res->numero_resolucion)->toBe('77/2026')
        ->and($res->plantilla)->toContain('TEXTO CAMBIADO A MANO')->toContain('class="documento-resolucion"')
        ->not->toContain('<script')->not->toContain('onerror');

    $this->actingAs($user)->get(route('resoluciones.imprimir', $res))
        ->assertOk()->assertSee('TEXTO CAMBIADO A MANO')->assertSee('<style', false)->assertDontSee('alert(1)', false);
});

test('resolución vieja (sin tipo, con texto guardado y PDF adjunto): la lista y la ficha abren el PDF adjunto', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $res = Resolucion::factory()->create([
        'numero_exp' => '1234/440-2026', 'numero_resolucion' => '12/2025', 'fecha' => '2025-05-02',
        'oficina_id' => $user->oficinaAsignadaId(), 'plantilla' => '<p>Texto viejo</p>', 'tipo' => null,
    ]);
    $archivo = ResolucionArchivo::create([
        'resolucion_id' => $res->id, 'nombre_original' => 'firmada.pdf', 'nombre_archivo' => 'x.pdf',
        'ruta' => 'resoluciones/x.pdf', 'tipo' => 'application/pdf', 'tamano' => 10,
    ]);

    expect($res->fresh('archivos')->urlPdf())->toBe(route('resoluciones.archivo', $archivo));

    Livewire::actingAs($user)->test(Resoluciones::class)
        ->assertSee(route('resoluciones.archivo', $archivo), false)
        ->assertSee('Sin tipo')->assertSee('02/05/2025');
});

test('Personalizado conserva los logos (rutas relativas) y no deja imágenes externas', function () {
    expect(Documento::rutasRelativas('<img src="http://otro-host:8080/images/Logo-ipv.png" alt="IPV">'))
        ->toBe('<img src="/images/Logo-ipv.png" alt="IPV">');

    $c = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])->call('usarPersonalizado');
    $limpio = Purifier::clean($c->get('plantilla'), 'resolucion');
    expect(substr_count($limpio, '<img'))->toBeGreaterThanOrEqual(3)
        ->and(Purifier::clean('<img src="https://rastreo.example/x.gif">', 'resolucion'))->not->toContain('<img');
});

function resolucionSinNumero(User $user): Resolucion
{
    aplicarPagosCompleto($user)->call('guardarPlantilla')->assertHasNoErrors();

    return Resolucion::sole();
}

test('el número de resolución es opcional: se guarda sin número y se asigna después desde la lista', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $res = resolucionSinNumero($user);

    $res->update(['documento_html' => '<p>viejo</p>']);
    expect($res->numero_resolucion)->toBeNull();

    Livewire::actingAs($user)->test(Resoluciones::class)
        ->assertSee('Sin número')->assertSee('Asignar número')
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_resolucion', '41/2026')
        ->call('guardarEdicion')->assertHasNoErrors();

    $res->refresh();
    expect($res->numero_resolucion)->toBe('41/2026')
        ->and($res->datos['campos']['num_res'])->toBe('41/2026')
        ->and($res->documento_html)->toContain('778899');
});

test('el PDF se guarda fijo al crear: cambiar la plantilla después no modifica la resolución', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $res = resolucionSinNumero($user);

    expect($res->documento_html)->toContain('778899');

    $res->update(['documento_html' => '<p>COPIA FIJA ORIGINAL</p>']);
    $this->actingAs($user)->get(route('resoluciones.imprimir', $res))->assertOk()->assertSee('COPIA FIJA ORIGINAL', false);
});

test('lista: pagina de a 20 y conserva la búsqueda al cambiar de página', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $oficina = $user->oficinaAsignadaId();
    foreach (range(1, 25) as $n) {
        Resolucion::create(['oficina_id' => $oficina, 'numero_exp' => "9{$n}/440-2026", 'numero_resolucion' => "{$n}/2026", 'fecha' => '2026-01-01']);
    }

    $c = Livewire::actingAs($user)->test(Resoluciones::class);
    expect($c->viewData('resolucionesConExpediente'))->toHaveCount(20)
        ->and($c->viewData('resolucionesConExpediente')->total())->toBe(25);

    $c->call('gotoPage', 2);
    expect($c->viewData('resolucionesConExpediente'))->toHaveCount(5);

    $c->set('buscar', '9');
    expect($c->viewData('resolucionesConExpediente')->currentPage())->toBe(1);
});

test('exportar: baja un CSV con lo que filtra la lista y solo de la oficina de la persona', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $oficina = $user->oficinaAsignadaId();
    Resolucion::create(['oficina_id' => $oficina, 'numero_exp' => '111/440-2026', 'numero_resolucion' => '5/2026', 'fecha' => '2026-03-07', 'tipo' => 'Cancelaciones']);
    Resolucion::create(['oficina_id' => $oficina, 'numero_exp' => '222/440-2026', 'numero_resolucion' => '=CMD()', 'fecha' => '2026-03-08']);
    Resolucion::create(['oficina_id' => Oficina::factory()->create()->id, 'numero_exp' => '333/440-2026', 'numero_resolucion' => '7/2026', 'fecha' => '2026-03-09']);

    $csv = $this->actingAs($user)->get(route('resoluciones.exportar'))->assertOk()->streamedContent();
    expect($csv)->toContain('111/440-2026', '07/03/2026', "'=CMD()")->not->toContain('333/440-2026');

    $filtrado = $this->actingAs($user)->get(route('resoluciones.exportar', ['tipo' => 'Cancelaciones']))->streamedContent();
    expect($filtrado)->toContain('111/440-2026')->not->toContain('222/440-2026');

    $this->actingAs(User::factory()->create())->get(route('resoluciones.exportar'))->assertForbidden();
});

test('historial: registra quién creó y qué cambió, sin guardar el texto del documento', function () {
    $user = editorDeResoluciones();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_ver']);
    $res = resolucionSinNumero($user);

    Livewire::actingAs($user)->test(Resoluciones::class)
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_resolucion', '12/2026')
        ->call('guardarEdicion')
        ->call('verHistorial', $res->id)
        ->assertSee('Creó la resolución')->assertSee('Modificó la resolución')
        ->assertSee('N° de resolución: (vacío) → 12/2026');

    foreach ($res->audits as $audit) {
        expect($audit->new_values)->not->toHaveKeys(['plantilla', 'documento_html', 'datos']);
    }
});

test('usar como base: copia los datos de otra resolución, sin número y con la fecha de hoy', function () {
    $user = editorDeResoluciones();
    aplicarPagosCompleto($user)->set('datos.num_res', '10/2026')->set('datos.fecha_res', '2026-03-07')->call('guardarPlantilla')->assertHasNoErrors();
    $res = Resolucion::sole();

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'AplicarPagos', 'base' => $res->id])
        ->assertSet('modo', 'plantilla')
        ->assertSet('datos.codigo_pago', '778899')
        ->assertSet('datos.num_res', '')
        ->assertSet('datos.fecha_res', date('Y-m-d'));

    // Un tipo distinto no copia nada.
    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelaciones', 'base' => $res->id])
        ->assertSet('datos.codigo_pago', '');
});

/** El texto de un documento guardado, sin etiquetas ni saltos de línea de más. */
function textoPlano(string $html): string
{
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES)));
}

test('Cancelación Ley 8042: acepta manzana y lote con letra y arma el documento con la ley y los titulares', function () {
    $user = editorDeResoluciones();

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelacion8042'])
        ->call('usarPlantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.manzana', 'B')->set('datos.lote', '6A')->set('datos.nombre_barrio', '16 de Junio')
        ->set('datos.nombre_titular', 'Maria Elena Brizuela')->set('datos.dni_titular', '16.784.569')
        ->set('datos.nombre_cotitular', 'Juan Carlos Guaitima')->set('datos.dni_cotitular', '8.395.472')
        ->set('datos.num_dictamen', '1874/26')
        ->call('guardarPlantilla')->assertHasNoErrors();

    $res = Resolucion::sole();
    $html = textoPlano($res->documento_html);

    expect($res->cod_barrio)->toBeNull()
        ->and($html)->toContain('MANZANA B - LOTE 6A DEL BARRIO 16 DE JUNIO')
        ->and($html)->toContain('Ley N° 8042/2008; Decreto Nº 1933/01')
        ->and($html)->toContain('los Señores MARIA ELENA BRIZUELA DNI N° 16.784.569 y JUAN CARLOS GUAITIMA');
});

test('Adjudicación y Oficio judicial: los importes y porcentajes salen en el documento', function () {
    $user = editorDeResoluciones();

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Adjudicacion'])
        ->call('usarPlantilla')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.nombre_titular', 'Gonzalez Ramon')->set('datos.dni_titular', '24.803.980')
        ->set('datos.precio_vivienda', '33000000')->set('datos.num_cuotas', '360')->set('datos.num_dictamen', '2036/25')
        ->call('guardarPlantilla')->assertHasNoErrors();

    $texto = textoPlano(Resolucion::sole()->documento_html);
    expect($texto)->toContain('Art. 2º.- APROBAR el Precio Definitivo')->and($texto)->toContain('33.000.000')->and($texto)->toContain('360');

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'OficioJudicial'])
        ->call('usarPlantilla')
        ->set('datos.num_exp', '1235/440-2026')
        ->set('datos.nombre_titular', 'Barrientos Eduardo')->set('datos.dni_titular', '25.923.831')
        ->set('datos.nombre_titular_nuevo', 'Gramajo Maria Teresa')->set('datos.dni_titular_nuevo', '23.055.985')
        ->set('datos.num_dictamen', '2108/26')
        ->call('guardarPlantilla')->assertHasNoErrors();

    $oficio = textoPlano(Resolucion::latest('id')->first()->documento_html);
    expect($oficio)->toContain('TOMAR RAZON')->and($oficio)->toContain('100%');
});

test('completar desde el expediente: sugiere el causante como titular y lo carga si la persona acepta', function () {
    $c = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->call('usarPlantilla')
        ->assertSet('datos.nombre_titular', '')
        ->set('datos.num_exp', '1234/440-2026')
        ->assertSee('Usar «GARCIA, ANA» como titular');

    $c->call('usarTitularSugerido')
        ->assertSet('datos.nombre_titular', 'GARCIA, ANA')
        ->assertDontSee('Usar «');

    // Aplicar pagos no lleva titular: no ofrece nada.
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'AplicarPagos'])
        ->call('usarPlantilla')->set('datos.num_exp', '1234/440-2026')->assertDontSee('Usar «');
});
