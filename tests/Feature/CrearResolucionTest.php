<?php

use App\Livewire\CrearResolucion;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Resolucion;
use App\Models\User;
use Livewire\Livewire;

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
        ->set('plantilla', '<p>Resolución Nº 123/2026. Se resuelve cancelar el plan del titular Juan Pérez.</p>')
        ->call('guardarPersonalizado')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success')
        ->assertSet('modo', '');

    expect(Resolucion::count())->toBe(1);
});

test('modelo completo: con todo vacío no guarda y muestra qué falta', function () {
    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'completo')
        ->call('guardar')
        ->assertHasErrors(['datos.num_exp', 'datos.num_res', 'datos.nombre_titular', 'datos.dni_titular'])
        ->assertSee('Revisá lo siguiente:')
        ->assertSee('El número de expediente es obligatorio')
        ->assertDispatched('toast', type: 'error');

    expect(Resolucion::count())->toBe(0);
});

test('Rectificación y Transferencias no exigen un titular que su formulario no tiene', function () {
    $rectificacion = Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('modo', 'completo')
        ->set('datos.num_exp', '1234/440-2026')
        ->set('datos.num_res', '55/2026')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    expect(Resolucion::count())->toBe(1);

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Transferencias'])
        ->set('modo', 'completo')
        ->set('datos.num_exp', '1235/440-2026')
        ->set('datos.num_res', '56/2026')
        ->call('guardar')
        ->assertHasErrors(['datos.nombre_titular_nuevo', 'datos.dni_titular_nuevo'])
        ->assertHasNoErrors(['datos.nombre_titular', 'datos.dni_titular']);
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

function pdfDeCancelacion(): \Illuminate\Http\UploadedFile
{
    return \Illuminate\Http\UploadedFile::fake()->createWithContent('resolucion.pdf', pdfConTexto([
        'S.M. de Tucuman, 20 de mayo de 2026',
        'Expte. N 1234/440-2026',
        'RESOLUCION N 55/2026',
        'VISTO, las presentes actuaciones mediante las cuales se solicita la Cancelacion Definitiva y Levantamiento de Hipoteca en 1 Grado,',
        'MANZANA 12 - LOTE 7 DEL BARRIO Los Aromos CONSIDERANDO:',
        'Art. 1.- APROBAR la Cancelacion Definitiva, que fuera otorgada a los Senores PEREZ JUAN DNI N 20.123.456 y GOMEZ ANA DNI N 21.654.321',
    ]));
}

test('subir un PDF del mismo tipo precarga el formulario, lo adjunta y avisa para revisar', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', pdfDeCancelacion())
        ->assertHasNoErrors()
        ->assertSet('modo', 'completo')
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
    \Illuminate\Support\Facades\Storage::fake('local');
    $user = editorDeResoluciones();

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Rectificacion'])
        ->set('documentoAnalizar', pdfDeCancelacion())
        ->assertSet('sugerencia', ['tipo' => 'Cancelaciones', 'nombre' => 'Cancelaciones'])
        ->assertSet('datos.num_exp', '')
        ->assertSee('Este documento parece ser una resolución de tipo')
        ->call('aplicarSugerencia')
        ->assertRedirect(route('resoluciones.crear', ['tipo' => 'Cancelaciones']));

    Livewire::actingAs($user)->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->assertSet('modo', 'completo')
        ->assertSet('datos.num_exp', '1234/440-2026')
        ->assertSet('datos.nombre_barrio', 'Los Aromos');
});

test('un PDF que no es una resolución conocida avisa y no toca el formulario', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', \Illuminate\Http\UploadedFile::fake()->createWithContent('receta.pdf', pdfConTexto(['Receta de empanadas tucumanas con carne cortada a cuchillo y cebolla de verdeo'])))
        ->assertSet('modo', '')
        ->assertSet('sugerencia', null)
        ->assertSet('datos.num_exp', '')
        ->assertDispatched('toast', type: 'warning');
});

test('un archivo que no es PDF ni Word se rechaza con un mensaje claro', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('documentoAnalizar', \Illuminate\Http\UploadedFile::fake()->create('programa.exe', 10))
        ->assertHasErrors('documentoAnalizar')
        ->assertSee('Ese archivo no se puede leer. Subí un PDF o un Word (.docx).')
        ->assertSet('documentoAnalizar', null);
});

test('en el modelo completo se puede adjuntar un PDF a la resolución', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::actingAs(editorDeResoluciones())->test(CrearResolucion::class, ['tipo' => 'Cancelaciones'])
        ->set('modo', 'completo')
        ->assertSee('Agregar PDF')
        ->set('tempArchivos', [\Illuminate\Http\UploadedFile::fake()->create('dni.pdf', 20, 'application/pdf')])
        ->assertHasNoErrors()
        ->assertCount('archivosPDF', 1)
        ->assertSee('dni.pdf');
});
