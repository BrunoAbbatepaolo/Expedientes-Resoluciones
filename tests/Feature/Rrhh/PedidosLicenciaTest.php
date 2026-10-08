<?php

use App\Enums\EstadoPedido;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\RrhhPedidoLicencia;
use App\Models\RrhhTipoPedible;
use App\Models\User;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Organigrama;
use App\Services\Rrhh\PedidosLicencia;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00')); // martes
    config(['sirex.rrhh_pedidos' => true]);
    MitivFalso::crear();
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    $this->otra = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    app(Organigrama::class)->asignarPadre($this->dpto, $this->area);

    $this->ana = MitivFalso::empleadoRrhh(['legajo' => 111, 'oficina_id' => $this->dpto, 'fecha_ingreso' => '2015-01-01']); // 30 días
    $this->empleada = User::factory()->create(['legajo' => '111']);
    MitivFalso::empleadoRrhh(['legajo' => 222, 'oficina_id' => $this->area]);
    $this->jefe = User::factory()->create(['legajo' => '222']);
    RrhhJefatura::create(['user_id' => $this->jefe->id, 'mitiv_oficina_id' => $this->area]);
    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_confirmar_pedidos']);

    MitivFalso::vacaciones($this->ana, '2026-02-02', '2026-02-06', 2025); // tomó 5: le quedan 25 del período 2025
    RrhhTipoPedible::create(['tipo_ausencia_id' => 96, 'nombre' => 'VAC ANUALES']);
    $this->enfermedad = MitivFalso::tipoAusencia('LIC.P/ENFERMEDAD');
    RrhhTipoPedible::create(['tipo_ausencia_id' => $this->enfermedad, 'nombre' => 'LIC.P/ENFERMEDAD', 'permite_retroactivo' => true]);
    $this->servicio = app(PedidosLicencia::class);
});

function alcanceDeUsuario(User $u)
{
    app()->forgetScopedInstances();

    return app(AlcanceFactory::class)->para($u);
}

function pedir($test, array $datos): RrhhPedidoLicencia
{
    return app(PedidosLicencia::class)->crear(alcanceDeUsuario($test->empleada), $datos + ['tipo_ausencia_id' => 96]);
}

test('circuito completo: pide, aprueba el jefe del área superior y confirma RRHH', function () {
    MitivFalso::feriado('2026-11-23');
    $pedido = pedir($this, ['desde' => '2026-11-23', 'hasta' => '2026-11-27']); // lun a vie con un feriado

    expect($pedido->dias_habiles)->toBe(4)
        ->and($pedido->estado)->toBe(EstadoPedido::PendienteJefe)
        ->and($pedido->es_vacaciones)->toBeTrue()
        ->and($this->servicio->porAprobar(alcanceDeUsuario($this->jefe))->pluck('id')->all())->toBe([$pedido->id])
        ->and(PedidosLicencia::contadores($this->jefe)['aprobar'])->toBe(1);

    $this->servicio->aprobar(alcanceDeUsuario($this->jefe), $pedido->id);
    expect($pedido->fresh()->estado)->toBe(EstadoPedido::AprobadoJefe)
        ->and($this->servicio->porConfirmar(alcanceDeUsuario($this->rrhh))->pluck('id')->all())->toBe([$pedido->id]);

    $this->servicio->confirmar(alcanceDeUsuario($this->rrhh), $pedido->id);
    expect($pedido->fresh())->estado->toBe(EstadoPedido::Confirmado)->rrhh_id->toBe($this->rrhh->id);
});

test('sin jefe asignado pasa directo a RRHH', function () {
    RrhhJefatura::query()->delete();

    expect(pedir($this, ['desde' => '2026-11-02', 'hasta' => '2026-11-03'])->estado)->toBe(EstadoPedido::AprobadoJefe);
});

test('nadie aprueba su propio pedido: el del jefe sin superior va a RRHH', function () {
    $alcanceJefe = alcanceDeUsuario($this->jefe);
    $pedido = $this->servicio->crear($alcanceJefe, ['tipo_ausencia_id' => $this->enfermedad, 'desde' => '2026-10-21', 'hasta' => '2026-10-21']);

    expect($pedido->estado)->toBe(EstadoPedido::AprobadoJefe)
        ->and($this->servicio->porAprobar(alcanceDeUsuario($this->jefe)))->toBeEmpty();
});

