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

    $componente = Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->set('nuevoNombre', 'Ana')
        ->set('nuevoApellido', 'Pérez')
        ->set('nuevoLegajo', 'L-1')
        ->set('nuevoEmail', 'ana@example.com')
        ->set('nuevoOficinaId', $oficina->id)
        ->call('crearUsuario');

    $nuevo = User::where('email', 'ana@example.com')->firstOrFail();
    expect(Hash::check('12345678', $nuevo->password))->toBeFalse()
        ->and($componente->get('claveGenerada'))->toStartWith('ana@example.com: ');
});
