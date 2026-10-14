<?php

use App\Livewire\Admin\PanelAdmin;
use App\Models\Audit;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function adminDelPanel(): User
{
    $admin = User::factory()->create();
    Permiso::create(['user_id' => $admin->id, 'nombre' => 'admin_ver']);

    return $admin;
}

beforeEach(fn () => Cache::forget('admin.resumen'));

test('sin admin_ver el panel da 403 y el menú no lo ofrece', function () {
    $usuario = User::factory()->create();
    Permiso::create(['user_id' => $usuario->id, 'nombre' => 'lista_usuario_ver']);

    $this->actingAs($usuario)->get(route('admin'))->assertForbidden();
    $this->actingAs($usuario)->get(route('listausuarios'))->assertOk()->assertDontSee(route('admin'));
});

test('sin sesión manda al login', function () {
    $this->get(route('admin'))->assertRedirect(route('login'));
});

test('con admin_ver se ve el panel y el menú lo ofrece', function () {
    $this->actingAs(adminDelPanel())->get(route('admin'))
        ->assertOk()->assertSee('Administración')->assertSee('Usuarios más activos');
});

test('una pestaña inventada vuelve al resumen', function () {
    Livewire::actingAs(adminDelPanel())->withQueryParams(['tab' => 'x'])->test(PanelAdmin::class)->assertSet('tab', 'resumen');
});

test('la auditoría lista, filtra por evento y muestra el detalle', function () {
    $admin = adminDelPanel();
    Audit::create(['auditable_type' => User::class, 'auditable_id' => 7, 'event' => 'permisos', 'old_values' => ['a' => 1], 'new_values' => ['b' => 2], 'user_id' => $admin->id, 'ip_address' => '10.0.0.9']);
    Audit::create(['auditable_type' => User::class, 'auditable_id' => 8, 'event' => 'email', 'old_values' => [], 'new_values' => [], 'user_id' => $admin->id, 'ip_address' => '10.0.0.8']);

    Livewire::actingAs($admin)->test(PanelAdmin::class)
        ->call('irA', 'auditoria')
        ->assertSee('10.0.0.9')->assertSee('10.0.0.8')
        ->set('evento', 'permisos')
        ->assertSee('10.0.0.9')->assertDontSee('10.0.0.8')
        ->call('alternar', Audit::where('event', 'permisos')->value('id'))
        ->assertSee('Antes');
});

test('un login y un fallo quedan registrados sin guardar la contraseña', function () {
    $usuario = User::factory()->create(['email' => 'ana@ejemplo.test']);

    Livewire::test('auth.login')->set('email', 'ana@ejemplo.test')->set('password', 'MalaClave1')->call('login');
    Livewire::test('auth.login')->set('email', 'ana@ejemplo.test')->set('password', 'password')->call('login');

    expect(Audit::where('event', 'login_fallido')->count())->toBe(1)
        ->and(Audit::where('event', 'login')->where('user_id', $usuario->id)->count())->toBe(1);
    expect(json_encode(Audit::all()->toArray()))->not->toContain('MalaClave1');
});

test('un intento con algo que no es un correo no deja lo tipeado en la auditoría (puede ser una clave)', function () {
    Livewire::test('auth.login')->set('email', 'MiClaveSecreta99')->set('password', 'otra')->call('login');

    expect(json_encode(Audit::where('event', 'login_fallido')->get()->toArray()))
        ->not->toContain('MiClaveSecreta99');
});

test('los accesos aparecen solo en su pestaña', function () {
    $admin = adminDelPanel();
    Audit::create(['auditable_type' => User::class, 'auditable_id' => 0, 'event' => 'login_fallido', 'new_values' => ['email' => 'intruso@x.test'], 'ip_address' => '9.9.9.9']);

    Livewire::actingAs($admin)->test(PanelAdmin::class)->call('irA', 'accesos')->assertSee('intruso@x.test');
    Livewire::actingAs($admin)->test(PanelAdmin::class)->call('irA', 'auditoria')->assertDontSee('intruso@x.test');
});

test('la pestaña Sistema muestra el estado', function () {
    Livewire::actingAs(adminDelPanel())->test(PanelAdmin::class)->call('irA', 'sistema')
        ->assertSee('Correo (mailer)')->assertSee('Base de usuarios');
});
