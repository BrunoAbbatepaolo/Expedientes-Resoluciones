<?php

use App\Livewire\ListaUsuario;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use App\Support\CatalogoPermisos;
use Livewire\Livewire;

function administradorDePermisos(): User
{
    $oficina = Oficina::factory()->create();
    $admin = User::factory()->create();
    Permiso::setOficinaAsignada($admin->id, $oficina->id);
    foreach (['lista_usuario_ver', 'lista_usuario_editar'] as $permiso) {
        Permiso::create(['user_id' => $admin->id, 'nombre' => $permiso]);
    }

    return $admin;
}

test('el catálogo y la pantalla de usuarios manejan los mismos permisos', function () {
    $claves = array_keys((new ListaUsuario)->permisos);

    expect($claves)->toEqualCanonicalizing(CatalogoPermisos::claves())
        ->and(CatalogoPermisos::claves())->toHaveCount(count(array_unique(CatalogoPermisos::claves())));
});

test('normalizar agrega lo que cada permiso necesita y descarta claves ajenas', function () {
    expect(CatalogoPermisos::normalizar(['expediente_editar']))->toBe(['expediente_ver', 'expediente_editar'])
        ->and(CatalogoPermisos::normalizar(['rrhh_confirmar_pedidos', 'rrhh_horarios']))->toBe(['rrhh_ver', 'rrhh_horarios', 'rrhh_confirmar_pedidos'])
        ->and(CatalogoPermisos::normalizar(['superadmin', 'oficina_asignada']))->toBe([])
        ->and(CatalogoPermisos::normalizar(['expediente_ver', 'expediente_ver']))->toBe(['expediente_ver']);
});

test('las plantillas de rol usan solo permisos del catálogo y ya traen sus dependencias', function () {
    foreach (CatalogoPermisos::roles() as $clave => $rol) {
        expect($rol['permisos'])->not->toBeEmpty("{$clave} sin permisos")
            ->and(CatalogoPermisos::normalizar($rol['permisos']))->toEqualCanonicalizing($rol['permisos'], "{$clave} no cierra sus dependencias");
    }
});

test('guardar permisos aplica las dependencias aunque el cliente no las mande', function () {
    $admin = administradorDePermisos();
    $otro = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.expediente_editar', true)
        ->set('permisos.rrhh_confirmar_pedidos', true)
        ->call('guardarPermisos')
        ->assertDispatched('toast', type: 'success');

    expect(Permiso::where('user_id', $otro->id)->pluck('nombre')->all())
        ->toContain('expediente_ver', 'expediente_editar', 'rrhh_ver', 'rrhh_confirmar_pedidos');
});

test('guardar permisos quita los que se desactivaron y no toca los ajenos al catálogo', function () {
    $admin = administradorDePermisos();
    $otro = User::factory()->create();
    Permiso::setOficinaAsignada($otro->id, Oficina::factory()->create()->id);
    Permiso::create(['user_id' => $otro->id, 'nombre' => 'resolucion_ver']);

    Livewire::actingAs($admin)
        ->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.resolucion_ver', false)
        ->set('permisos.expediente_ver', true)
        ->call('guardarPermisos');

    $nombres = Permiso::where('user_id', $otro->id)->pluck('nombre')->all();
    expect($nombres)->toContain('expediente_ver', 'oficina_asignada')->not->toContain('resolucion_ver');
});

test('al elegir un usuario se guarda lo que ya tenía para mostrar el resumen de cambios', function () {
    $admin = administradorDePermisos();
    $otro = User::factory()->create();
    Permiso::create(['user_id' => $otro->id, 'nombre' => 'expediente_ver']);

    $componente = Livewire::actingAs($admin)->test(ListaUsuario::class)->call('seleccionarUsuario', $otro->id);

    expect($componente->get('permisosOriginales')['expediente_ver'])->toBeTrue()
        ->and($componente->get('permisosOriginales')['resolucion_ver'])->toBeFalse();

    $componente->set('permisos.resolucion_ver', true);
    expect($componente->get('permisosOriginales')['resolucion_ver'])->toBeFalse();
});

test('la ventana de permisos muestra los módulos, las plantillas y el resumen', function () {
    $admin = administradorDePermisos();

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->assertSee('Expedientes')
        ->assertSee('Resoluciones')
        ->assertSee('Usuarios y oficinas')
        ->assertSee('Recursos Humanos')
        ->assertSee('Ver expedientes')
        ->assertSee('Requiere «Ver expedientes»', false)
        ->assertSee('Plantillas de rol')
        ->assertSee('Buscar permiso')
        ->assertSee('Solo activos')
        ->assertSee('Sin cambios por guardar.')
        ->assertSee('modalPermisos(', false);
});

test('lista_usuario_editar solo no alcanza para darse ni dar admin_ver', function () {
    $admin = administradorDePermisos();
    $otro = User::factory()->create();

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $admin->id)
        ->set('permisos.admin_ver', true)
        ->call('guardarPermisos');
    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.admin_ver', true)
        ->call('guardarPermisos');

    expect(Permiso::where('nombre', 'admin_ver')->count())->toBe(0);
});

test('quien tiene admin_ver puede otorgarlo, y quien no lo tiene no se lo puede quitar a otro', function () {
    $superadmin = administradorDePermisos();
    Permiso::create(['user_id' => $superadmin->id, 'nombre' => 'admin_ver']);
    $otro = User::factory()->create();

    Livewire::actingAs($superadmin)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.admin_ver', true)
        ->call('guardarPermisos');
    expect(Permiso::where('user_id', $otro->id)->where('nombre', 'admin_ver')->exists())->toBeTrue();

    // Un editor sin admin_ver guarda otros permisos del mismo usuario: admin_ver se conserva.
    $editor = administradorDePermisos();
    Livewire::actingAs($editor)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.admin_ver', false)
        ->set('permisos.resolucion_ver', true)
        ->call('guardarPermisos');

    expect(Permiso::where('user_id', $otro->id)->where('nombre', 'admin_ver')->exists())->toBeTrue()
        ->and(Permiso::where('user_id', $otro->id)->where('nombre', 'resolucion_ver')->exists())->toBeTrue();
});
