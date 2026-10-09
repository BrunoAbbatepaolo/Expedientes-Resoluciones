<?php

use App\Enums\EstadoPedido;
use App\Livewire\Rrhh\Licencias;
use App\Livewire\Rrhh\MiEquipo;
use App\Livewire\Rrhh\MiRrhh;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\RrhhPedidoLicencia;
use App\Models\RrhhTipoPedible;
use App\Models\User;
use App\Services\Rrhh\Organigrama;
use App\Services\Rrhh\PedidosLicencia;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00'));
    config(['sirex.rrhh_pedidos' => true]);
    Storage::fake('local');
    MitivFalso::crear();
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    app(Organigrama::class)->asignarPadre($this->dpto, $this->area);

    $ana = MitivFalso::empleadoRrhh(['legajo' => 111, 'apellido' => 'GOMEZ', 'oficina_id' => $this->dpto, 'fecha_ingreso' => '2015-01-01']);
    $this->empleada = User::factory()->create(['legajo' => '111', 'nombre' => 'Ana', 'apellido' => 'Gomez']);
    MitivFalso::empleadoRrhh(['legajo' => 222, 'oficina_id' => $this->area]);
    $this->jefe = User::factory()->create(['legajo' => '222']);
    RrhhJefatura::create(['user_id' => $this->jefe->id, 'mitiv_oficina_id' => $this->area]);

    $this->rrhh = User::factory()->create();
    foreach (['rrhh_ver', 'rrhh_confirmar_pedidos'] as $p) {
        Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => $p]);
    }

    RrhhTipoPedible::create(['tipo_ausencia_id' => 96, 'nombre' => 'VAC ANUALES']);
    MitivFalso::vacaciones($ana, '2026-02-02', '2026-02-06', 2025);
    $this->certificado = MitivFalso::tipoAusencia('LIC.P/ENFERMEDAD');
    RrhhTipoPedible::create(['tipo_ausencia_id' => $this->certificado, 'nombre' => 'LIC.P/ENFERMEDAD', 'requiere_adjunto' => true, 'permite_retroactivo' => true]);
});

test('el empleado pide vacaciones, ve la vista previa y su pedido queda esperando al jefe', function () {
    Livewire::actingAs($this->empleada)
        ->test(MiRrhh::class)
        ->assertSee('Mis pedidos de licencia')
        ->set('pidiendo', true)
        ->set('pedido.tipo_ausencia_id', '96')
        ->set('pedido.desde', '2026-11-02')
        ->set('pedido.hasta', '2026-11-06')
        ->assertSeeHtml('<strong>5</strong> días hábiles')
        ->assertSeeHtml('<strong class="">20</strong> de vacaciones')
        ->call('pedirLicencia')
        ->assertHasNoErrors()
        ->assertSee('Esperando al jefe');

    expect(RrhhPedidoLicencia::count())->toBe(1);
});

test('muestra el error de validación y no crea el pedido', function () {
    Livewire::actingAs($this->empleada)
        ->test(MiRrhh::class)
        ->set('pidiendo', true)
        ->set('pedido.tipo_ausencia_id', '96')
        ->set('pedido.desde', '2026-10-19')
        ->set('pedido.hasta', '2026-10-19')
        ->call('pedirLicencia')
        ->assertHasErrors('pedido.desde')
        ->assertSee('No se puede pedir con fecha pasada');

    expect(RrhhPedidoLicencia::count())->toBe(0);
});

test('un tipo con certificado exige adjuntarlo y el adjunto lo ven el jefe y RRHH pero no otros', function () {
    // Con el reloj congelado la subida de archivos de Livewire detecta mal el tipo (efecto del
    // test, no de la app): se usa el reloj real y el próximo lunes como fecha.
    $this->travelBack();
    $lunes = CarbonImmutable::parse('next monday')->toDateString();

    $c = Livewire::actingAs($this->empleada)
        ->test(MiRrhh::class)
        ->set('pidiendo', true)
        ->set('pedido.tipo_ausencia_id', (string) $this->certificado)
        ->set('pedido.desde', $lunes)
        ->set('pedido.hasta', $lunes)
        ->call('pedirLicencia')
        ->assertHasErrors('adjunto');

    // PDF mínimo real: la validación mira el contenido, no la extensión.
    $pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 0/Kids[]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
    $c->set('adjunto', UploadedFile::fake()->createWithContent('certificado.pdf', $pdf))
        ->call('pedirLicencia')
        ->assertHasNoErrors();

    $pedido = RrhhPedidoLicencia::firstOrFail();
    Storage::disk('local')->assertExists($pedido->adjunto_ruta);

    $this->actingAs($this->jefe)->get(route('rrhh.pedido.adjunto', $pedido))->assertOk();
    $this->actingAs($this->rrhh)->get(route('rrhh.pedido.adjunto', $pedido))->assertOk();
    $this->actingAs(User::factory()->create(['legajo' => '999']))->get(route('rrhh.pedido.adjunto', $pedido))->assertForbidden();
});

