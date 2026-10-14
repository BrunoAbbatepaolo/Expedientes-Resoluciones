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
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $this->ana)->update(['fecha_ingreso' => '2015-01-01']); // 30 días del período vigente 2025

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

    expect($csv)->toStartWith("\xEF\xBB\xBF")->toContain('"GOMEZ, ANA";')->toContain(';30'); // la LIC. ANUAL cargada es del período 2026, todavía no vigente

    $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'licencias', 'desde' => '2026-01-01', 'hasta' => '2026-12-31']))
        ->assertOk()
        ->assertSee('LIC. ANUAL');
});

test('un reporte inexistente da 404', function () {
    $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'sueldos']))->assertNotFound();
});

test('inasistencias por empleado cuenta por tipo y respeta el período', function () {
    MitivFalso::inasistencia($this->ana, '2026-09-15', 82);
    MitivFalso::inasistencia($this->ana, '2026-02-01', 82); // fuera del período

    $csv = $this->actingAs($this->rrhh)
        ->get(route('rrhh.reportes.ver', ['reporte' => 'inasistencias', 'desde' => '2026-09-01', 'hasta' => '2026-09-30', 'formato' => 'csv']))
        ->assertOk()->streamedContent();

    expect($csv)->toContain('"GOMEZ, ANA"')->toContain('AUSENTE')->toContain(';2');

    $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'inasistencias', 'desde' => '2026-09-30', 'hasta' => '2026-09-01']))->assertStatus(422);
});

test('cumpleaños del mes muestra solo día y mes, nunca el año ni la edad', function () {
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $this->ana)->update(['fecha_nacimiento' => '1985-10-23']);
    $otra = MitivFalso::empleadoRrhh(['apellido' => 'RUIZ', 'nombre' => 'EVA', 'oficina_id' => $this->oficina]);
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $otra)->update(['fecha_nacimiento' => '1990-11-02']);

    $r = $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'cumpleanios', 'mes' => 10]))->assertOk();

    $r->assertSee('GOMEZ, ANA')->assertDontSee('RUIZ, EVA')->assertDontSee('1985')->assertDontSee('1990');
});

test('cumpleaños, antigüedad y personal son solo para RRHH', function () {
    foreach (['cumpleanios', 'antiguedad', 'personal', 'inasistencias'] as $reporte) {
        $this->actingAs(User::factory()->create())->get(route('rrhh.reportes.ver', ['reporte' => $reporte]))->assertForbidden();
    }
});

test('antigüedad suma los reconocimientos al tiempo en el IPV', function () {
    DB::connection('pgsql_mitiv')->table('empleados')->where('id', $this->ana)->update([
        'fecha_ingreso' => '2016-10-20', 'anio_reconocimiento_nacional' => 2, 'mes_reconocimiento_provincial' => 3,
    ]);

    $csv = $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'antiguedad', 'formato' => 'csv']))->assertOk()->streamedContent();

    expect($csv)->toContain('10 años, 0 meses, 0 días')->toContain('12 años, 3 meses, 0 días');
});

test('el listado de personal separa activos y pasivos', function () {
    $pasivo = MitivFalso::empleadoRrhh(['apellido' => 'LUNA', 'nombre' => 'OSCAR', 'oficina_id' => $this->oficina, 'espasivo' => true]);

    $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'personal']))->assertSee('GOMEZ, ANA')->assertDontSee('LUNA, OSCAR');
    $this->actingAs($this->rrhh)->get(route('rrhh.reportes.ver', ['reporte' => 'personal', 'estado' => 'pasivos']))->assertSee('LUNA, OSCAR')->assertDontSee('GOMEZ, ANA');
});

test('la pantalla de reportes ofrece los nuevos informes', function () {
    $this->actingAs($this->rrhh)->get(route('rrhh.reportes'))->assertOk()
        ->assertSee('Inasistencias por empleado')->assertSee('Cumpleaños del mes')->assertSee('Antigüedad del personal')->assertSee('Listado de personal');
});
