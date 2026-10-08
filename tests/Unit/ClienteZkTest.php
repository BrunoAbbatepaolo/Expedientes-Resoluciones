<?php

use App\Services\Rrhh\Relojes\ClienteZk;
use App\Services\Rrhh\Relojes\ErrorReloj;
use Tests\Support\RelojSimulado;

/** Vectores generados con pyzk (la implementación de referencia, probada con los relojes reales). */
dataset('paquetes de referencia', [
    'connect' => [1000, '', 0, 65534, '5050827d08000000e80317fc00000000'],
    'tamanos' => [50, '', 1234, 5, '5050827d080000003200f5fad2040600'],
    'prepare_buffer' => [1503, '010d000000000000000000', 1234, 6, '5050827d13000000df0546e8d2040700010d000000000000000000'],
    'read_buffer' => [1504, '00000000c0ff0000', 1234, 7, '5050827d10000000e00584f5d204080000000000c0ff0000'],
    'serie' => [11, '7e53657269616c4e756d62657200', 1234, 8, '5050827d160000000b0016b2d20409007e53657269616c4e756d62657200'],
    'salir con la respuesta al límite' => [1001, '', 1234, 65535, '5050827d08000000e90343f7d2040100'],
    'datos de largo impar' => [11, '7e506c6174666f726d00', 77, 65533, '5050827d120000000b006d744d00feff7e506c6174666f726d00'],
]);

test('los paquetes son idénticos a los de la implementación de referencia', function (int $cmd, string $datos, int $sesion, int $reply, string $esperado) {
    expect(bin2hex(ClienteZk::paquete($cmd, hex2bin($datos), $sesion, $reply)))->toBe($esperado);
})->with('paquetes de referencia');

test('la clave de autenticación coincide con la de referencia', function (int $clave, int $sesion, string $esperado) {
    expect(bin2hex(ClienteZk::claveDeComunicacion($clave, $sesion)))->toBe($esperado);
})->with([[0, 1234, '617d327d'], [0, 4660, '617d326b'], [0, 65535, '617d3286'], [123456, 777, '267f32fa']]);

test('la hora codificada se decodifica igual que la referencia', function (string $fecha, string $bruto) {
    expect(ClienteZk::decodificarHora(unpack('V', hex2bin($bruto))[1]))->toBe($fecha);
})->with([['2026-10-08 08:09:07', 'a3ab4833'], ['2026-01-31 23:59:59', '7f0af831'], ['2025-12-31 00:00:00', '80dacd31']]);

test('una fecha imposible (31 de febrero) no se interpreta', function () {
    expect(ClienteZk::decodificarHora(RelojSimulado::codificarHora('2026-02-28 10:00:00') + 3 * 86400))->toBeNull();
});

function fichadasDePrueba(int $cantidad): array
{
    $filas = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $filas[] = [(string) (100 + $i % 400), sprintf('2026-09-%02d %02d:%02d:%02d', 1 + $i % 28, 7 + $i % 8, $i % 60, ($i * 7) % 60), 15, $i % 2 ? 4 : 0];
    }

    return $filas;
}

function conectado(RelojSimulado $reloj, int $clave = 0): ClienteZk
{
    $cliente = new ClienteZk($reloj, $clave);
    $cliente->conectar();

    return $cliente;
}

test('conecta, se autentica y toma la sesión del reloj', function () {
    $reloj = new RelojSimulado;
    conectado($reloj);

    expect($reloj->comandos)->toBe([ClienteZk::CMD_CONNECT, ClienteZk::CMD_AUTH]);
});

test('con un reloj que no pide autenticación conecta directo y usa la sesión en los comandos siguientes', function () {
    $reloj = new RelojSimulado(fichadasDePrueba(3));
    $reloj->pideAutenticacion = false;
    $cliente = conectado($reloj);
    $cliente->memoria();

    expect($reloj->comandos)->toBe([ClienteZk::CMD_CONNECT, ClienteZk::CMD_GET_FREE_SIZES])
        ->and($reloj->sesiones)->toBe([0, RelojSimulado::SESION]);
});

test('una clave de comunicación equivocada se informa con claridad', function () {
    $reloj = new RelojSimulado;
    $reloj->clave = 999;

    expect(fn () => conectado($reloj, 0))->toThrow(ErrorReloj::class, 'rechazó la clave');
    expect(fn () => conectado($reloj, 999))->not->toThrow(ErrorReloj::class);
});