test('circuito por pantallas: aprueba el jefe, confirma RRHH y el empleado ve cada paso', function () {
    Livewire::actingAs($this->empleada)->test(MiRrhh::class)
        ->set('pidiendo', true)->set('pedido.tipo_ausencia_id', '96')->set('pedido.desde', '2026-11-02')->set('pedido.hasta', '2026-11-04')
        ->call('pedirLicencia');
    $id = RrhhPedidoLicencia::value('id');

    Livewire::actingAs($this->jefe)->test(MiEquipo::class, ['vista' => 'pedidos'])
        ->assertSee('Gomez')
        ->assertSee('3 hábiles')
        ->call('rechazarPedido', $id) // sin motivo: no se puede
        ->assertSee('Aprobar')
        ->call('aprobarPedido', $id)
        ->assertSee('No hay pedidos esperando tu aprobación');

    Livewire::actingAs($this->rrhh)->test(Licencias::class, ['vista' => 'pedidos'])
        ->assertSee('Por confirmar')
        ->assertSee('VAC ANUALES')
        ->call('confirmarPedido', $id);

    Livewire::actingAs($this->empleada)->test(MiRrhh::class)
        ->assertSee('Confirmado')
        ->assertSee('Confirmado por RRHH');
});

test('RRHH rechaza con motivo y el empleado lo ve', function () {
    Livewire::actingAs($this->empleada)->test(MiRrhh::class)
        ->set('pidiendo', true)->set('pedido.tipo_ausencia_id', '96')->set('pedido.desde', '2026-11-09')->set('pedido.hasta', '2026-11-10')
        ->call('pedirLicencia');
    $id = RrhhPedidoLicencia::value('id');
    RrhhPedidoLicencia::whereKey($id)->update(['estado' => EstadoPedido::AprobadoJefe->value]);

    Livewire::actingAs($this->rrhh)->test(Licencias::class, ['vista' => 'pedidos'])
        ->set("motivos.$id", 'Falta la resolución')
        ->call('rechazarPedido', $id);

    Livewire::actingAs($this->empleada)->test(MiRrhh::class)
        ->assertSee('Rechazado por RRHH')
        ->assertSee('Motivo de RRHH: Falta la resolución');
});

test('quien confirma administra los tipos que se pueden pedir', function () {
    Livewire::actingAs($this->rrhh)->test(Licencias::class, ['vista' => 'tipos'])
        ->assertSee('VAC ANUALES')
        ->set('tipoPedibleId', $this->certificado)
        ->set('tipoAdjunto', true)
        ->call('guardarTipo')
        ->assertSee('pide certificado')
        ->call('quitarTipo', RrhhTipoPedible::where('tipo_ausencia_id', 96)->value('id'));

    expect(RrhhTipoPedible::where('tipo_ausencia_id', 96)->exists())->toBeFalse();
});

test('RRHH sin el permiso de confirmar no ve las vistas de pedidos', function () {
    $soloVer = User::factory()->create();
    Permiso::create(['user_id' => $soloVer->id, 'nombre' => 'rrhh_ver']);

    Livewire::actingAs($soloVer)->test(Licencias::class, ['vista' => 'pedidos'])
        ->assertSet('vista', 'listado')
        ->assertDontSee('Tipos que se pueden pedir');
});

test('el menú avisa cuántos pedidos esperan al jefe y a RRHH', function () {
    Livewire::actingAs($this->empleada)->test(MiRrhh::class)
        ->set('pidiendo', true)->set('pedido.tipo_ausencia_id', '96')->set('pedido.desde', '2026-11-02')->set('pedido.hasta', '2026-11-02')
        ->call('pedirLicencia');

    $this->actingAs($this->jefe)->get(route('dashboard'))->assertSee('class="sirex-contador"', false);
    app()->forgetScopedInstances();
    expect(PedidosLicencia::contadores($this->jefe))->toBe(['aprobar' => 1, 'confirmar' => 0]);
});

test('con el interruptor apagado no hay formulario ni pantallas de pedidos', function () {
    config(['sirex.rrhh_pedidos' => false]);

    Livewire::actingAs($this->empleada)->test(MiRrhh::class)->assertDontSee('Mis pedidos de licencia');
    Livewire::actingAs($this->jefe)->test(MiEquipo::class, ['vista' => 'pedidos'])->assertSet('vista', 'hoy');
    $this->actingAs($this->rrhh)->get(route('rrhh.pedido.adjunto', 1))->assertNotFound();
});
