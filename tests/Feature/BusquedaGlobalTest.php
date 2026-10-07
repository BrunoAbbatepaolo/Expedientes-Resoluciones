<?php

use App\Livewire\BusquedaGlobal;
use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->oficina = Oficina::factory()->create();
    $this->user = User::factory()->create();
    Permiso::setOficinaAsignada($this->user->id, $this->oficina->id);
    Permiso::create(['user_id' => $this->user->id, 'nombre' => 'expediente_ver']);
});

test('busca en el IPV y en los registros de mi oficina', function () {
    MitivFalso::expediente('4455/440-2026', 'SOSA, MARTA');
    Expediente::factory()->create(['num_exp' => '4455/440-2026', 'causante' => 'SOSA, MARTA', 'oficina_id' => $this->oficina->id]);

    Livewire::actingAs($this->user)
        ->test(BusquedaGlobal::class)
        ->set('q', '4455')
        ->assertSee('Expedientes del IPV')
        ->assertSee('Registrados en mi oficina')
        ->assertSee('4455/440-2026');
});

test('no busca con menos de 3 caracteres ni muestra lo que no puede ver', function () {
    MitivFalso::expediente('4455/440-2026', 'SOSA, MARTA');
    $sinPermiso = User::factory()->create();

    Livewire::actingAs($this->user)->test(BusquedaGlobal::class)->set('q', '44')->assertDontSee('4455/440-2026');
    Livewire::actingAs($sinPermiso)->test(BusquedaGlobal::class)->set('q', '4455')->assertDontSee('4455/440-2026');
});

test('RRHH encuentra empleados y quien no tiene permiso no los ve', function () {
    $oficina = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $empleado = MitivFalso::empleadoRrhh(['apellido' => 'ZAPATA', 'nombre' => 'RITA', 'oficina_id' => $oficina]);

    Livewire::actingAs($this->user)
        ->test(BusquedaGlobal::class)
        ->set('q', 'zapata')
        ->assertDontSee('ZAPATA');

    Permiso::create(['user_id' => $this->user->id, 'nombre' => 'rrhh_ver']);
    $this->app->forgetScopedInstances(); // en la app real, cada request arma su alcance

    Livewire::actingAs($this->user->fresh())
        ->test(BusquedaGlobal::class)
        ->set('q', 'zapata')
        ->assertSee('Personal')
        ->assertSee('ZAPATA, RITA')
        ->assertSee(route('rrhh.ficha', $empleado));
});
