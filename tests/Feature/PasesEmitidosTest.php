<?php

use App\Livewire\PasesEmitidos;
use App\Models\Permiso;
use App\Models\User;
use App\Services\ExpedientesIpv;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->mesa = MitivFalso::oficina('MESA DE ENTRADAS');
    $this->legal = MitivFalso::oficina('DIRECCION AREA LEGAL');
    $this->conta = MitivFalso::oficina('CONTADURIA');
    $this->servicio = app(ExpedientesIpv::class);

    $this->user = User::factory()->create();
    Permiso::create(['user_id' => $this->user->id, 'nombre' => 'expediente_ver']);
});

test('lista los pases que salieron de la oficina, con destino, y no cuenta el alta', function () {
    $emp = MitivFalso::empleado(['apellido' => 'GOMEZ', 'nombre' => 'LAURA']);
    // Alta en MESA (sin origen) → pasa a LEGAL el 10/09 → pasa a CONTADURIA el 20/09.
    $id = MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->mesa, '2026-09-05 09:00'], [$this->legal, '2026-09-10 10:00'], [$this->conta, '2026-09-20 11:00']]);
    DB::connection('pgsql_mitiv')->table('expediente_pases')->where('expediente_id', $id)->where('oficina_id', $this->conta)->update(['empleado_id' => $emp]);

    $r = $this->servicio->pasesEmitidos($this->legal, '2026-09-01', '2026-09-30');

    expect($r['filas'])->toHaveCount(1)
        ->and($r['filas'][0]->origen)->toBe('DIRECCION AREA LEGAL')
        ->and($r['filas'][0]->destino)->toBe('CONTADURIA')
        ->and($r['filas'][0]->apellido)->toBe('GOMEZ');

    // Sin filtrar la oficina de origen salen los dos pases con origen (MESA→LEGAL y LEGAL→CONTADURIA), no el alta.
    expect($this->servicio->pasesEmitidos(null, '2026-09-01', '2026-09-30')['filas'])->toHaveCount(2);
});

test('respeta el período, incluido el último día', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->mesa, '2026-08-01 09:00'], [$this->legal, '2026-09-30 23:30']]);
    MitivFalso::expediente('2/440-2026', 'LOPEZ, PEDRO', [[$this->mesa, '2026-08-01 09:00'], [$this->legal, '2026-08-15 09:00']]);

    expect($this->servicio->pasesEmitidos(null, '2026-09-30', '2026-09-30')['filas'])->toHaveCount(1);
});

test('la pantalla muestra los pases y exige expediente_ver', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->mesa, now()->startOfMonth()->addDay()->format('Y-m-d').' 09:00'], [$this->legal, now()->startOfMonth()->addDay()->format('Y-m-d').' 10:00']]);

    Livewire::actingAs($this->user)->test(PasesEmitidos::class)
        ->assertSee('1/440-2026')->assertSee('MESA DE ENTRADAS')->assertSee('DIRECCION AREA LEGAL');

    $sin = User::factory()->create();
    $this->actingAs($sin)->get(route('expedientes.ipv.pases'))->assertForbidden();
});

test('el CSV baja con cabecera y datos, y la impresión sale en A4', function () {
    MitivFalso::expediente('1/440-2026', '=CMD|calc', [[$this->mesa, '2026-09-05 09:00'], [$this->legal, '2026-09-10 10:00']]);
    $q = ['desde' => '2026-09-01', 'hasta' => '2026-09-30'];

    $csv = $this->actingAs($this->user)->get(route('expedientes.ipv.pases.csv', $q))->assertOk()->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBFFecha;Expediente")->toContain('1/440-2026')->toContain("'=CMD|calc")->not->toContain(';=CMD');

    $this->actingAs($this->user)->get(route('expedientes.ipv.pases.imprimir', $q + ['origen' => $this->mesa]))
        ->assertOk()->assertSee('Pases emitidos')->assertSee('1/440-2026');

    $this->actingAs($this->user)->get(route('expedientes.ipv.pases.csv', ['desde' => 'x', 'hasta' => 'y']))->assertStatus(422);
    $this->actingAs($this->user)->get(route('expedientes.ipv.pases.csv', $q + ['origen' => 99999]))->assertNotFound();
    $this->actingAs(User::factory()->create())->get(route('expedientes.ipv.pases.csv', $q))->assertForbidden();
});

test('Ingresos a oficina también se imprime', function () {
    MitivFalso::expediente('1/440-2026', 'GARCIA, ANA', [[$this->mesa, '2026-09-05 09:00'], [$this->legal, '2026-09-10 10:00']]);

    $this->actingAs($this->user)->get(route('expedientes.ipv.ingresos.imprimir', ['oficina' => $this->legal, 'desde' => '2026-09-01', 'hasta' => '2026-09-30']))
        ->assertOk()->assertSee('Ingresos a oficina')->assertSee('1/440-2026')->assertSee('DIRECCION AREA LEGAL');

    $this->actingAs(User::factory()->create())->get(route('expedientes.ipv.ingresos.imprimir', ['oficina' => $this->legal, 'desde' => '2026-09-01', 'hasta' => '2026-09-30']))->assertForbidden();
});
