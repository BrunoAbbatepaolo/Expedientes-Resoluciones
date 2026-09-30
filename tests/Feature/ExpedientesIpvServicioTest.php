<?php

use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\Expediente;
use App\Services\ExpedientesIpv;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->mesa = MitivFalso::oficina('MESA DE ENTRADAS');
    $this->legal = MitivFalso::oficina('DIRECCION AREA LEGAL');
    $this->servicio = app(ExpedientesIpv::class);
});

test('mitiv es de solo lectura desde los modelos', function () {
    $id = MitivFalso::expediente('1/440-2026');

    expect(fn () => Expediente::find($id)->forceFill(['asunto' => 'x'])->save())->toThrow(LogicException::class)
        ->and(fn () => Expediente::find($id)->delete())->toThrow(LogicException::class);
});

test('el listado trae la ubicación actual y los días que lleva ahí', function () {
    MitivFalso::expediente('10/440-2026', 'GARCIA, ANA', [
        [$this->mesa, now()->subDays(20)->format('Y-m-d H:i')],
        [$this->legal, now()->subDays(5)->format('Y-m-d H:i')],
    ]);

    $fila = $this->servicio->buscar([])->items()[0];

    expect($fila->numero)->toBe('10/440-2026')
        ->and($fila->ubicacion)->toBe('DIRECCION AREA LEGAL')
        ->and($fila->dias_en_ubicacion)->toBe(5);
});

test('busca por número (prefijo) y por causante', function () {
    MitivFalso::expediente('7780/440-2026', 'GARCIA, ANA');
    MitivFalso::expediente('7781/440-2026', 'LOPEZ, PEDRO');

    expect($this->servicio->buscar(['texto' => '7780'])->total())->toBe(1)
        ->and($this->servicio->buscar(['texto' => 'lopez'])->items()[0]->numero)->toBe('7781/440-2026')
        ->and($this->servicio->buscar(['texto' => '100%'])->total())->toBe(0);
});

test('filtra por oficina donde está hoy el expediente', function () {
    MitivFalso::expediente('1/440-2026', 'A', [[$this->mesa, '2026-01-01 10:00'], [$this->legal, '2026-02-01 10:00']]);
    MitivFalso::expediente('2/440-2026', 'B', [[$this->legal, '2026-01-01 10:00'], [$this->mesa, '2026-02-01 10:00']]);

    $enLegal = $this->servicio->buscar(['oficina_id' => $this->legal]);

    expect($enLegal->total())->toBe(1)
        ->and($enLegal->items()[0]->numero)->toBe('1/440-2026');
});

test('la ficha arma la línea de tiempo con días por oficina', function () {
    $id = MitivFalso::expediente('5/440-2026', 'GARCIA, ANA', [
        [$this->mesa, '2026-09-01 09:00'],
        [$this->legal, '2026-09-11 09:00'],
    ]);

    $ficha = $this->servicio->ficha($id);

    expect($ficha['pases'])->toHaveCount(2)
        ->and($ficha['pases'][0]->oficina)->toBe('MESA DE ENTRADAS')
        ->and($ficha['pases'][0]->dias)->toBe(10)
        ->and($ficha['pases'][0]->actual)->toBeFalse()
        ->and($ficha['ubicacion']->oficina)->toBe('DIRECCION AREA LEGAL')
        ->and($ficha['ubicacion']->actual)->toBeTrue();
});

test('expedientes demorados en una oficina', function () {
    MitivFalso::expediente('1/440-2026', 'A', [[$this->legal, now()->subDays(40)->format('Y-m-d H:i')]]);
    MitivFalso::expediente('2/440-2026', 'B', [[$this->legal, now()->subDays(2)->format('Y-m-d H:i')]]);

    expect($this->servicio->enOficina($this->legal))->toHaveCount(2)
        ->and($this->servicio->enOficina($this->legal, 30)->pluck('numero')->all())->toBe(['1/440-2026'])
        ->and($this->servicio->resumenOficina($this->legal))->toMatchArray(['en_oficina' => 2, 'demorados' => 1]);
});

test('los empleados nunca exponen columnas sensibles', function () {
    MitivFalso::empleado(['apellido' => 'DIAZ']);

    $empleado = Empleado::activos()->first();

    expect($empleado->apellido)->toBe('DIAZ')
        ->and(array_key_exists('password', $empleado->getAttributes()))->toBeFalse();
});
