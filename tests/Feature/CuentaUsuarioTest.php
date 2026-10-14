<?php

use App\Livewire\ListaUsuario;
use App\Models\Audit;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function adminDeCuentas(array $permisos = ['lista_usuario_ver', 'lista_usuario_editar']): User
{
    $admin = User::factory()->create();
    Permiso::setOficinaAsignada($admin->id, Oficina::factory()->create()->id);
    foreach ($permisos as $permiso) {
        Permiso::create(['user_id' => $admin->id, 'nombre' => $permiso]);
    }

    return $admin;
}

test('se puede cambiar el email de un usuario y queda auditado', function () {
    $otro = User::factory()->create(['email' => 'viejo@ipv.test']);

    Livewire::actingAs(adminDeCuentas())->test(ListaUsuario::class)
        ->call('abrirCuenta', $otro->id)
        ->assertSet('cuentaEmail', 'viejo@ipv.test')
        ->set('cuentaEmail', 'nuevo@ipv.test')
        ->call('guardarEmail')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    expect($otro->fresh()->email)->toBe('nuevo@ipv.test');

    $auditoria = Audit::where('event', 'email')->latest('id')->first();
    expect($auditoria->auditable_id)->toBe($otro->id)
        ->and($auditoria->old_values['email'])->toBe('viejo@ipv.test')
        ->and($auditoria->new_values['email'])->toBe('nuevo@ipv.test');
});

test('el email no puede repetirse ni ser inválido, y se explica en llano', function () {
    $otro = User::factory()->create();
    $ocupado = User::factory()->create(['email' => 'ocupado@ipv.test']);

    Livewire::actingAs(adminDeCuentas())->test(ListaUsuario::class)
        ->call('abrirCuenta', $otro->id)
        ->set('cuentaEmail', 'ocupado@ipv.test')
        ->call('guardarEmail')
        ->assertHasErrors('cuentaEmail')
        ->assertSee('Ese email ya lo usa otro usuario.')
        ->set('cuentaEmail', 'esto no es un mail')
        ->call('guardarEmail')
        ->assertHasErrors('cuentaEmail');

    expect($otro->fresh()->email)->not->toBe('ocupado@ipv.test');
});

test('guardar el mismo email no cambia nada ni falla por "ya existe"', function () {
    $otro = User::factory()->create(['email' => 'igual@ipv.test']);

    Livewire::actingAs(adminDeCuentas())->test(ListaUsuario::class)
        ->call('abrirCuenta', $otro->id)
        ->call('guardarEmail')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'info');

    expect(Audit::where('event', 'email')->count())->toBe(0);
});

test('restablecer la contraseña da una provisoria nueva, obliga a cambiarla y cierra sesiones', function () {
    $otro = User::factory()->create(['require_password_change' => false, 'remember_token' => 'abc']);
    $claveVieja = $otro->password;

    DB::table('sessions')->insert([
        'id' => 'sesion-del-otro', 'user_id' => $otro->id, 'ip_address' => '127.0.0.1',
        'user_agent' => 'test', 'payload' => '', 'last_activity' => time(),
    ]);
    config(['session.driver' => 'database']);

    $componente = Livewire::actingAs(adminDeCuentas())->test(ListaUsuario::class)
        ->call('abrirCuenta', $otro->id)
        ->call('restablecerClave')
        ->assertDispatched('toast', type: 'success')
        ->assertSee('Contraseña restablecida.');

    [$email, $clave] = explode(': ', $componente->get('claveGenerada'));
    $otro->refresh();

    expect($email)->toBe($otro->email)
        ->and(strlen($clave))->toBe(12)
        ->and(Hash::check($clave, $otro->password))->toBeTrue()
        ->and($otro->password)->not->toBe($claveVieja)
        ->and($otro->require_password_change)->toBeTrue()
        ->and($otro->remember_token)->toBeNull()
        ->and(DB::table('sessions')->where('user_id', $otro->id)->count())->toBe(0)
        ->and(Audit::where('event', 'clave_restablecida')->where('auditable_id', $otro->id)->exists())->toBeTrue();
});

test('no se puede restablecer la propia contraseña desde esta pantalla', function () {
    $admin = adminDeCuentas();
    $antes = $admin->password;

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('abrirCuenta', $admin->id)
        ->call('restablecerClave')
        ->assertDispatched('toast', type: 'error')
        ->assertSet('claveGenerada', null);

    expect($admin->fresh()->password)->toBe($antes);
});

test('sin permiso de edición no se puede tocar la cuenta de nadie', function () {
    $otro = User::factory()->create(['email' => 'intacto@ipv.test']);
    $lector = adminDeCuentas(['lista_usuario_ver']);

    Livewire::actingAs($lector)->test(ListaUsuario::class)
        ->call('abrirCuenta', $otro->id)
        ->assertDispatched('toast', type: 'error')
        ->assertSet('cuentaUserId', null)
        ->call('guardarEmail')
        ->call('restablecerClave');

    expect($otro->fresh()->email)->toBe('intacto@ipv.test')
        ->and(Audit::whereIn('event', ['email', 'clave_restablecida'])->count())->toBe(0);
});
