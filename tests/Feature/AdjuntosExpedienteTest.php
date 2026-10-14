<?php

use App\Livewire\FichaIpv;
use App\Models\Permiso;
use App\Models\User;
use App\Services\ExpedientesIpv;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

// Expedientes agregados (expediente_adjuntos): cabecera_id = expediente principal, expediente_id = el agregado.

beforeEach(function () {
    MitivFalso::crear();
    $this->a = MitivFalso::expediente('1/440-2026', 'GARCIA, ANA');
    $this->b = MitivFalso::expediente('2/440-2026', 'LOPEZ, PEDRO');
    $this->c = MitivFalso::expediente('3/440-2026', 'DIAZ, LUIS');
});

function agregar(int $agregado, int $cabecera): void
{
    DB::connection('pgsql_mitiv')->table('expediente_adjuntos')->insert(['expediente_id' => $agregado, 'cabecera_id' => $cabecera]);
}

test('un expediente muestra sus agregados con todos los niveles y las cabeceras a las que pertenece', function () {
    agregar($this->b, $this->a);   // B está agregado a A
    agregar($this->c, $this->b);   // C está agregado a B

    $deA = app(ExpedientesIpv::class)->adjuntos($this->a);
    expect($deA['agregados']->pluck('numero')->all())->toBe(['2/440-2026', '3/440-2026'])
        ->and($deA['agregados']->pluck('nivel')->all())->toBe([1, 2])
        ->and($deA['cabeceras'])->toBeEmpty();

    $deC = app(ExpedientesIpv::class)->adjuntos($this->c);
    expect($deC['cabeceras']->pluck('numero')->all())->toBe(['2/440-2026', '1/440-2026'])
        ->and($deC['agregados'])->toBeEmpty();
});

test('un ciclo en los datos no cuelga el recorrido ni se repite', function () {
    agregar($this->b, $this->a);
    agregar($this->a, $this->b);   // ciclo A <-> B (dato inconsistente)

    $r = app(ExpedientesIpv::class)->adjuntos($this->a);

    expect($r['agregados']->pluck('numero')->all())->toBe(['2/440-2026']);
});

test('las filas de un expediente consigo mismo se ignoran', function () {
    agregar($this->a, $this->a);

    $r = app(ExpedientesIpv::class)->adjuntos($this->a);

    expect($r['agregados'])->toBeEmpty()->and($r['cabeceras'])->toBeEmpty();
});

test('la ficha lista los agregados con enlace', function () {
    agregar($this->b, $this->a);
    $user = User::factory()->create();
    Permiso::create(['user_id' => $user->id, 'nombre' => 'expediente_ver']);

    Livewire::withoutLazyLoading()->actingAs($user)->test(FichaIpv::class, ['id' => $this->a])
        ->assertSee('Tiene agregados')->assertSee('2/440-2026')->assertSee(route('expedientes.ipv.ficha', $this->b), false);

    Livewire::withoutLazyLoading()->actingAs($user)->test(FichaIpv::class, ['id' => $this->c])
        ->assertDontSee('Expedientes agregados');
});
