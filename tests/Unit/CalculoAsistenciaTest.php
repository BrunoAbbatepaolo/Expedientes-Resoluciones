<?php

use App\Enums\EstadoDia;
use App\Services\Rrhh\CalculoAsistencia;
use Carbon\CarbonImmutable;

function horario(?string $dm = '08:00:00', ?string $hm = '14:00:00', int $tm = 0, ?string $dt = null, ?string $ht = null, int $tt = 0): object
{
    return (object) ['desde_maniana' => $dm, 'hasta_maniana' => $hm, 'tolerancia_maniana' => $tm, 'desde_tarde' => $dt, 'hasta_tarde' => $ht, 'tolerancia_tarde' => $tt];
}

function marcas(string ...$horas): array
{
    return array_map(fn ($h) => CarbonImmutable::parse('2026-10-06 '.$h), $horas);
}

beforeEach(function () {
    // Regla del IPV: 10 min de tolerancia; de 10 a 30 min es llegada tarde; más de 30, falta.
    $this->calc = new CalculoAsistencia(toleranciaDefault: 10, tipoAusente: 82, tipoTarde: 7, limiteFalta: 30);
    $this->fecha = CarbonImmutable::parse('2026-10-06');
    $this->despues = CarbonImmutable::parse('2026-10-07 10:00');
    $this->h7 = horario(dm: '07:00:00', hm: '14:00:00', tm: 10);
});

test('hasta 10 minutos después de la entrada es presente', function () {
    $dia = $this->calc->dia($this->fecha, $this->h7, marcas('07:10', '14:05'), false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Presente)->and($dia->marcas)->toBe(['07:10', '14:05']);
});

test('de 10 a 30 minutos es llegada tarde', function (string $hora, int $minutos) {
    $dia = $this->calc->dia($this->fecha, $this->h7, marcas($hora), false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Tarde)->and($dia->minutosTarde)->toBe($minutos);
})->with([['07:11', 11], ['07:30', 30]]);

test('más de 30 minutos tarde cuenta como falta', function () {
    $dia = $this->calc->dia($this->fecha, $this->h7, marcas('07:31'), false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Falta)
        ->and($dia->minutosTarde)->toBe(31)
        ->and($dia->motivo)->toContain('31 min');
});

test('tolerancia cero en el horario usa la de configuración (10)', function () {
    expect($this->calc->dia($this->fecha, horario(dm: '07:00:00', tm: 0), marcas('07:10'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Presente)
        ->and($this->calc->dia($this->fecha, horario(dm: '07:00:00', tm: 0), marcas('07:12'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Tarde);
});

test('falta si no fichó en un día laboral pasado', function () {
    $dia = $this->calc->dia($this->fecha, $this->h7, [], false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Falta)->and($dia->motivo)->toContain('Sin fichadas');
});

test('sin fichada hoy queda pendiente hasta los 30 minutos y después es falta', function () {
    expect($this->calc->dia($this->fecha, $this->h7, [], false, null, null, CarbonImmutable::parse('2026-10-06 07:25'))->estado)->toBe(EstadoDia::Pendiente)
        ->and($this->calc->dia($this->fecha, $this->h7, [], false, null, null, CarbonImmutable::parse('2026-10-06 07:31'))->estado)->toBe(EstadoDia::Falta);
});

test('futuro para días que no llegaron', function () {
    $dia = $this->calc->dia($this->fecha->addDays(3), horario(), [], false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Futuro);
});

test('la inasistencia registrada manda sobre las fichadas', function (int $tipo, EstadoDia $estado) {
    $inasistencia = (object) ['tipo_inasistencia_id' => $tipo, 'nombre' => 'X'];
    $dia = $this->calc->dia($this->fecha, horario(), marcas('08:00'), false, null, $inasistencia, $this->despues);

    expect($dia->estado)->toBe($estado);
})->with([
    [82, EstadoDia::Falta],
    [7, EstadoDia::Tarde],
    [33, EstadoDia::Justificado],
]);

test('licencia y feriado ganan sobre la falta de fichadas', function () {
    expect($this->calc->dia($this->fecha, horario(), [], false, 'LIC. ANUAL', null, $this->despues)->estado)->toBe(EstadoDia::Licencia)
        ->and($this->calc->dia($this->fecha, horario(), [], true, null, null, $this->despues)->estado)->toBe(EstadoDia::Feriado);
});

test('franco si no tiene horario ese día', function () {
    $dia = $this->calc->dia($this->fecha, null, marcas('09:00'), false, null, null, $this->despues);

    expect($dia->estado)->toBe(EstadoDia::Franco)->and($dia->marcas)->toBe(['09:00']);
});

test('horario solo de tarde usa la entrada de la tarde', function () {
    $h = horario(dm: null, hm: null, dt: '15:00:00', ht: '20:00:00', tt: 10);

    expect($this->calc->dia($this->fecha, $h, marcas('15:05'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Presente)
        ->and($this->calc->dia($this->fecha, $h, marcas('15:20'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Tarde);
});

test('turno partido sin fichada a la tarde queda incompleto', function () {
    $h = horario(dm: '08:00:00', hm: '12:00:00', dt: '16:00:00', ht: '20:00:00');

    expect($this->calc->dia($this->fecha, $h, marcas('08:00', '12:00'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Incompleto)
        ->and($this->calc->dia($this->fecha, $h, marcas('08:00', '12:00', '16:05', '20:00'), false, null, null, $this->despues)->estado)->toBe(EstadoDia::Presente);
});

test('un régimen que exime de fichar se muestra como justificado y no como licencia', function () {
    $dia = $this->calc->dia($this->fecha, horario(), [], false, 'CONT', null, $this->despues, sinFichar: true);

    expect($dia->estado)->toBe(EstadoDia::Justificado)->and($dia->motivo)->toBe('CONT (no ficha)');
});
