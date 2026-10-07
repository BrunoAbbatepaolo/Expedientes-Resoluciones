<?php

use App\Livewire\Rrhh\Asistencia;
use App\Livewire\Rrhh\Licencias;
use App\Livewire\Rrhh\Personal;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $this->computos = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $this->legal = MitivFalso::oficinaRrhh('AREA LEGAL');
    $this->ana = MitivFalso::empleadoRrhh(['apellido' => 'GOMEZ', 'nombre' => 'ANA', 'legajo' => 321, 'card_id' => 321, 'oficina_id' => $this->computos]);
    $this->luis = MitivFalso::empleadoRrhh(['apellido' => 'DIAZ', 'nombre' => 'LUIS', 'oficina_id' => $this->legal]);
    MitivFalso::horarioSemana($this->ana);
    MitivFalso::fichada(321, '2026-10-20 08:40');

    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
});

test('las pantallas de RRHH exigen el permiso rrhh_ver, aunque sea jefe', function (string $ruta) {
    $jefe = User::factory()->create();
    RrhhJefatura::create(['user_id' => $jefe->id, 'mitiv_oficina_id' => $this->computos]);

    $this->actingAs($jefe)->get(route($ruta))->assertForbidden();
    $this->actingAs($this->rrhh)->get(route($ruta))->assertOk();
})->with(['rrhh.personal', 'rrhh.asistencia', 'rrhh.licencias']);

test('el listado de personal filtra por texto y oficina', function () {
    Livewire::actingAs($this->rrhh)
        ->test(Personal::class)
        ->assertSee('GOMEZ, ANA')
        ->assertSee('DIAZ, LUIS')
        ->set('oficinaId', $this->legal)
        ->assertDontSee('GOMEZ, ANA')
        ->set('oficinaId', null)
        ->set('texto', '321')
        ->assertSee('GOMEZ, ANA')
        ->assertDontSee('DIAZ, LUIS');
});

test('el tablero del día muestra quién llegó tarde y el estado del lector', function () {
    Livewire::actingAs($this->rrhh)
        ->test(Asistencia::class)
        ->assertSee('GOMEZ, ANA')
        ->assertSee('Tarde')
        ->assertSee('El lector de relojes no responde');
});

test('las fichadas crudas se filtran por legajo', function () {
    MitivFalso::fichada(999, '2026-10-20 09:00');

    Livewire::actingAs($this->rrhh)
        ->test(Asistencia::class)
        ->set('vista', 'fichadas')
        ->assertSee('Tarjeta 999')
        ->set('legajo', '321')
        ->assertSee('20/10/2026 08:40')
        ->assertDontSee('Tarjeta 999');
});

test('la grilla del mes muestra la oficina elegida', function () {
    Livewire::actingAs($this->rrhh)
        ->test(Asistencia::class)
        ->set('vista', 'mes')
        ->set('oficinaId', $this->legal)
        ->assertSee('DIAZ')
        ->assertDontSee('GOMEZ');
});

test('licencias del período y vacaciones adeudadas', function () {
    MitivFalso::ausencia($this->luis, '2026-10-05', '2026-10-09', 'LIC. ANUAL');
    DB::connection('pgsql_mitiv')->table('vista_saldo_vacaciones')->insert(['id' => $this->ana, 'anio' => 2025, 'saldo' => 15]);

    Livewire::actingAs($this->rrhh)
        ->test(Licencias::class)
        ->assertSee('DIAZ, LUIS')
        ->assertSee('5 días')
        ->set('vista', 'adeudadas')
        ->assertSee('GOMEZ, ANA')
        ->assertSee('15 días');
});
