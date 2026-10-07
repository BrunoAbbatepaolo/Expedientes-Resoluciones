<?php

use App\Enums\NivelDatoRrhh;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\User;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Organigrama;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    $this->hermana = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    app(Organigrama::class)->asignarPadre($this->dpto, $this->area);
    $this->empleadoDpto = MitivFalso::empleadoRrhh(['oficina_id' => $this->dpto]);
});

function alcanceDe(User $user)
{
    return app(AlcanceFactory::class)->para($user);
}

test('cada empleado ve lo suyo en nivel sensible', function () {
    MitivFalso::empleadoRrhh(['legajo' => 4242, 'oficina_id' => $this->hermana]);
    $user = User::factory()->create(['legajo' => '4242']);

    $alcance = alcanceDe($user);

    expect($alcance->empleadoId)->not->toBeNull()
        ->and($alcance->nivelPara($alcance->empleadoId, $this->hermana))->toBe(NivelDatoRrhh::Sensible)
        ->and($alcance->nivelPara($this->empleadoDpto, $this->dpto))->toBeNull()
        ->and($alcance->puedeEntrar())->toBeTrue();
});

test('el jefe de un área ve las oficinas que dependen y no las hermanas', function () {
    $jefe = User::factory()->create();
    RrhhJefatura::create(['user_id' => $jefe->id, 'mitiv_oficina_id' => $this->area]);

    $alcance = alcanceDe($jefe);

    expect($alcance->esJefe())->toBeTrue()
        ->and($alcance->oficinasVisibles())->toEqualCanonicalizing([$this->area, $this->dpto])
        ->and($alcance->nivelPara($this->empleadoDpto, $this->dpto))->toBe(NivelDatoRrhh::Basico)
        ->and($alcance->nivelPara(999, $this->hermana))->toBeNull()
        ->and($alcance->veObservacion($this->empleadoDpto))->toBeFalse();
});

test('RRHH ve todo en nivel personal y con sensibles en nivel sensible', function () {
    $rrhh = User::factory()->create();
    Permiso::create(['user_id' => $rrhh->id, 'nombre' => 'rrhh_ver']);
    $jefa = User::factory()->create();
    Permiso::create(['user_id' => $jefa->id, 'nombre' => 'rrhh_ver']);
    Permiso::create(['user_id' => $jefa->id, 'nombre' => 'rrhh_sensibles']);

    expect(alcanceDe($rrhh)->oficinasVisibles())->toBeNull()
        ->and(alcanceDe($rrhh)->nivelPara($this->empleadoDpto, $this->dpto))->toBe(NivelDatoRrhh::Personal)
        ->and(alcanceDe($rrhh)->veObservacion($this->empleadoDpto))->toBeTrue()
        ->and(alcanceDe($jefa)->nivelPara($this->empleadoDpto, $this->dpto))->toBe(NivelDatoRrhh::Sensible);
});

test('legajo vacío, no numérico o inexistente no rompe y no da acceso', function (?string $legajo) {
    $user = User::factory()->create(['legajo' => $legajo]);

    $alcance = alcanceDe($user);

    expect($alcance->empleadoId)->toBeNull()
        ->and($alcance->puedeEntrar())->toBeFalse();
})->with([null, '', 'abc', '99999']);
