<?php

use App\Livewire\Oficinas;
use App\Livewire\TableroIpv;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Seguimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->legalIpv = MitivFalso::oficina('DIRECCION AREA LEGAL');
    $this->oficina = Oficina::factory()->create(['nombre' => 'Legal']);
    $this->user = User::factory()->create();
    Permiso::setOficinaAsignada($this->user->id, $this->oficina->id);
    foreach (['expediente_ver', 'lista_usuario_ver', 'lista_usuario_editar'] as $p) {
        Permiso::create(['user_id' => $this->user->id, 'nombre' => $p]);
    }
});

test('sin vínculo el tablero invita a vincular la oficina', function () {
    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(TableroIpv::class)
        ->assertSee('todavía no está vinculada')
        ->assertSee('Vincularla en Oficinas');
});

test('con vínculo muestra métricas y demorados', function () {
    $this->oficina->update(['mitiv_oficina_id' => $this->legalIpv]);
    MitivFalso::expediente('1/440-2026', 'DEMORADO SA', [[$this->legalIpv, now()->subDays(50)->format('Y-m-d H:i')]]);
    MitivFalso::expediente('2/440-2026', 'RECIENTE SA', [[$this->legalIpv, now()->subDays(1)->format('Y-m-d H:i')]]);

    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(TableroIpv::class)
        ->assertSee('DIRECCION AREA LEGAL')
        ->assertSee('activos con más de 30 días sin pase')
        ->assertSee('1/440-2026')
        ->assertDontSee('2/440-2026');
});

test('la oficina IPV se deduce del legajo si no hay vínculo', function () {
    $this->user->forceFill(['legajo' => '777'])->save();
    $empleado = MitivFalso::empleado(['legajo' => 777]);
    DB::connection('pgsql_mitiv')->table('empleado_expediente_oficinas')->insert(['empleado_id' => $empleado, 'expediente_oficina_id' => $this->legalIpv]);

    expect(app(\App\Services\ExpedientesIpv::class)->oficinaIpvDe($this->user))->toBe($this->legalIpv);
});

test('los seguimientos avisan cuando el expediente se movió', function () {
    MitivFalso::expediente('9/440-2026', 'A', [[$this->legalIpv, '2026-09-20 10:00']]);
    $s = Seguimiento::create(['user_id' => $this->user->id, 'numero_exp' => '9/440-2026', 'ultimo_pase_visto' => '2026-09-01 10:00']);

    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(TableroIpv::class)
        ->assertSee('Se movió')
        ->call('marcarVisto', $s->id)
        ->assertDontSee('Se movió');
});

test('vincular una oficina con el IPV y ver su personal', function () {
    $empleado = MitivFalso::empleado(['apellido' => 'ROMERO', 'nombre' => 'LUIS']);
    $pasivo = MitivFalso::empleado(['apellido' => 'JUBILADO', 'espasivo' => true]);
    foreach ([$empleado, $pasivo] as $e) {
        DB::connection('pgsql_mitiv')->table('empleado_expediente_oficinas')->insert(['empleado_id' => $e, 'expediente_oficina_id' => $this->legalIpv]);
    }

    Livewire::actingAs($this->user)
        ->test(Oficinas::class)
        ->call('vincular', $this->oficina->id)
        ->set('buscarIpv', 'legal')
        ->assertSee('DIRECCION AREA LEGAL')
        ->call('guardarVinculo', $this->legalIpv)
        ->call('alternarPersonal', $this->oficina->id)
        ->assertSee('ROMERO, LUIS')
        ->assertDontSee('JUBILADO');

    expect($this->oficina->fresh()->mitiv_oficina_id)->toBe($this->legalIpv);
});

test('vincular oficinas requiere permiso de edición de usuarios', function () {
    $sinPermiso = User::factory()->create();

    Livewire::actingAs($sinPermiso)
        ->test(Oficinas::class)
        ->call('vincular', $this->oficina->id)
        ->assertForbidden();
});