test('informa el equipo, la hora y la memoria', function () {
    $cliente = conectado(new RelojSimulado(fichadasDePrueba(7)));

    expect($cliente->informacion())->toBe([
        'firmware' => 'Ver 6.60 Sep 19 2019', 'serie' => 'AEWD224660235', 'nombre' => 'MB360/ID',
        'plataforma' => 'ZMM220_TFT', 'hora' => '2026-10-08 08:34:12',
    ])->and($cliente->memoria())->toBe(['registros' => 7, 'usuarios' => 761, 'capacidad_registros' => 100000]);
});

test('lee toda la memoria por bloques y fragmentos sin perder ni repetir fichadas', function () {
    $fichadas = fichadasDePrueba(2500); // 100 KB: más de un bloque de 65.472 bytes
    $reloj = new RelojSimulado($fichadas);
    $lectura = conectado($reloj)->leerFichadas();

    expect($lectura->fichadas)->toHaveCount(2500)
        ->and($lectura->descartadas)->toBe(0)
        ->and($lectura->bytesPorRegistro)->toBe(40)
        ->and(array_map(fn ($f) => [$f->userId, $f->fechaHora, $f->status, $f->punch], $lectura->fichadas))->toBe($fichadas)
        ->and(array_count_values($reloj->comandos)[ClienteZk::CMD_READ_BUFFER])->toBe(2)
        ->and(end($reloj->comandos))->toBe(ClienteZk::CMD_FREE_DATA);
});

test('también lee cuando el reloj devuelve todo de una vez', function () {
    $reloj = new RelojSimulado(fichadasDePrueba(50));
    $reloj->modo = 'directo';

    expect(conectado($reloj)->leerFichadas()->fichadas)->toHaveCount(50);
});

test('lee registros de 16 bytes', function () {
    $fichadas = fichadasDePrueba(30);
    $reloj = new RelojSimulado($fichadas);
    $reloj->bytesPorRegistro = 16;
    $lectura = conectado($reloj)->leerFichadas();

    expect($lectura->bytesPorRegistro)->toBe(16)
        ->and(array_map(fn ($f) => [$f->userId, $f->fechaHora], $lectura->fichadas))->toBe(array_map(fn ($f) => [$f[0], $f[1]], $fichadas));
});

test('los registros de 8 bytes (sin número de tarjeta) se rechazan con un mensaje claro', function () {
    $reloj = new RelojSimulado(fichadasDePrueba(10));
    $reloj->bytesPorRegistro = 8;

    expect(fn () => conectado($reloj)->leerFichadas())->toThrow(ErrorReloj::class, '8 bytes');
});

test('si alguien ficha entre pedir el tamaño y leer, la lectura sigue funcionando', function () {
    $reloj = new RelojSimulado(fichadasDePrueba(1000));
    $reloj->registrosDeMenos = 1; // el tamaño informado queda uno por debajo de lo que se lee

    expect(conectado($reloj)->leerFichadas()->fichadas)->toHaveCount(1000);
});

test('los registros con fecha inválida se cuentan como descartados y no frenan la lectura', function () {
    $fichadas = fichadasDePrueba(10);
    $fichadas[4][1] = 'INVALIDA';

    $lectura = conectado(new RelojSimulado($fichadas))->leerFichadas();

    expect($lectura->fichadas)->toHaveCount(9)->and($lectura->descartadas)->toBe(1);
});

test('un reloj sin fichadas devuelve una lectura vacía sin pedir datos', function () {
    $reloj = new RelojSimulado([]);
    $lectura = conectado($reloj)->leerFichadas();

    expect($lectura->fichadas)->toBe([])->and($reloj->comandos)->not->toContain(ClienteZk::CMD_PREPARE_BUFFER);
});

test('solo usa comandos de lectura y cierra la conexión', function () {
    $reloj = new RelojSimulado(fichadasDePrueba(20));
    $cliente = conectado($reloj);
    $cliente->informacion();
    $cliente->leerFichadas();
    $cliente->desconectar();

    $permitidos = [
        ClienteZk::CMD_CONNECT, ClienteZk::CMD_AUTH, ClienteZk::CMD_EXIT, ClienteZk::CMD_OPTIONS_RRQ, ClienteZk::CMD_GET_TIME,
        ClienteZk::CMD_GET_VERSION, ClienteZk::CMD_GET_FREE_SIZES, ClienteZk::CMD_PREPARE_BUFFER, ClienteZk::CMD_READ_BUFFER, ClienteZk::CMD_FREE_DATA,
    ];
    expect(array_diff($reloj->comandos, $permitidos))->toBe([])
        ->and(end($reloj->comandos))->toBe(ClienteZk::CMD_EXIT)
        ->and($reloj->cerrado)->toBeTrue();
});

test('comandos sin conexión previa fallan', function () {
    expect(fn () => (new ClienteZk(new RelojSimulado))->memoria())->toThrow(ErrorReloj::class, 'No hay conexión');
});
