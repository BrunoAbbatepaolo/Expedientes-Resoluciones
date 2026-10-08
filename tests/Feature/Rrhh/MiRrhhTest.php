<?php

use App\Livewire\Rrhh\MiRrhh;
use App\Models\User;
use App\Services\Rrhh\AlcanceFactory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $this->oficina = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
});

test('sin legajo cargado avisa que hay que completarlo', function () {
    $this->actingAs(User::factory()->create(['legajo' => null]))
        ->get(route('rrhh.mio'))
        ->assertOk()
        ->assertSee('no tiene un legajo cargado');
});

test('con un legajo que no existe en el sistema de personal avisa sin romper', function () {
    $this->actingAs(User::factory()->create(['legajo' => '99999']))
        ->get(route('rrhh.mio'))
        ->assertOk()
        ->assertSee('No encontramos tu legajo');
});

test('muestra saldo, faltas del mes, licencias y sus datos', function () {
    $id = MitivFalso::empleadoRrhh(['legajo' => 777, 'card_id' => 777, 'oficina_id' => $this->oficina, 'apellido' => 'GOMEZ', 'enfermedad' => 'asma', 'fecha_ingreso' => '2020-01-01']);
    MitivFalso::horarioSemana($id);
    MitivFalso::inasistencia($id, '2026-10-06', 82);
    MitivFalso::ausencia($id, '2026-10-14', '2026-10-15', 'LIC. ANUAL', 'viaje'); // período 2026: todavía no vigente
    MitivFalso::vacaciones($id, '2026-02-02', '2026-02-06', 2025); // 5 hábiles del período vigente (2025)
    MitivFalso::vacaciones($id, '2025-02-03', '2025-02-07', 2024); // período vencido: no cuenta

    Livewire::actingAs(User::factory()->create(['legajo' => '777']))
        ->test(MiRrhh::class)
        ->assertSee('Vacaciones pendientes (período 2025)')
        ->assertSee('20') // 6 años de antigüedad al cierre de 2025 = 25 días, menos 5
        ->assertSee('de 25')
        ->assertSee('LIC. ANUAL')
        ->assertSee('viaje')
        ->assertSee('AUSENTE')
        ->assertSee('DPTO. COMPUTOS')
        ->assertSee('asma')
        ->assertDontSee('CLAVE-SECRETA')
        ->call('moverMes', -1)
        ->assertSet('mes', '2026-09');
});

test('si el sistema viejo no responde muestra un aviso', function () {
    MitivFalso::empleadoRrhh(['legajo' => 778]);
    $user = User::factory()->create(['legajo' => '778']);
    $this->actingAs($user);
    app(AlcanceFactory::class)->para($user);
    DB::connection('pgsql_mitiv')->getSchemaBuilder()->drop('ausencias');

    Livewire::test(MiRrhh::class)->assertSee('No se pudieron consultar los datos de RRHH');
});

test('el calendario lista los feriados del año', function () {
    MitivFalso::feriado('2026-10-12', 'Día del Respeto a la Diversidad Cultural');

    $this->actingAs(User::factory()->create())
        ->get(route('rrhh.calendario', ['anio' => 2026]))
        ->assertOk()
        ->assertSee('Día del Respeto a la Diversidad Cultural');
});

test('el menú muestra Mi RRHH a quien tiene legajo', function () {
    $this->actingAs(User::factory()->create(['legajo' => '777']))
        ->get(route('dashboard'))
        ->assertSee(route('rrhh.mio'))
        ->assertSee(route('rrhh.calendario'));
});
