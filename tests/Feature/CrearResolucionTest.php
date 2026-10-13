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
        ->assertSee('No se pudo guardar. Revisá lo siguiente:')
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
