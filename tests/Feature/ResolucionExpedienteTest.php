<?php

use App\Livewire\FichaIpv;
use App\Livewire\Resoluciones;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Resolucion;
use App\Models\User;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

// Una resolución va vinculada a un expediente de mitivdb por su número: se exige al cambiarlo y se ve desde la ficha.

beforeEach(function () {
    MitivFalso::crear();
    $this->expId = MitivFalso::expediente('1234/440-2026', 'GARCIA, ANA');
    MitivFalso::expediente('1235/440-2026', 'LOPEZ, PEDRO');

    $this->oficina = Oficina::factory()->create();
    $this->user = User::factory()->create();
    Permiso::setOficinaAsignada($this->user->id, $this->oficina->id);
    foreach (['resolucion_ver', 'resolucion_editar', 'expediente_ver'] as $p) {
        Permiso::create(['user_id' => $this->user->id, 'nombre' => $p]);
    }
});

function resolucionDe(string $numeroExp, ?int $oficinaId, array $extra = []): Resolucion
{
    return Resolucion::factory()->create(['numero_exp' => $numeroExp, 'oficina_id' => $oficinaId, 'fecha' => '2026-09-01', 'fecha_ingreso' => '2026-09-01'] + $extra);
}

test('al editar, cambiar el número a un expediente que no existe se rechaza', function () {
    $res = resolucionDe('1234/440-2026', $this->oficina->id);

    Livewire::actingAs($this->user)->test(Resoluciones::class)
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_exp', '9999/440-2026')
        ->call('guardarEdicion')
        ->assertHasErrors('resolucionForm.numero_exp');

    expect($res->fresh()->numero_exp)->toBe('1234/440-2026');
});

test('al editar, cambiar el número a un expediente real se guarda', function () {
    $res = resolucionDe('1234/440-2026', $this->oficina->id);

    Livewire::actingAs($this->user)->test(Resoluciones::class)
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_exp', '1235/440-2026')
        ->call('guardarEdicion')
        ->assertHasNoErrors();

    expect($res->fresh()->numero_exp)->toBe('1235/440-2026');
});

test('una resolución vieja con expediente inexistente se puede seguir editando si no se toca el número', function () {
    $res = resolucionDe('0001/440-1999', $this->oficina->id, ['numero_resolucion' => '10/1999']);

    Livewire::actingAs($this->user)->test(Resoluciones::class)
        ->call('cargarResolucion', $res->id)
        ->set('resolucionForm.numero_resolucion', '11/1999')
        ->call('guardarEdicion')
        ->assertHasNoErrors();

    expect($res->fresh()->numero_resolucion)->toBe('11/1999');
});

test('el listado enlaza el número de expediente a su ficha', function () {
    // En mitivdb real es una vista; en los tests se arma igual sobre las tablas falsas.
    \Illuminate\Support\Facades\DB::connection('pgsql_mitiv')->statement(
        'create view vista_expedientes as select e.id, e.numero, e.asunto, e.folio, e.fecha, c.nombre as causante from expedientes e join causantes c on c.id = e.causante_id'
    );
    resolucionDe('1234/440-2026', $this->oficina->id);

    Livewire::actingAs($this->user)->test(Resoluciones::class)
        ->assertSee(route('expedientes.ipv.ficha', $this->expId), false);
});

test('la ficha del expediente muestra sus resoluciones y el PDF solo a la oficina dueña', function () {
    resolucionDe('1234/440-2026', $this->oficina->id, ['numero_resolucion' => '938/2026', 'pdf' => 'resoluciones/a.pdf']);
    resolucionDe('1234/440-2026', Oficina::factory()->create()->id, ['numero_resolucion' => '939/2026', 'pdf' => 'resoluciones/b.pdf']);

    $c = Livewire::withoutLazyLoading()->actingAs($this->user)->test(FichaIpv::class, ['id' => $this->expId])
        ->assertSee('Resolución 938/2026')->assertSee('Resolución 939/2026');

    // Solo se ofrece el PDF de la resolución propia.
    expect(substr_count($c->html(), 'Ver PDF'))->toBe(1);
});

test('sin resolucion_ver la ficha no muestra resoluciones', function () {
    resolucionDe('1234/440-2026', $this->oficina->id, ['numero_resolucion' => '938/2026']);
    $sin = User::factory()->create();
    Permiso::create(['user_id' => $sin->id, 'nombre' => 'expediente_ver']);

    Livewire::withoutLazyLoading()->actingAs($sin)->test(FichaIpv::class, ['id' => $this->expId])->assertDontSee('938/2026');
});
