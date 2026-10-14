<?php

use App\Livewire\IngresosOficina;
use App\Models\Permiso;
use App\Models\User;
use App\Services\ExpedientesIpv;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->mesa = MitivFalso::oficina('MESA DE ENTRADAS');
    $this->legal = MitivFalso::oficina('DIRECCION AREA LEGAL');
    $this->otra = MitivFalso::oficina('CONTADURIA');
    $this->servicio = app(ExpedientesIpv::class);

    $this->user = User::factory()->create();
    Permiso::create(['user_id' => $this->user->id, 'nombre' => 'expediente_ver']);
});

test('lista lo que entró a la oficina en el período aunque hoy esté en otra', function () {
    // Entró a LEGAL el 10/09 y siguió a CONTADURIA: no importa dónde está hoy.
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->mesa, '2026-09-05 09:00'], [$this->legal, '2026-09-10 10:00'], [$this->otra, '2026-09-20 11:00']]);
    // Entró fuera del período.
    MitivFalso::expediente('2/440-2026', 'LOPEZ, PEDRO', [[$this->legal, '2026-08-01 09:00']]);
    // Nunca pasó por LEGAL.
    MitivFalso::expediente('3/440-2026', 'DIAZ, LUIS', [[$this->mesa, '2026-09-12 09:00']]);

    $r = $this->servicio->ingresosAOficina($this->legal, '2026-09-01', '2026-09-30');

    expect($r['filas'])->toHaveCount(1)
        ->and($r['filas'][0]->numero)->toBe('1/440-2026')
        ->and($r['filas'][0]->viene_de)->toBe('MESA DE ENTRADAS')
        ->and($r['truncado'])->toBeFalse();
});

test('un expediente que entró varias veces cuenta una sola vez con su primera entrada', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [
        [$this->legal, '2026-09-02 09:00'], [$this->otra, '2026-09-03 09:00'], [$this->legal, '2026-09-10 09:00'],
    ]);

    $fila = $this->servicio->ingresosAOficina($this->legal, '2026-09-01', '2026-09-30')['filas'][0];

    expect($fila->veces)->toBe(2)
        ->and($fila->fecha_hora)->toStartWith('2026-09-02');
});

test('el último día del período entra completo (hasta las 23:59)', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->legal, '2026-09-30 23:30']]);

    expect($this->servicio->ingresosAOficina($this->legal, '2026-09-30', '2026-09-30')['filas'])->toHaveCount(1);
});

test('el período se valida', function () {
    expect(ExpedientesIpv::errorDePeriodo('2026-09-01', '2026-09-30'))->toBeNull()
        ->and(ExpedientesIpv::errorDePeriodo('', '2026-09-30'))->not->toBeNull()
        ->and(ExpedientesIpv::errorDePeriodo('2026-02-31', '2026-03-05'))->not->toBeNull()
        ->and(ExpedientesIpv::errorDePeriodo('2026-09-30', '2026-09-01'))->not->toBeNull()
        ->and(ExpedientesIpv::errorDePeriodo('2024-01-01', '2026-01-01'))->toContain('un año');
});

test('la pantalla muestra los ingresos y el botón de CSV', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->legal, '2026-09-10 10:00']]);

    Livewire::actingAs($this->user)->test(IngresosOficina::class)
        ->assertSee('Elegí una oficina')
        ->set('oficinaId', $this->legal)->set('desde', '2026-09-01')->set('hasta', '2026-09-30')
        ->assertSee('1/440-2026')->assertSee('GARCIA, ANA')->assertSee('Exportar CSV')
        ->set('hasta', '2025-01-01')
        ->assertSee('no puede ser posterior');
});

test('la pantalla y el CSV exigen expediente_ver', function () {
    $sinPermiso = User::factory()->create();

    $this->actingAs($sinPermiso)->get(route('expedientes.ipv.ingresos'))->assertForbidden();
    $this->actingAs($sinPermiso)->get(route('expedientes.ipv.ingresos.csv', ['oficina' => $this->legal, 'desde' => '2026-09-01', 'hasta' => '2026-09-30']))->assertForbidden();
});

test('el CSV baja con cabecera, BOM y datos; neutraliza fórmulas', function () {
    MitivFalso::expediente('1/440-2026', '=CMD|calc', [[$this->mesa, '2026-09-05 09:00'], [$this->legal, '2026-09-10 10:00']]);

    $r = $this->actingAs($this->user)->get(route('expedientes.ipv.ingresos.csv', ['oficina' => $this->legal, 'desde' => '2026-09-01', 'hasta' => '2026-09-30']));
    $r->assertOk()->assertDownload();
    $csv = $r->streamedContent();

    expect($csv)->toStartWith("\xEF\xBB\xBFExpediente;Causante;Asunto;")
        ->and($csv)->toContain('1/440-2026')->toContain("'=CMD|calc")->toContain('10/09/2026 10:00')->toContain('MESA DE ENTRADAS');
});

test('el CSV rechaza oficina o fechas inválidas', function () {
    $this->actingAs($this->user)->get(route('expedientes.ipv.ingresos.csv', ['oficina' => 'x', 'desde' => '2026-09-01', 'hasta' => '2026-09-30']))->assertNotFound();
    $this->actingAs($this->user)->get(route('expedientes.ipv.ingresos.csv', ['oficina' => $this->legal, 'desde' => 'mal', 'hasta' => '2026-09-30']))->assertStatus(422);
    $this->actingAs($this->user)->get(route('expedientes.ipv.ingresos.csv', ['oficina' => 9999, 'desde' => '2026-09-01', 'hasta' => '2026-09-30']))->assertNotFound();
});
