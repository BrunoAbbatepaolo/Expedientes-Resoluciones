<?php

use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;

test('las pantallas principales renderizan con el layout propio (sin Flux)', function (string $ruta) {
    $oficina = Oficina::factory()->create();
    $user = User::factory()->create();
    Permiso::setOficinaAsignada($user->id, $oficina->id);

    foreach (['expediente_ver', 'expediente_editar', 'resolucion_ver', 'resolucion_editar', 'lista_usuario_ver', 'lista_usuario_editar'] as $permiso) {
        Permiso::create(['user_id' => $user->id, 'nombre' => $permiso]);
    }

    $this->actingAs($user)
        ->get($ruta)
        ->assertOk()
        ->assertDontSee('flux', false)
        ->assertSee("localStorage.getItem('theme')", false);
})->with([
    '/dashboard',
    '/expedientes',
    '/resoluciones',
    '/resoluciones/elegir',
    '/resoluciones/crear/Cancelaciones',
    '/oficinas',
    '/usuarios',
    '/settings/profile',
    '/settings/appearance',
]);

test('la página 404 funciona también sin sesión iniciada', function () {
    $this->get('/no-existe')
        ->assertNotFound()
        ->assertSee('No encontrado');
});

test('un tipo de resolución con caracteres inválidos da 404', function () {
    $user = User::factory()->create();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'resolucion_editar']);

    $this->actingAs($user)
        ->get('/resoluciones/crear/..%2Fwelcome')
        ->assertNotFound();
});
