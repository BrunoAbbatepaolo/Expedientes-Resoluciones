<?php

use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;

function usuarioDelMenu(array $permisos = ['expediente_ver', 'resolucion_ver']): User
{
    $oficina = Oficina::factory()->create();
    $user = User::factory()->create();
    Permiso::setOficinaAsignada($user->id, $oficina->id);
    foreach ($permisos as $permiso) {
        Permiso::create(['user_id' => $user->id, 'nombre' => $permiso]);
    }

    return $user;
}

test('el menú lateral tiene barra de íconos para escritorio y cajón para celular', function () {
    $this->actingAs(usuarioDelMenu())->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="menu-lateral"', false)
        ->assertSee('sirex-aside', false)
        ->assertSee('data-rail', false)
        ->assertSee("localStorage.getItem('sirex-sidebar')", false)
        ->assertSee('aria-controls="menu-lateral"', false)
        ->assertSee('Contraer menú')
        ->assertSee('aria-label="Abrir menú"', false)
        ->assertSee('aria-label="Cerrar menú"', false);
});

test('el menú marca la pantalla actual y muestra solo lo que el usuario puede ver', function () {
    $this->actingAs(usuarioDelMenu(['expediente_ver']))->get(route('dashboard'))
        ->assertSee('aria-current="page"', false)
        ->assertSee('Expedientes')
        ->assertDontSee('title="Resoluciones"', false);

    $this->actingAs(usuarioDelMenu(['resolucion_ver']))->get(route('dashboard'))
        ->assertSee('title="Resoluciones"', false)
        ->assertDontSee('title="Expedientes"', false);
});

test('cada ítem del menú lleva título para la barra de íconos', function () {
    $this->actingAs(usuarioDelMenu())->get(route('dashboard'))
        ->assertSee('title="'.__('Dashboard').'"', false)
        ->assertSee('title="Oficinas"', false)
        ->assertSee('title="Usuarios"', false)
        ->assertSee('title="Modo oscuro"', false);
});

test('el CSS del menú define la barra de íconos solo desde 1024px', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toMatch('/@media \(min-width: 1024px\)\s*\{[^@]*\.sirex-aside\[data-rail=\'on\'\]/s')
        ->toContain('.sirex-nav-item.is-active')
        ->toContain('.sirex-subnav');
});
