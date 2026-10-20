<?php

use App\Livewire\ConsultaIpv;
use App\Livewire\Detalles;
use App\Livewire\Expedientes;
use App\Livewire\FichaIpv;
use App\Livewire\SeguirExpediente;
use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Seguimiento;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->legal = MitivFalso::oficina('DIRECCION AREA LEGAL');

    $this->oficina = Oficina::factory()->create();
    $this->user = User::factory()->create();
    Permiso::setOficinaAsignada($this->user->id, $this->oficina->id);
    foreach (['expediente_ver', 'expediente_editar'] as $p) {
        Permiso::create(['user_id' => $this->user->id, 'nombre' => $p]);
    }
});

test('la consulta IPV lista y filtra expedientes', function () {
    MitivFalso::expediente('100/440-2025', 'GARCIA, ANA', [[$this->legal, now()->subDays(3)->format('Y-m-d H:i')]]);
    MitivFalso::expediente('200/440-2025', 'LOPEZ, PEDRO');

    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(ConsultaIpv::class)
        ->assertSee('100/440-2025')
        ->assertSee('DIRECCION AREA LEGAL')
        ->assertSee('3 días')
        ->set('texto', 'lopez')
        ->assertSee('200/440-2025')
        ->assertDontSee('100/440-2025');
});

test('la consulta IPV requiere permiso expediente_ver', function () {
    Livewire::withoutLazyLoading()->actingAs(User::factory()->create())
        ->test(ConsultaIpv::class)
        ->assertForbidden();
});

test('si el sistema IPV no responde se muestra un aviso', function () {
    Schema::connection('pgsql_mitiv')->drop('expedientes');

    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(ConsultaIpv::class)
        ->assertSee('No se pudo consultar el sistema de expedientes del IPV');
});

test('la ficha muestra ubicación actual y recorrido', function () {
    $id = MitivFalso::expediente('10/440-2026', 'GARCIA, ANA', [
        [MitivFalso::oficina('MESA DE ENTRADAS'), '2026-09-01 09:00'],
        [$this->legal, '2026-09-11 09:00'],
    ]);

    Livewire::withoutLazyLoading()->actingAs($this->user)
        ->test(FichaIpv::class, ['id' => $id])
        ->assertSee('10/440-2026')
        ->assertSee('MESA DE ENTRADAS')
        ->assertSee('Actual')
        ->assertSee('Registrar en mi oficina');
});

test('la página de impresión muestra carátula e historial', function () {
    $id = MitivFalso::expediente('10/440-2026', 'GARCIA, ANA', [[$this->legal, '2026-09-11 09:00']]);

    $this->actingAs($this->user)
        ->get(route('expedientes.ipv.imprimir', $id))
        ->assertOk()
        ->assertSee('Expediente N° 10/440-2026')
        ->assertSee('DIRECCION AREA LEGAL');
});

test('seguir y dejar de seguir un expediente', function () {
    MitivFalso::expediente('10/440-2026', 'A', [[$this->legal, '2026-09-11 09:00']]);

    $c = Livewire::actingAs($this->user)->test(SeguirExpediente::class, ['numero' => '10/440-2026'])
        ->call('alternar')
        ->assertSet('siguiendo', true);

    expect(Seguimiento::where('user_id', $this->user->id)->first()->ultimo_pase_visto->format('Y-m-d H:i'))->toBe('2026-09-11 09:00');

    $c->call('alternar')->assertSet('siguiendo', false);
    expect(Seguimiento::count())->toBe(0);
});

test('mi oficina muestra dónde está hoy cada expediente según el IPV', function () {
    MitivFalso::expediente('10/440-2026', 'A', [[$this->legal, now()->subDays(40)->format('Y-m-d H:i')]]);
    Expediente::factory()->create(['num_exp' => '10/440-2026', 'oficina_id' => $this->oficina->id]);

    Livewire::actingAs($this->user)
        ->test(Expedientes::class)
        ->assertSee('DIRECCION AREA LEGAL')
        ->assertSee('40 días')
        ->assertDontSee('Realizar pase');
});

test('el detalle muestra el historial de cambios y el acceso al IPV', function () {
    MitivFalso::expediente('77/440-2026', 'A');
    $this->actingAs($this->user);
    $exp = Expediente::factory()->create(['num_exp' => '77/440-2026', 'asunto' => 'VIEJO', 'oficina_id' => $this->oficina->id]);
    $exp->update(['asunto' => 'NUEVO']);

    Livewire::test(Detalles::class, ['id' => $exp->id])
        ->assertSee('Historial de cambios')
        ->assertSeeInOrder([$this->user->name, 'editó', 'VIEJO', 'NUEVO'])
        ->assertSee('Ver recorrido completo en el IPV');
});

test('los pases internos quedan desactivados del lado del servidor', function () {
    $exp = Expediente::factory()->create(['oficina_id' => $this->oficina->id]);

    Livewire::actingAs($this->user)
        ->test(Expedientes::class)
        ->call('abrirPase', $exp->id)
        ->assertNotFound();
});
