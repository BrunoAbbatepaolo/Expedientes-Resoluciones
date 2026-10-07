<?php

use App\Models\Permiso;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $this->oficina = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $this->ana = MitivFalso::empleadoRrhh(['apellido' => 'GOMEZ', 'nombre' => 'ANA', 'legajo' => 321, 'card_id' => 321, 'oficina_id' => $this->oficina]);
    MitivFalso::horarioSemana($this->ana);
    MitivFalso::inasistencia($this->ana, '2026-09-08', 82);
    MitivFalso::ausencia($this->ana, '2026-03-02', '2026-03-06', 'LIC. ANUAL');
    DB::connection('pgsql_mitiv')->table('vista_saldo_vacaciones')->insert(['id' => $this->ana, 'anio' => 2025, 'saldo' => 7]);

    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
});

test('los reportes son solo para RRHH', function () {
    $this->actingAs(User::factory()->create())->get(route('rrhh.reportes'))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('rrhh.reportes.ver', ['reporte' => 'adeudadas']))->assertForbidden();
    $this->actingAs($this->rrhh)->get(route('rrhh.reportes'))->assertOk();
});

test('ficha personal imprimible sin claves', function () {
    $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'ficha', 'legajo' => 321]))
        ->assertOk()
        ->assertSee('GOMEZ, ANA')
        ->assertSee('LIC. ANUAL')
        ->assertSee('Imprimir / Guardar PDF')
        ->assertDontSee('CLAVE-SECRETA')
        ->assertDontSee('dato de salud');
});

test('presentismo mensual cuenta las faltas registradas', function () {
    $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'presentismo', 'oficina' => $this->oficina, 'mes' => '2026-09']))
        ->assertOk()
        ->assertSee('GOMEZ, ANA')
        ->assertSee('Falta');
});

test('vacaciones adeudadas y licencias por tipo salen en CSV para Excel', function () {
    $csv = $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'adeudadas', 'formato' => 'csv']))
        ->assertOk()
        ->streamedContent();

    expect($csv)->toStartWith("\xEF\xBB\xBF")->toContain('"GOMEZ, ANA";')->toContain(';7');

    $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'licencias', 'desde' => '2026-01-01', 'hasta' => '2026-12-31']))
        ->assertOk()
        ->assertSee('LIC. ANUAL');
});

test('un reporte inexistente da 404', function () {
    $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'sueldos']))->assertNotFound();
});
