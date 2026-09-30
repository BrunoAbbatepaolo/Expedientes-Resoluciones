<?php

use App\Livewire\DashboardPanel;
use App\Livewire\Expedientes;
use App\Livewire\ListaUsuario;
use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use Livewire\Livewire;

function usuarioConPermisos(Oficina $oficina, array $permisos): User
{
    $user = User::factory()->create();
    Permiso::setOficinaAsignada($user->id, $oficina->id);

    foreach ($permisos as $permiso) {
        Permiso::create(['user_id' => $user->id, 'nombre' => $permiso]);
    }

    return $user;
}

test('editar un expediente no permite cambiar su oficina', function () {
    $miOficina = Oficina::factory()->create();
    $otraOficina = Oficina::factory()->create();
    $expediente = Expediente::factory()->create(['oficina_id' => $miOficina->id]);
    $user = usuarioConPermisos($miOficina, ['expediente_ver', 'expediente_editar']);

    Livewire::actingAs($user)
        ->test(Expedientes::class)
        ->call('editar', $expediente->id)
        ->set('expedienteForm.asunto', 'Asunto nuevo')
        ->set('expedienteForm.oficina_id', $otraOficina->id)
        ->set('expedienteForm.cod_area', '99')
        ->call('actualizar');

    $expediente->refresh();
    expect($expediente->asunto)->toBe('Asunto nuevo')
        ->and($expediente->oficina_id)->toBe($miOficina->id)
        ->and($expediente->cod_area)->not->toBe('99');
});

test('eliminar un expediente cierra el modal y avisa con un toast', function () {
    $oficina = Oficina::factory()->create();
    $expediente = Expediente::factory()->create(['oficina_id' => $oficina->id]);
    $user = usuarioConPermisos($oficina, ['expediente_ver', 'expediente_editar']);

    Livewire::actingAs($user)
        ->test(Expedientes::class)
        ->call('confirmarBorrado', $expediente->id)
        ->call('eliminarExpediente')
        ->assertDispatched('modal-close', name: 'modal-ConfirmarBorrado')
        ->assertDispatched('toast', type: 'success');

    expect(Expediente::find($expediente->id))->toBeNull();
});

test('los ids de borrado y pase no se pueden setear desde el cliente', function () {
    $oficina = Oficina::factory()->create();
    $user = usuarioConPermisos($oficina, ['expediente_ver', 'expediente_editar']);

    Livewire::actingAs($user)
        ->test(Expedientes::class)
        ->set('expedienteId', 123);
})->throws(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

test('guardar un expediente requiere permiso de edición', function () {
    $oficina = Oficina::factory()->create();
    $user = usuarioConPermisos($oficina, ['expediente_ver']);

    Livewire::actingAs($user)
        ->test(Expedientes::class)
        ->call('guardar')
        ->assertForbidden();
});

test('el dashboard no permite cambiar la oficina consultada', function () {
    $oficina = Oficina::factory()->create();
    $user = usuarioConPermisos($oficina, ['expediente_ver']);

    Livewire::actingAs($user)
        ->test(DashboardPanel::class)
        ->set('oficinaId', $oficina->id + 1);
})->throws(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

test('guardar permisos ignora claves fuera de la lista blanca', function () {
    $oficina = Oficina::factory()->create();
    $admin = usuarioConPermisos($oficina, ['lista_usuario_ver', 'lista_usuario_editar']);
    $otro = usuarioConPermisos($oficina, []);

    Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.expediente_ver', true)
        ->set('permisos.superadmin', true)
        ->set('permisos.oficina_asignada', false)
        ->call('guardarPermisos');

    $nombres = Permiso::where('user_id', $otro->id)->pluck('nombre')->all();
    expect($nombres)->toContain('expediente_ver')
        ->toContain('oficina_asignada')
        ->not->toContain('superadmin');
});

test('crear usuario genera una clave provisoria aleatoria', function () {
    $oficina = Oficina::factory()->create();
    $admin = usuarioConPermisos($oficina, ['lista_usuario_ver', 'lista_usuario_editar']);

    \Tests\Support\MitivFalso::crear();
    $empleadoId = \Tests\Support\MitivFalso::empleado(['legajo' => 4321, 'apellido' => 'PEREZ', 'nombre' => 'ANA', 'email' => 'ana@example.com']);

    $componente = Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->set('buscarEmpleado', 'pere')
        ->assertSee('PEREZ, ANA')
        ->call('seleccionarEmpleado', $empleadoId)
        ->set('nuevoOficinaId', $oficina->id)
        ->call('crearUsuario');

    $nuevo = User::where('email', 'ana@example.com')->firstOrFail();
    expect(Hash::check('12345678', $nuevo->password))->toBeFalse()
        ->and($nuevo->legajo)->toBe('4321')
        ->and($componente->get('claveGenerada'))->toStartWith('ana@example.com: ');
});

test('no se puede crear un usuario sin elegir un empleado activo', function () {
    \Tests\Support\MitivFalso::crear();
    $oficina = Oficina::factory()->create();
    $admin = usuarioConPermisos($oficina, ['lista_usuario_ver', 'lista_usuario_editar']);
    $pasivo = \Tests\Support\MitivFalso::empleado(['espasivo' => true]);

    Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->call('seleccionarEmpleado', $pasivo)
        ->assertSet('empleadoId', null)
        ->set('nuevoNombre', 'Inventado')
        ->set('nuevoEmail', 'x@example.com')
        ->set('nuevoOficinaId', $oficina->id)
        ->call('crearUsuario')
        ->assertHasErrors('buscarEmpleado');

    expect(User::where('email', 'x@example.com')->exists())->toBeFalse();
});

test('marca a los usuarios que ya no son empleados activos', function () {
    \Tests\Support\MitivFalso::crear();
    \Tests\Support\MitivFalso::empleado(['legajo' => 111]);
    $oficina = Oficina::factory()->create();
    $admin = usuarioConPermisos($oficina, ['lista_usuario_ver', 'lista_usuario_editar']);
    $admin->forceFill(['legajo' => '111', 'apellido' => 'ACTIVO'])->save();
    User::factory()->create(['legajo' => '999', 'apellido' => 'DADODEBAJA']);

    Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->assertSeeInOrder(['DADODEBAJA', 'No activo en IPV']);
});
