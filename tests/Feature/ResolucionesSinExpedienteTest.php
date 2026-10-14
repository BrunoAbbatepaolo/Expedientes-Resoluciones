<?php

use App\Models\Resolucion;
use App\Services\Resoluciones\SinExpediente;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    MitivFalso::expediente('1234/440-2026', 'GARCIA, ANA');
    MitivFalso::expediente('77/440-2026', 'BAJA, LUIS', [], ['habilitado' => false]);
});

test('detecta las resoluciones cuyo expediente no existe o está deshabilitado', function () {
    $buena = Resolucion::factory()->create(['numero_exp' => '1234/440-2026']);
    $mala = Resolucion::factory()->create(['numero_exp' => '9999/440-2026']);
    $baja = Resolucion::factory()->create(['numero_exp' => '77/440-2026']);

    $ids = app(SinExpediente::class)->buscar()->pluck('id')->all();

    expect($ids)->toContain($mala->id, $baja->id)->not->toContain($buena->id);
});

test('revisa todos los lotes, no solo el primero', function () {
    Resolucion::factory()->count(3)->create(['numero_exp' => '1234/440-2026']);
    foreach (range(1, 3) as $i) {
        Resolucion::factory()->create(['numero_exp' => "X{$i}/440-2026"]);
    }

    expect(app(SinExpediente::class)->buscar(lote: 2))->toHaveCount(3);
});

test('el comando lista las huérfanas y no modifica nada', function () {
    Resolucion::factory()->create(['numero_exp' => '9999/440-2026', 'numero_resolucion' => '55/2026']);

    $this->artisan('resoluciones:sin-expediente')
        ->expectsOutputToContain('9999/440-2026')
        ->assertSuccessful();

    expect(Resolucion::count())->toBe(1);
});

test('el comando avisa cuando todo está bien', function () {
    Resolucion::factory()->create(['numero_exp' => '1234/440-2026']);

    $this->artisan('resoluciones:sin-expediente')->expectsOutputToContain('Todas las resoluciones')->assertSuccessful();
});
