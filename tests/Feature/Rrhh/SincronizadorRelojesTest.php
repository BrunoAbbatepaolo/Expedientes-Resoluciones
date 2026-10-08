<?php

use App\Models\RrhhReloj;
use App\Services\Rrhh\Relojes\ClienteZk;
use App\Services\Rrhh\Relojes\ErrorReloj;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;
use App\Services\Rrhh\Relojes\SincronizadorRelojes;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\Support\FabricaRelojesSimulada;
use Tests\Support\MitivFalso;
use Tests\Support\RelojSimulado;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00')); // SIREX_RRHH_ZONA=UTC en los tests: hora local = 10:00
    MitivFalso::crear();
    config(['sirex.relojes_conexion_mitiv_escritura' => 'pgsql_mitiv']);

    $this->fabrica = new FabricaRelojesSimulada;
    $this->app->instance(FabricaClienteReloj::class, $this->fabrica);

    $this->r198 = new RelojSimulado([
        ['101', '2026-10-19 07:05:10', 15, 0],
        ['102', '2026-10-19 07:12:00', 15, 4],
        ['101', '2026-10-10 07:01:00', 15, 0], // vieja: fuera de la ventana de la doble escritura
    ], hora: '2026-10-20 10:03:00');
    $this->r199 = new RelojSimulado([['201', '2026-10-19 08:00:00', 15, 0]], hora: '2026-10-20 10:00:00');
    $this->fabrica->relojes = ['192.168.7.198' => $this->r198, '192.168.7.199' => $this->r199];

    $this->reloj198 = RrhhReloj::create(['nombre' => 'Mesa de entradas', 'ip' => '192.168.7.198']);
    $this->reloj199 = RrhhReloj::create(['nombre' => 'Planta baja', 'ip' => '192.168.7.199']);
    $this->sincronizador = fn () => app(SincronizadorRelojes::class);
});

function fichadasGuardadas(?int $relojId = null)
{
    return DB::connection('mysql_admin')->table('rrhh_fichadas')->when($relojId, fn ($q) => $q->where('reloj_id', $relojId));
}

function lecturas(RelojSimulado $reloj): int
{
    return count(array_filter($reloj->comandos, fn ($c) => $c === ClienteZk::CMD_READ_BUFFER));
}

test('la primera vuelta guarda todo y registra el estado de cada reloj', function () {
    $resultados = ($this->sincronizador)()->sincronizar();

    expect($resultados)->toHaveCount(2)
        ->and(collect($resultados)->pluck('estado')->all())->toBe(['sincronizado', 'sincronizado'])
        ->and(fichadasGuardadas($this->reloj198->id)->count())->toBe(3)
        ->and(fichadasGuardadas($this->reloj199->id)->count())->toBe(1);

    $r = $this->reloj198->fresh();
    expect($r->serie)->toBe('AEWD224660235')
        ->and($r->modelo)->toBe('MB360/ID ZMM220_TFT')
        ->and($r->firmware)->toBe('Ver 6.60 Sep 19 2019')
        ->and($r->registros_en_reloj)->toBe(3)
        ->and($r->ultima_fichada_at)->toBe('2026-10-19 07:12:00')
        ->and($r->desvio_segundos)->toBe(180)
        ->and($r->fallas_seguidas)->toBe(0)
        ->and($r->ultimo_contacto_at)->not->toBeNull();
});

test('si la cantidad de fichadas no cambió, no lee nada ni duplica', function () {
    ($this->sincronizador)()->sincronizar();
    $lecturasAntes = lecturas($this->r198);

    $resultados = ($this->sincronizador)()->sincronizar();

    expect(collect($resultados)->pluck('estado')->all())->toBe(['sin_cambios', 'sin_cambios'])
        ->and(lecturas($this->r198))->toBe($lecturasAntes)
        ->and(fichadasGuardadas()->count())->toBe(4);
});

