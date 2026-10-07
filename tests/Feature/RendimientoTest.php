<?php

use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\Support\MitivFalso;

/**
 * Presupuesto de consultas por página: evita regresiones de N+1 (por ejemplo,
 * User::permiso() consultando la base en cada llamada del menú y las vistas).
 */
function contarConsultas(callable $fn): int
{
    // DB::listen es global (escucha todas las conexiones): se registra una sola vez.
    $total = 0;
    DB::listen(function () use (&$total) {
        $total++;
    });
    $fn();

    return $total;
}

beforeEach(function () {
    MitivFalso::crear();
    $oficina = Oficina::factory()->create();
    $this->user = User::factory()->create();
    Permiso::setOficinaAsignada($this->user->id, $oficina->id);
    foreach (['expediente_ver', 'expediente_editar', 'resolucion_ver', 'resolucion_editar', 'lista_usuario_ver', 'lista_usuario_editar'] as $p) {
        Permiso::create(['user_id' => $this->user->id, 'nombre' => $p]);
    }
    Expediente::factory()->count(10)->create(['oficina_id' => $oficina->id]);
});

test('páginas principales dentro del presupuesto de consultas', function (string $ruta, int $maximo) {
    $this->actingAs($this->user);
    $consultas = contarConsultas(fn () => $this->get($ruta)->assertOk());

    expect($consultas)->toBeLessThanOrEqual($maximo);
})->with([
    ['/dashboard', 20],
    ['/expedientes', 20],
    ['/resoluciones', 15],
    ['/oficinas', 15],
    ['/usuarios', 20],
]);

test('pantallas de RRHH dentro del presupuesto de consultas', function (string $ruta, int $maximo) {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    $oficina = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    foreach (range(1, 15) as $i) {
        $id = MitivFalso::empleadoRrhh(['oficina_id' => $oficina]);
        MitivFalso::horarioSemana($id);
        MitivFalso::ausencia($id, '2026-10-05', '2026-10-06');
    }
    $this->user->forceFill(['legajo' => (string) DB::connection('pgsql_mitiv')->table('empleados')->value('legajo')])->save();
    Permiso::create(['user_id' => $this->user->id, 'nombre' => 'rrhh_ver']);
    RrhhJefatura::create(['user_id' => $this->user->id, 'mitiv_oficina_id' => $oficina]);
    $this->actingAs($this->user);

    $consultas = contarConsultas(fn () => $this->get($ruta)->assertOk());

    expect($consultas)->toBeLessThanOrEqual($maximo);
})->with([
    ['/rrhh', 30],
    ['/rrhh/equipo', 30],
    ['/rrhh/personal', 30],
    ['/rrhh/asistencia', 30],
    ['/rrhh/licencias', 30],
    ['/rrhh/organigrama', 30],
]);
