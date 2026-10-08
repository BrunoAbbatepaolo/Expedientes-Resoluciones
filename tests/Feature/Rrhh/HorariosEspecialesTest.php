<?php

use App\Enums\EstadoDia;
use App\Livewire\Rrhh\FichaEmpleado;
use App\Models\Permiso;
use App\Models\RrhhHorarioEspecial;
use App\Models\User;
use App\Services\Rrhh\AlcanceFactory;
use App\Services\Rrhh\Contracts\Asistencias;
use Carbon\CarbonImmutable;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    MitivFalso::crear();
    $oficina = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $this->ana = MitivFalso::empleadoRrhh(['apellido' => 'GOMEZ', 'nombre' => 'ANA', 'card_id' => 900, 'oficina_id' => $oficina]);
    MitivFalso::horarioSemana($this->ana, '07:00', '14:00', 10);
    MitivFalso::fichada(900, '2026-10-19 08:05');

    $this->jefa = User::factory()->create();
    foreach (['rrhh_ver', 'rrhh_sensibles', 'rrhh_horarios'] as $p) {
        Permiso::create(['user_id' => $this->jefa->id, 'nombre' => $p]);
    }
    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
});

function diaDeAna($test, string $fecha)
{
    app()->forgetScopedInstances();
    $dias = app(Asistencias::class)->mes(app(AlcanceFactory::class)->para($test->rrhh), $test->ana, 2026, 10);

    return collect($dias)->first(fn ($d) => $d->fecha->toDateString() === $fecha);
}

test('la jefa de RRHH carga un horario de 8 a 13 y la asistencia lo usa', function () {
    // Con el horario del sistema viejo (7 a 14), entrar 08:05 es más de 30 min tarde: falta.
    expect(diaDeAna($this, '2026-10-19')->estado)->toBe(EstadoDia::Falta);

    Livewire::actingAs($this->jefa)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->set('pestania', 'horario')
        ->assertSee('Nuevo horario especial')
        ->set('nuevo.desde_maniana', '08:00')
        ->set('nuevo.hasta_maniana', '13:00')
        ->set('nuevo.vigente_desde', '2026-10-01')
        ->set('nuevo.motivo', 'Resolución 123/26')
        ->call('agregarHorario')
        ->assertHasNoErrors()
        ->assertSee('Resolución 123/26')
        ->assertSee('vigente');

    $dia = diaDeAna($this, '2026-10-19');
    expect($dia->estado)->toBe(EstadoDia::Presente)
        ->and($dia->horario)->toBe('08:00–13:00 (horario especial)')
        ->and(diaDeAna($this, '2026-09-30'))->toBeNull(); // otro mes: no cambia nada
});

test('RRHH sin el permiso de horarios lo ve pero no puede cargarlo', function () {
    RrhhHorarioEspecial::create([
        'empleado_id' => $this->ana, 'dias' => [1], 'desde_maniana' => '08:00', 'hasta_maniana' => '13:00',
        'vigente_desde' => '2026-10-01', 'motivo' => 'Estudio', 'user_id' => $this->jefa->id,
    ]);

    Livewire::actingAs($this->rrhh)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->set('pestania', 'horario')
        ->assertSee('Estudio')
        ->assertDontSee('Nuevo horario especial')
        ->set('nuevo.motivo', 'intento')
        ->call('agregarHorario')
        ->assertForbidden();
});

test('valida horas, motivo y que no se superponga con otro especial', function () {
    $c = Livewire::actingAs($this->jefa)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->set('pestania', 'horario')
        ->set('nuevo.desde_maniana', '13:00')
        ->set('nuevo.hasta_maniana', '08:00')
        ->call('agregarHorario')
        ->assertHasErrors(['nuevo.hasta_maniana', 'nuevo.motivo']);

    $c->set('nuevo.desde_maniana', '08:00')->set('nuevo.hasta_maniana', '13:00')->set('nuevo.motivo', 'Primero')
        ->call('agregarHorario')->assertHasNoErrors();
    $c->set('nuevo.dias', [5])->set('nuevo.desde_maniana', '09:00')->set('nuevo.hasta_maniana', '13:00')->set('nuevo.motivo', 'Segundo')
        ->call('agregarHorario')->assertHasErrors('nuevo.dias');

    expect(RrhhHorarioEspecial::count())->toBe(1);
});

test('finalizar un horario que ya empezó lo hace vencer ayer', function () {
    $h = RrhhHorarioEspecial::create([
        'empleado_id' => $this->ana, 'dias' => [1, 2, 3, 4, 5], 'desde_maniana' => '08:00', 'hasta_maniana' => '13:00',
        'vigente_desde' => '2026-10-01', 'motivo' => 'Estudio', 'user_id' => $this->jefa->id,
    ]);

    Livewire::actingAs($this->jefa)
        ->test(FichaEmpleado::class, ['empleado' => $this->ana])
        ->set('pestania', 'horario')
        ->call('finalizarHorario', $h->id);

    expect($h->fresh()->vigente_hasta->toDateString())->toBe('2026-10-19');
});
