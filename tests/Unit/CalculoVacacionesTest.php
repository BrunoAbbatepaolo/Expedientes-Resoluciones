<?php

use App\Services\Rrhh\CalculoVacaciones;
use Carbon\CarbonImmutable;

test('días según antigüedad: 20, 25, 30 y 35 con tope', function (string $ingreso, int $antiguedad, int $dias) {
    expect(CalculoVacaciones::corresponden(CarbonImmutable::parse($ingreso), 2026))
        ->toBe(['antiguedad' => $antiguedad, 'dias' => $dias]);
})->with([
    ['2023-01-01', 4, 20],
    ['2022-01-01', 5, 25],
    ['2017-01-01', 10, 30],
    ['2012-01-01', 15, 35],
    ['1990-03-01', 37, 35],
]);

test('el primer año es proporcional a los meses trabajados', function () {
    expect(CalculoVacaciones::corresponden(CarbonImmutable::parse('2026-07-01'), 2026))
        ->toBe(['antiguedad' => 0, 'dias' => 8]); // 12 - 7 = 5 meses → 20 × 5 / 12 ≈ 8
});

test('los años reconocidos suman antigüedad', function () {
    $sin = CalculoVacaciones::corresponden(CarbonImmutable::parse('2024-01-01'), 2026);
    $con = CalculoVacaciones::corresponden(CarbonImmutable::parse('2024-01-01'), 2026, ['anios' => 8, 'meses' => 0, 'dias' => 0]);

    expect($sin['dias'])->toBe(20)->and($con)->toBe(['antiguedad' => 11, 'dias' => 30]);
});

test('sin fecha de ingreso o ingreso posterior al período no corresponden días', function () {
    expect(CalculoVacaciones::corresponden(null, 2026)['dias'])->toBe(0)
        ->and(CalculoVacaciones::corresponden(CarbonImmutable::parse('2027-02-01'), 2026)['dias'])->toBe(0);
});

test('los días hábiles excluyen fines de semana y feriados', function () {
    // Lunes 5 a domingo 18 de octubre de 2026 = 10 hábiles; el 12 es feriado.
    expect(CalculoVacaciones::diasHabiles(CarbonImmutable::parse('2026-10-05'), CarbonImmutable::parse('2026-10-18'), ['2026-10-12' => 'Feriado']))
        ->toBe(9);
});
