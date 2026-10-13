<?php

use App\Livewire\Rrhh\Asistencia;
use App\Models\Permiso;
use App\Models\RrhhReloj;
use App\Models\User;
use App\Services\Rrhh\Contracts\LectorReloj;
use App\Services\Rrhh\Mitiv\LectorRelojMitiv;
use App\Services\Rrhh\Relojes\FabricaClienteReloj;
use App\Services\Rrhh\Relojes\LectorRelojSirex;
use Carbon\CarbonImmutable;
use Livewire\Livewire;
use Tests\Support\FabricaRelojesSimulada;
use Tests\Support\MitivFalso;
use Tests\Support\RelojSimulado;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00')); // martes, hora local = 10:00
    MitivFalso::crear();
    config(['sirex.relojes_activo' => true]);
    $this->ahora = fn () => CarbonImmutable::parse('2026-10-20 10:00');
    $this->estado = fn () => app(LectorReloj::class)->estado(($this->ahora)());
});

function relojConContacto(string $nombre, string $ip, ?string $contacto, array $extra = []): RrhhReloj
{
    return RrhhReloj::create(array_merge(['nombre' => $nombre, 'ip' => $ip, 'ultimo_contacto_at' => $contacto, 'desvio_segundos' => 0], $extra));
}

test('con el agente activo se usa el lector de SiRex y sin él el del sistema viejo', function () {
    expect(app(LectorReloj::class))->toBeInstanceOf(LectorRelojSirex::class);

    app()->forgetScopedInstances();
    config(['sirex.relojes_activo' => false]);
    expect(app(LectorReloj::class))->toBeInstanceOf(LectorRelojMitiv::class);
});

test('sin relojes cargados avisa cómo agregarlos', function () {
    $e = ($this->estado)();

    expect($e['alerta'])->toBeTrue()->and($e['mensaje'])->toContain('relojes:agregar')->and($e['relojes'])->toBe([]);
});

test('todos los relojes con contacto reciente: sin alerta y con el detalle de cada uno', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 09:57:00', ['ultima_fichada_at' => '2026-10-20 09:40:00', 'registros_en_reloj' => 21201, 'modelo' => 'MB360/ID']);
    relojConContacto('Planta baja', '192.168.7.199', '2026-10-20 09:58:00');

    $e = ($this->estado)();

    expect($e['alerta'])->toBeFalse()
        ->and($e['relojes'])->toHaveCount(2)
        ->and(collect($e['relojes'])->pluck('estado')->unique()->all())->toBe(['ok'])
        ->and($e['ultima_fichada']->format('H:i'))->toBe('09:40')
        ->and($e['ultimo_chequeo']->format('H:i'))->toBe('09:58');
});

test('un reloj sin contacto por más de 75 minutos en horario laboral dispara la alerta nombrándolo', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 08:30:00'); // hace 90 min
    relojConContacto('Planta baja', '192.168.7.199', '2026-10-20 09:20:00'); // hace 40 min: normal

    $e = ($this->estado)();
    $estados = collect($e['relojes'])->pluck('estado', 'nombre');

    expect($e['alerta'])->toBeTrue()
        ->and($e['mensaje'])->toContain('Mesa de entradas')->not->toContain('Planta baja')
        ->and($estados['Mesa de entradas'])->toBe('sin_contacto')
        ->and($estados['Planta baja'])->toBe('ok');
});

test('un reloj que nunca respondió alerta en horario laboral', function () {
    relojConContacto('Nuevo', '192.168.7.250', null);

    expect(($this->estado)()['relojes'][0]['estado'])->toBe('sin_contacto');
});

test('fuera de horario laboral no alerta por falta de contacto, y a las 7:30 se da margen', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-19 11:30:00'); // ayer a la mañana

    $alertaA = fn (string $hora) => app(LectorRelojSirex::class)->estado(CarbonImmutable::parse($hora))['alerta'];

    expect($alertaA('2026-10-19 20:00'))->toBeFalse() // de noche
        ->and($alertaA('2026-10-24 10:00'))->toBeFalse() // sábado
        ->and($alertaA('2026-10-20 07:30'))->toBeFalse() // recién abren
        ->and($alertaA('2026-10-20 08:30'))->toBeTrue(); // 90 min después de las 7
});

test('la hora desviada del reloj se informa aunque haya contacto', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 09:59:00', ['desvio_segundos' => 1200]);
    relojConContacto('Planta baja', '192.168.7.199', '2026-10-20 09:59:00', ['desvio_segundos' => -45]);

    $e = ($this->estado)();
    $estados = collect($e['relojes'])->pluck('estado', 'nombre');

    expect($estados['Mesa de entradas'])->toBe('hora_desviada')
        ->and($estados['Planta baja'])->toBe('ok')
        ->and($e['mensaje'])->toContain('20 min adelantada');
});

test('los relojes desactivados no cuentan', function () {
    relojConContacto('Viejo', '192.168.7.223', '2026-10-01 10:00:00', ['activo' => false]);
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 09:59:00');

    expect(($this->estado)()['relojes'])->toHaveCount(1);
});

test('Asistencia: tarjetas por reloj y aviso cuando uno no responde', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 09:59:00', ['modelo' => 'MB360/ID', 'registros_en_reloj' => 21201, 'ultima_fichada_at' => '2026-10-20 09:40:00']);
    relojConContacto('Planta baja', '192.168.7.199', '2026-10-20 07:10:00', ['fallas_seguidas' => 3, 'ultimo_error' => 'El reloj no respondió a tiempo.']);
    $rrhh = User::factory()->create();
    Permiso::create(['user_id' => $rrhh->id, 'nombre' => 'rrhh_ver']);

    Livewire::actingAs($rrhh)->test(Asistencia::class)
        ->assertSee('Mesa de entradas')
        ->assertSee('Funciona')
        ->assertSee('Sin contacto')
        ->assertSee('3 fallas seguidas: El reloj no respondió a tiempo.')
        ->assertSee('21.201')
        ->assertSee('El reloj Planta baja (192.168.7.199) no responde');
});

test('Asistencia: el botón sincroniza los relojes al instante y resume el resultado', function () {
    relojConContacto('Mesa de entradas', '192.168.7.198', '2026-10-20 09:59:00');
    relojConContacto('Planta baja', '192.168.7.199', '2026-10-20 09:59:00');
    $fabrica = new FabricaRelojesSimulada;
    $fabrica->relojes = [
        '192.168.7.198' => new RelojSimulado([['101', '2026-10-19 07:05:10', 15, 0], ['102', '2026-10-19 07:12:00', 15, 4]], hora: '2026-10-20 10:00:00'),
        '192.168.7.199' => new RelojSimulado([['201', '2026-10-19 08:00:00', 15, 0]], hora: '2026-10-20 10:00:00'),
    ];
    app()->instance(FabricaClienteReloj::class, $fabrica);
    $rrhh = User::factory()->create();
    Permiso::create(['user_id' => $rrhh->id, 'nombre' => 'rrhh_ver']);

    Livewire::actingAs($rrhh)->test(Asistencia::class)
        ->assertSee('Sincronizar ahora')
        ->call('sincronizarAhora')
        ->assertSee('3 fichadas nuevas de 2 relojes.');
});
