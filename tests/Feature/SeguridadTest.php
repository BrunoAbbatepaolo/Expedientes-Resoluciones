<?php

use App\Livewire\ListaUsuario;
use App\Models\Audit;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Livewire\Volt\Volt;

function adminDeSeguridad(): User
{
    $admin = User::factory()->create();
    Permiso::setOficinaAsignada($admin->id, Oficina::factory()->create()->id);
    foreach (['lista_usuario_ver', 'lista_usuario_editar'] as $permiso) {
        Permiso::create(['user_id' => $admin->id, 'nombre' => $permiso]);
    }

    return $admin;
}

test('las respuestas llevan cabeceras de seguridad', function () {
    $respuesta = $this->get('/login');

    $respuesta->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

    expect($respuesta->headers->get('Content-Security-Policy'))
        ->toContain("frame-ancestors 'self'")->toContain("object-src 'none'")
        ->and($respuesta->headers->has('Strict-Transport-Security'))->toBeFalse();
});

test('con HTTPS se agrega Strict-Transport-Security', function () {
    $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
});

test('las cabeceras no pisan las que ya puso la respuesta', function () {
    Route::middleware('web')->get('/_prueba-cabeceras', fn () => response('ok', 200, ['X-Frame-Options' => 'DENY']));

    $this->get('/_prueba-cabeceras')->assertHeader('X-Frame-Options', 'DENY');
});

test('hay un tope general de pedidos: pasado el límite responde 429', function () {
    config(['sirex.limite_peticiones' => 6]); // sin sesión: la tercera parte, 2 por minuto

    $this->get('/login')->assertOk();
    $this->get('/login')->assertOk();
    $this->get('/login')->assertStatus(429);
});

test('los usuarios con sesión tienen su propio contador', function () {
    config(['sirex.limite_peticiones' => 3]);
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/profile')->assertOk();
    $this->actingAs($user)->get('/settings/profile')->assertOk();
    $this->actingAs($user)->get('/settings/profile')->assertOk();
    $this->actingAs($user)->get('/settings/profile')->assertStatus(429);

    // Otro usuario no queda bloqueado por el primero.
    $this->actingAs(User::factory()->create())->get('/settings/profile')->assertOk();
});

test('la recuperación de contraseña se frena después de 5 pedidos por hora', function () {
    RateLimiter::clear('recuperar-clave|lenta@example.com|127.0.0.1');

    $componente = Volt::test('auth.forgot-password')->set('email', 'lenta@example.com');
    foreach (range(1, 5) as $_) {
        $componente->call('sendPasswordResetLink')->assertHasNoErrors();
    }

    $componente->call('sendPasswordResetLink')->assertHasErrors('email');
});

test('la política de contraseñas exige 8 caracteres con mayúsculas, minúsculas y números', function () {
    $regla = Password::default();
    $valida = fn (string $clave) => validator(['p' => $clave], ['p' => [$regla]])->passes();

    expect($valida('corta1A'))->toBeFalse()
        ->and($valida('Corta1Ab'))->toBeTrue()
        ->and($valida('todominusculas123'))->toBeFalse()
        ->and($valida('SinNumerosAquiAbc'))->toBeFalse()
        ->and($valida('new-password'))->toBeFalse()
        ->and($valida('ClaveSegura2026'))->toBeTrue();
});

test('guardar permisos deja rastro de quién cambió qué', function () {
    $admin = adminDeSeguridad();
    $otro = User::factory()->create();
    Permiso::create(['user_id' => $otro->id, 'nombre' => 'resolucion_ver']);

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->set('permisos.resolucion_ver', false)
        ->set('permisos.expediente_editar', true)
        ->call('guardarPermisos');

    $auditoria = Audit::where('event', 'permisos')->latest('id')->first();

    expect($auditoria)->not->toBeNull()
        ->and($auditoria->auditable_id)->toBe($otro->id)
        ->and($auditoria->user_id)->toBe($admin->id)
        ->and($auditoria->new_values['agregados'])->toContain('expediente_ver', 'expediente_editar')
        ->and($auditoria->new_values['quitados'])->toBe(['resolucion_ver'])
        ->and($auditoria->old_values['permisos'])->toBe(['resolucion_ver']);
});

test('guardar permisos sin cambios no genera auditoría', function () {
    $admin = adminDeSeguridad();
    $otro = User::factory()->create();
    Permiso::create(['user_id' => $otro->id, 'nombre' => 'expediente_ver']);

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('seleccionarUsuario', $otro->id)
        ->call('guardarPermisos');

    expect(Audit::where('event', 'permisos')->count())->toBe(0);
});

test('cambiar la oficina y la contraseña también queda auditado, sin guardar claves', function () {
    $admin = adminDeSeguridad();
    $otro = User::factory()->create();
    $nueva = Oficina::factory()->create();

    Livewire::actingAs($admin)->test(ListaUsuario::class)
        ->call('editarOficina', $otro->id)
        ->set('oficina_id', $nueva->id)
        ->call('guardarOficina');

    expect(Audit::where('event', 'oficina')->first()?->new_values)->toBe(['oficina_id' => $nueva->id]);

    $user = User::factory()->create(['password' => bcrypt('Anterior2026x')]);
    Volt::actingAs($user)->test('settings.password')
        ->set('current_password', 'Anterior2026x')
        ->set('password', 'ClaveNueva2026')
        ->set('password_confirmation', 'ClaveNueva2026')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $registro = Audit::where('event', 'clave_cambiada')->first();
    expect($registro)->not->toBeNull()
        ->and(json_encode($registro->getAttributes()))->not->toContain('ClaveNueva2026');
});
