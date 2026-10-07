<?php

use App\Livewire\Rrhh\FichaEmpleado;
use App\Livewire\Rrhh\MiEquipo;
use App\Models\Permiso;
use App\Models\RrhhAcceso;
use App\Models\RrhhJefatura;
use App\Models\User;
use App\Services\Rrhh\Organigrama;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    $this->otra = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    app(Organigrama::class)->asignarPadre($this->dpto, $this->area);

    $this->ana = MitivFalso::empleadoRrhh(['apellido' => 'GOMEZ', 'nombre' => 'ANA', 'oficina_id' => $this->dpto, 'card_id' => 501, 'numero_documento' => '30111222']);
    $this->luis = MitivFalso::empleadoRrhh(['apellido' => 'DIAZ', 'nombre' => 'LUIS', 'oficina_id' => $this->otra]);
    MitivFalso::horarioSemana($this->ana);
    MitivFalso::fichada(501, '2026-10-20 08:05');

    $this->jefe = User::factory()->create();
    RrhhJefatura::create(['user_id' => $this->jefe->id, 'mitiv_oficina_id' => $this->area]);
    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
});

test('Mi equipo es solo para jefes', function () {
    $this->actingAs(User::factory()->create())->get(route('rrhh.equipo'))->assertForbidden();
});

test('el jefe ve hoy a la gente de las oficinas que dependen de la suya', function () {
    Livewire::actingAs($this->jefe)
        ->test(MiEquipo::class)
        ->assertSee('GOMEZ, ANA')
        ->assertSee('08:05')
        ->assertDontSee('DIAZ')
        ->set('vista', 'mes')
        ->set('oficinaId', $this->dpto)
        ->assertSee('GOMEZ')
        ->set('vista', 'gente')
        ->assertSee('Legajo');
});

test('el jefe no puede abrir la ficha de alguien fuera de su alcance', function () {
    $this->actingAs($this->jefe)->get(route('rrhh.ficha', $this->luis))->assertForbidden();
});

test('el jefe ve datos básicos sin DNI ni pestañas personales y no queda registrado', function () {
    Livewire::actingAs($this->jefe)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->assertSee('GOMEZ, ANA')
        ->assertDontSee('30111222')
        ->assertDontSee('Familia')
        ->set('pestania', 'asistencia')
        ->assertSee('Presente');

    expect(RrhhAcceso::count())->toBe(0);
});

test('RRHH ve datos personales, no los sensibles, y el acceso se registra una vez', function () {
    Livewire::actingAs($this->rrhh)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->assertSee('30111222')
        ->assertDontSee('dato de salud')
        ->assertDontSee('CLAVE-SECRETA')
        ->set('pestania', 'familia')
        ->set('pestania', 'licencias');

    expect(RrhhAcceso::where('user_id', $this->rrhh->id)->count())->toBe(1);
});

test('la jefa de RRHH ve los datos sensibles', function () {
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_sensibles']);

    Livewire::actingAs($this->rrhh)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->assertSee('dato de salud')
        ->assertDontSee('CLAVE-SECRETA');
});

test('los archivos del legajo se descargan solo con nivel personal', function () {
    $archivo = DB::connection('pgsql_mitiv')->table('empleado_archivos')->insertGetId([
        'nombre' => 'dni.pdf', 'empleado_id' => $this->ana, 'contenido' => 'PDF-DNI', 'mimetype' => 'application/pdf',
    ]);

    $this->actingAs($this->jefe)->get(route('rrhh.archivo', [$this->ana, $archivo]))->assertForbidden();
    $this->actingAs($this->rrhh)->get(route('rrhh.archivo', [$this->ana, $archivo]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});