test('una fichada nueva dispara la lectura y se agrega una sola vez', function () {
    ($this->sincronizador)()->sincronizar();
    $this->r198->fichadas[] = ['103', '2026-10-20 07:30:00', 15, 0];

    $r = ($this->sincronizador)()->sincronizar('192.168.7.198')[0];

    expect($r['estado'])->toBe('sincronizado')->and($r['leidas'])->toBe(4)->and($r['nuevas'])->toBe(1)
        ->and(fichadasGuardadas($this->reloj198->id)->count())->toBe(4)
        ->and($this->reloj198->fresh()->ultima_fichada_at)->toBe('2026-10-20 07:30:00');
});

test('las fichadas sin número de tarjeta se cuentan aparte y no se guardan', function () {
    $this->r198->fichadas[] = ['ABC123', '2026-10-19 09:00:00', 15, 0];

    $r = ($this->sincronizador)()->sincronizar('192.168.7.198')[0];

    expect($r['sin_tarjeta'])->toBe(1)->and(fichadasGuardadas($this->reloj198->id)->count())->toBe(3);
});

test('un reloj caído no frena a los demás y se recupera solo', function () {
    $this->fabrica->relojes['192.168.7.198'] = new ErrorReloj('El reloj no respondió a tiempo.');

    $resultados = collect(($this->sincronizador)()->sincronizar())->keyBy(fn ($r) => $r['reloj']->ip);

    expect($resultados['192.168.7.198']['estado'])->toBe('error')
        ->and($resultados['192.168.7.199']['estado'])->toBe('sincronizado')
        ->and($this->reloj198->fresh()->fallas_seguidas)->toBe(1)
        ->and($this->reloj198->fresh()->ultimo_error)->toBe('El reloj no respondió a tiempo.');

    ($this->sincronizador)()->sincronizar();
    expect($this->reloj198->fresh()->fallas_seguidas)->toBe(2);

    $this->fabrica->relojes['192.168.7.198'] = $this->r198;
    ($this->sincronizador)()->sincronizar();
    expect($this->reloj198->fresh())->fallas_seguidas->toBe(0)->ultimo_error->toBeNull();
});

test('los relojes desactivados no se consultan', function () {
    $this->reloj199->update(['activo' => false]);

    $resultados = ($this->sincronizador)()->sincronizar();

    expect($resultados)->toHaveCount(1)->and($this->r199->comandos)->toBe([]);
});

test('con la doble escritura apagada no se toca el sistema viejo', function () {
    ($this->sincronizador)()->sincronizar();

    expect(DB::connection('pgsql_mitiv')->table('registros_reloj')->count())->toBe(0)
        ->and(DB::connection('pgsql_mitiv')->table('operaciones_reloj')->count())->toBe(0);
});

test('con la doble escritura guarda en el sistema viejo solo lo reciente y lo que falta', function () {
    config(['sirex.relojes_escribir_mitiv' => true]);
    MitivFalso::fichada(101, '2026-10-19 07:05:10', '192.168.7.198'); // ya cargada por el lector viejo

    $resultados = collect(($this->sincronizador)()->sincronizar())->keyBy(fn ($r) => $r['reloj']->ip);

    expect($resultados['192.168.7.198']['mitiv'])->toBe(1) // solo la 102: la 101 ya estaba y la del 10/10 es de hace más de 3 días
        ->and($resultados['192.168.7.199']['mitiv'])->toBe(1);

    $filas = DB::connection('pgsql_mitiv')->table('registros_reloj')->where('origen', 'SIREX')->orderBy('card_id')->get();
    expect($filas->pluck('card_id')->map(fn ($c) => (int) $c)->all())->toBe([102, 201])
        ->and($filas[0]->nombre_reloj)->toBe('192.168.7.198')
        ->and($filas[0]->fecha_hora_inmutable)->toBe('2026-10-19 07:12:00')
        ->and((bool) $filas[0]->procesado)->toBeFalse()
        ->and($filas[0]->created_at)->toBe('2026-10-20 10:00:00'); // hora local, como el sistema viejo

    // Segunda vuelta: no duplica
    $this->r198->fichadas[] = ['103', '2026-10-20 07:30:00', 15, 0];
    ($this->sincronizador)()->sincronizar();
    expect(DB::connection('pgsql_mitiv')->table('registros_reloj')->count())->toBe(4);
});