test('un jefe de otra oficina no puede aprobar', function () {
    $otroJefe = User::factory()->create();
    RrhhJefatura::create(['user_id' => $otroJefe->id, 'mitiv_oficina_id' => $this->otra]);
    $pedido = pedir($this, ['desde' => '2026-11-02', 'hasta' => '2026-11-03']);

    expect(fn () => $this->servicio->aprobar(alcanceDeUsuario($otroJefe), $pedido->id))->toThrow(AuthorizationException::class);
});

test('validaciones: fecha pasada, saldo de vacaciones y superposición', function (array $datos, string $campo) {
    MitivFalso::ausencia($this->ana, '2026-12-01', '2026-12-04', 'LIC. DEPORTIVA');

    try {
        pedir($this, $datos);
        $this->fail('Debía rechazar el pedido');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey($campo);
    }
})->with([
    'fecha pasada' => [['desde' => '2026-10-19', 'hasta' => '2026-10-19'], 'desde'],
    'supera el saldo' => [['desde' => '2026-11-02', 'hasta' => '2026-12-31'], 'hasta'],
    'se superpone con una licencia' => [['desde' => '2026-12-03', 'hasta' => '2026-12-07'], 'desde'],
    'solo fin de semana' => [['desde' => '2026-10-24', 'hasta' => '2026-10-25'], 'hasta'],
]);

test('los pedidos en trámite descuentan del saldo y un régimen como CONT no bloquea', function () {
    DB::connection('pgsql_mitiv')->table('tipo_ausencias')->insert(['id' => 131, 'descripcion' => 'CONT']);
    DB::connection('pgsql_mitiv')->table('ausencias')->insert(['empleado_id' => $this->ana, 'desde' => '2026-01-01', 'hasta' => '2026-12-31', 'tipo_ausencias_id' => 131]);

    pedir($this, ['desde' => '2026-11-02', 'hasta' => '2026-11-20']); // 15 hábiles de 25

    expect(fn () => pedir($this, ['desde' => '2026-11-23', 'hasta' => '2026-12-11']))->toThrow(ValidationException::class) // 15 más: no alcanza
        ->and(pedir($this, ['desde' => '2026-11-23', 'hasta' => '2026-12-04'])->dias_habiles)->toBe(10);
});

test('licencia por enfermedad se puede pedir con fecha pasada', function () {
    $pedido = pedir($this, ['tipo_ausencia_id' => $this->enfermedad, 'desde' => '2026-10-19', 'hasta' => '2026-10-20']);

    expect($pedido->dias_habiles)->toBe(2)->and($pedido->es_vacaciones)->toBeFalse();
});

test('el empleado cancela solo mientras espera al jefe; el rechazo exige motivo', function () {
    $pedido = pedir($this, ['desde' => '2026-11-02', 'hasta' => '2026-11-03']);

    expect(fn () => $this->servicio->cancelar(alcanceDeUsuario($this->jefe), $pedido->id))->toThrow(AuthorizationException::class)
        ->and(fn () => $this->servicio->rechazarJefe(alcanceDeUsuario($this->jefe), $pedido->id, '  '))->toThrow(ValidationException::class);

    $this->servicio->cancelar(alcanceDeUsuario($this->empleada), $pedido->id);
    expect($pedido->fresh()->estado)->toBe(EstadoPedido::Cancelado)
        ->and(fn () => $this->servicio->aprobar(alcanceDeUsuario($this->jefe), $pedido->id))->toThrow(ValidationException::class);
});

test('con el interruptor apagado no se puede pedir', function () {
    config(['sirex.rrhh_pedidos' => false]);

    expect(fn () => pedir($this, ['desde' => '2026-11-02', 'hasta' => '2026-11-03']))->toThrow(HttpException::class)
        ->and(PedidosLicencia::contadores($this->jefe))->toBe(['aprobar' => 0, 'confirmar' => 0]);
});
