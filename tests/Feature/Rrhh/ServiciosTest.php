<?php

use App\Enums\EstadoDia;
use App\Enums\NivelDatoRrhh;
use App\Models\Permiso;
use App\Models\RrhhAcceso;
use App\Models\RrhhJefatura;
use App\Models\User;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\LectorReloj;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Contracts\Licencias;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $this->computos = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $this->legal = MitivFalso::oficinaRrhh('AREA LEGAL');
    $this->ana = MitivFalso::empleadoRrhh(['apellido' => 'GOMEZ', 'nombre' => 'ANA', 'oficina_id' => $this->computos]);
    $this->luis = MitivFalso::empleadoRrhh(['apellido' => 'DIAZ', 'nombre' => 'LUIS', 'oficina_id' => $this->legal]);

    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
    $this->jefe = User::factory()->create();
    RrhhJefatura::create(['user_id' => $this->jefe->id, 'mitiv_oficina_id' => $this->computos]);
    $this->nadie = User::factory()->create();
});

function alcance(User $u)
{
    return app(AlcanceFactory::class)->para($u);
}

test('el listado aplica el alcance: RRHH ve todo y el jefe solo su oficina', function () {
    $legajos = app(Legajos::class);

    expect($legajos->buscar(alcance($this->rrhh), [])->pluck('id')->all())->toEqualCanonicalizing([$this->ana, $this->luis])
        ->and($legajos->buscar(alcance($this->jefe), [])->pluck('id')->all())->toBe([$this->ana])
        ->and($legajos->buscar(alcance($this->rrhh), ['texto' => 'diaz'])->pluck('id')->all())->toBe([$this->luis])
        ->and(fn () => $legajos->buscar(alcance($this->nadie), []))->toThrow(AuthorizationException::class);
});

test('la ficha trae solo las columnas del nivel y registra el acceso ajeno', function () {
    DB::connection('pgsql_mitiv')->table('grupo_familiares')->insert(['empleado_id' => $this->ana, 'apellido_nombre' => 'GOMEZ, HIJO', 'posee_orden_judicial' => '1']);
    $legajos = app(Legajos::class);

    $deJefe = $legajos->ficha(alcance($this->jefe), $this->ana);
    $deRrhh = $legajos->ficha(alcance($this->rrhh), $this->ana);

    expect($deJefe['nivel'])->toBe(NivelDatoRrhh::Basico)
        ->and($deJefe['empleado']->getAttributes())->not->toHaveKey('numero_documento')
        ->and($deJefe['familia'])->toBeEmpty()
        ->and($deRrhh['nivel'])->toBe(NivelDatoRrhh::Personal)
        ->and($deRrhh['empleado']->getAttributes())->toHaveKey('numero_documento')->not->toHaveKeys(['enfermedad', 'clave_fiscal'])
        ->and($deRrhh['familia']->first()->getAttributes())->not->toHaveKey('posee_orden_judicial')
        ->and(RrhhAcceso::where('user_id', $this->rrhh->id)->where('empleado_id', $this->ana)->exists())->toBeTrue()
        ->and(RrhhAcceso::where('user_id', $this->jefe->id)->exists())->toBeFalse();
});

test('la ficha de alguien fuera del alcance da 403', function () {
    expect(fn () => app(Legajos::class)->ficha(alcance($this->jefe), $this->luis))->toThrow(AuthorizationException::class);
});

test('el jefe ve el tipo de licencia pero no la observación', function () {
    MitivFalso::ausencia($this->ana, '2026-10-14', '2026-10-15', 'LIC.P/ENFERMEDAD', 'diagnóstico reservado');
    $licencias = app(Licencias::class);

    expect($licencias->deEmpleado(alcance($this->jefe), $this->ana)->first())
        ->tipo->descripcion->toBe('LIC.P/ENFERMEDAD')
        ->observacion->toBeNull()
        ->and($licencias->deEmpleado(alcance($this->rrhh), $this->ana)->first()->observacion)->toBe('diagnóstico reservado');
});

test('el mes de asistencia combina fichadas, faltas registradas, feriados y licencias', function () {
    MitivFalso::horarioSemana($this->ana, '08:00', '14:00');
    MitivFalso::fichada(5001, '2026-10-05 08:10'); // la tarjeta de Ana se asigna abajo
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $this->ana)->update(['card_id' => 5001]);
    MitivFalso::inasistencia($this->ana, '2026-10-06', 82);
    MitivFalso::feriado('2026-10-12');
    MitivFalso::ausencia($this->ana, '2026-10-14', '2026-10-15');

    $dias = collect(app(Asistencias::class)->mes(alcance($this->jefe), $this->ana, 2026, 10))->keyBy(fn ($d) => $d->fecha->day);

    expect($dias)->toHaveCount(31)
        ->and($dias[4]->estado)->toBe(EstadoDia::Franco)
        ->and($dias[5]->estado)->toBe(EstadoDia::Presente)
        ->and($dias[5]->marcas)->toBe(['08:10'])
        ->and($dias[6]->estado)->toBe(EstadoDia::Falta)
        ->and($dias[12]->estado)->toBe(EstadoDia::Feriado)
        ->and($dias[13]->estado)->toBe(EstadoDia::Falta)
        ->and($dias[14]->estado)->toBe(EstadoDia::Licencia)
        ->and($dias[25]->estado)->toBe(EstadoDia::Futuro);
});

test('empleado sin tarjeta ni horario no rompe el tablero del día', function () {
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $this->ana)->update(['card_id' => null]);

    $filas = app(Asistencias::class)->dia(alcance($this->jefe), CarbonImmutable::today());

    expect($filas)->toHaveCount(1)
        ->and($filas[0]['dia']->estado)->toBe(EstadoDia::Franco);
});

test('las fichadas crudas son solo para RRHH y el rango se recorta a 31 días', function () {
    MitivFalso::fichada(5001, '2026-09-01 08:00');
    MitivFalso::fichada(5001, '2026-10-15 08:00');
    $asistencias = app(Asistencias::class);

    $pagina = $asistencias->fichadas(alcance($this->rrhh), ['desde' => '2026-09-01', 'hasta' => '2026-10-20']);

    expect($pagina->total())->toBe(1)
        ->and(fn () => $asistencias->fichadas(alcance($this->jefe), []))->toThrow(AuthorizationException::class);
});

test('vacaciones adeudadas suma el saldo por año', function () {
    DB::connection('pgsql_mitiv')->table('vista_saldo_vacaciones')->insert([
        ['id' => $this->ana, 'anio' => 2024, 'saldo' => 5],
        ['id' => $this->ana, 'anio' => 2025, 'saldo' => 20],
        ['id' => $this->luis, 'anio' => 2025, 'saldo' => 0],
    ]);

    $filas = app(Licencias::class)->adeudadas(alcance($this->rrhh));

    expect($filas)->toHaveCount(1)
        ->and($filas[0]['total'])->toBe(25)
        ->and($filas[0]['anios'])->toBe([2024 => 5, 2025 => 20]);
});

test('el lector de relojes avisa si se cortó el latido', function () {
    $lector = app(LectorReloj::class);
    expect($lector->estado(CarbonImmutable::now())['alerta'])->toBeTrue();

    DB::connection('pgsql_mitiv')->table('operaciones_reloj')->insert(['operacion' => 'CHEQUEO_ESTADO_SERVICIO', 'created_at' => now()->subMinutes(5)]);
    MitivFalso::fichada(5001, now()->subMinutes(3)->format('Y-m-d H:i:s'));

    expect($lector->estado(CarbonImmutable::now())['alerta'])->toBeFalse();
});