test('la doble escritura recupera fichadas anteriores con --mitiv-desde', function () {
    config(['sirex.relojes_escribir_mitiv' => true]);

    ($this->sincronizador)()->sincronizar('192.168.7.198', '2026-10-01 00:00:00');

    expect(DB::connection('pgsql_mitiv')->table('registros_reloj')->count())->toBe(3);
});

test('deja el latido que exige el sistema viejo, sin repetirlo antes de tiempo', function () {
    config(['sirex.relojes_escribir_mitiv' => true]);

    ($this->sincronizador)()->sincronizar();
    $latidos = fn () => DB::connection('pgsql_mitiv')->table('operaciones_reloj')->where('operacion', 'CHEQUEO_ESTADO_SERVICIO');
    expect($latidos()->count())->toBe(1)
        ->and($latidos()->value('estado'))->toBe('PROCESADO')
        ->and($latidos()->value('created_at'))->toBe('2026-10-20 10:00:00');

    $this->travel(10)->minutes();
    ($this->sincronizador)()->sincronizar();
    expect($latidos()->count())->toBe(1);

    $this->travel(15)->minutes(); // ya pasaron 25 minutos
    ($this->sincronizador)()->sincronizar();
    expect($latidos()->count())->toBe(2);
});

test('sin ningún reloj funcionando no hay latido y el reloj caído queda en el tablero del sistema viejo', function () {
    config(['sirex.relojes_escribir_mitiv' => true]);
    $this->fabrica->relojes = [];

    ($this->sincronizador)()->sincronizar();

    $db = DB::connection('pgsql_mitiv');
    expect($db->table('operaciones_reloj')->where('operacion', 'CHEQUEO_ESTADO_SERVICIO')->count())->toBe(0)
        ->and($db->table('operaciones_reloj')->where('operacion', 'CHEQUEO_RELOJES')->count())->toBe(2)
        ->and($db->table('log_operaciones_reloj')->where('tipo', 'ERROR_CHECKEO')->pluck('texto')->sort()->values()->all())
        ->toBe(['Reloj inaccesible: 192.168.7.198', 'Reloj inaccesible: 192.168.7.199']);
});

test('si falla la escritura en el sistema viejo, SiRex guarda igual y reintenta la lectura', function () {
    config(['sirex.relojes_escribir_mitiv' => true, 'sirex.relojes_conexion_mitiv_escritura' => 'conexion_inexistente']);

    $r = ($this->sincronizador)()->sincronizar('192.168.7.198')[0];

    expect($r['estado'])->toBe('sincronizado')
        ->and($r['mensaje'])->toContain('sistema viejo')
        ->and(fichadasGuardadas($this->reloj198->id)->count())->toBe(3)
        ->and($this->reloj198->fresh()->registros_en_reloj)->toBeNull(); // la próxima vuelta vuelve a leer
});

test('el comando respeta el interruptor y muestra el resultado', function () {
    $this->artisan('relojes:sincronizar')->expectsOutputToContain('apagado')->assertSuccessful();
    expect(fichadasGuardadas()->count())->toBe(0);

    $this->artisan('relojes:sincronizar', ['--forzar' => true])->assertSuccessful();
    expect(fichadasGuardadas()->count())->toBe(4);

    $this->artisan('relojes:sincronizar', ['--forzar' => true, '--mitiv-desde' => 'ayer'])->assertFailed();
});

test('relojes:agregar verifica el reloj y toma sus datos', function () {
    $this->fabrica->relojes['192.168.7.250'] = new RelojSimulado([]);

    $this->artisan('relojes:agregar', ['ip' => '192.168.7.250', '--nombre' => 'Archivo', '--tipo' => 'facial'])->assertSuccessful();

    expect(RrhhReloj::where('ip', '192.168.7.250')->first())
        ->nombre->toBe('Archivo')->tipo->toBe('facial')->serie->toBe('AEWD224660235')->activo->toBeTrue();

    $this->artisan('relojes:agregar', ['ip' => '192.168.7.251'])->assertFailed(); // no responde
    $this->artisan('relojes:agregar', ['ip' => 'no-es-una-ip'])->assertFailed();
    expect(RrhhReloj::where('ip', '192.168.7.251')->exists())->toBeFalse();
});
