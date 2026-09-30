<?php

test('convierte números a letras según la norma', function (int $numero, string $esperado) {
    expect(convertNumberToLetters($numero))->toBe($esperado);
})->with([
    [1, 'UNO'],
    [16, 'DIECISÉIS'],
    [21, 'VEINTIUNO'],
    [22, 'VEINTIDÓS'],
    [30, 'TREINTA'],
    [31, 'TREINTA Y UNO'],
    [90, 'NOVENTA'],
    [100, 'CIEN'],
    [101, 'CIENTO UNO'],
    [110, 'CIENTO DIEZ'],
    [999, 'NOVECIENTOS NOVENTA Y NUEVE'],
    [1000, 'MIL'],
    [1100, 'MIL CIEN'],
    [2030, 'DOS MIL TREINTA'],
    [21000, 'VEINTIÚN MIL'],
    [31000, 'TREINTA Y UN MIL'],
    [100000, 'CIEN MIL'],
    [101000, 'CIENTO UN MIL'],
    [1000000, 'UN MILLÓN'],
    [1000001, 'UN MILLÓN UNO'],
    [21000000, 'VEINTIÚN MILLONES'],
    [2500300, 'DOS MILLONES QUINIENTOS MIL TRESCIENTOS'],
]);

test('num2letras con montos típicos', function () {
    expect(num2letras('1530.5'))->toBe('MIL QUINIENTOS TREINTA con 50/100')
        ->and(num2letras('$ 100'))->toBe('CIEN con 00/100')
        ->and(num2letras('0'))->toBe('');
});
