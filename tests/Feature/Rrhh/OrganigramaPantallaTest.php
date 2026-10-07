<?php

use App\Livewire\Rrhh\Organigrama;
use App\Models\Permiso;
use App\Models\RrhhJefatura;
use App\Models\RrhhOficinaPadre;
use App\Models\User;
use App\Services\Rrhh\Organigrama as OrganigramaRrhh;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    $this->admin = User::factory()->create(['nombre' => 'Admin', 'apellido' => 'Sirex']);
    Permiso::create(['user_id' => $this->admin->id, 'nombre' => 'lista_usuario_editar']);
    $this->rrhh = User::factory()->create();
    Permiso::create(['user_id' => $this->rrhh->id, 'nombre' => 'rrhh_ver']);
    $this->jefa = User::factory()->create(['nombre' => 'Marta', 'apellido' => 'Lopez']);
});

test('RRHH ve el organigrama pero no puede editar', function () {
    Livewire::actingAs($this->rrhh)
        ->test(Organigrama::class)
        ->assertSee('DPTO. DESPACHO')
        ->assertDontSee('Editar')
        ->call('editar', $this->dpto)
        ->assertForbidden();

    $this->actingAs(User::factory()->create())->get(route('rrhh.organigrama'))->assertForbidden();
});

test('el admin asigna dependencia y jefe, y rechaza ciclos', function () {
    $c = Livewire::actingAs($this->admin)
        ->test(Organigrama::class)
        ->call('editar', $this->dpto)
        ->set('padreId', $this->area)
        ->call('guardarPadre')
        ->assertHasNoErrors()
        ->set('nuevoJefeId', $this->jefa->id)
        ->call('agregarJefe')
        ->assertSee('Lopez');

    expect(app(OrganigramaRrhh::class)->padres()[$this->dpto])->toBe($this->area)
        ->and(RrhhJefatura::where('user_id', $this->jefa->id)->value('mitiv_oficina_id'))->toBe($this->dpto);

    $c->call('editar', $this->area)
        ->set('padreId', $this->dpto)
        ->call('guardarPadre')
        ->assertHasErrors('padreId');

    $c->call('quitarJefe', RrhhJefatura::first()->id);
    expect(RrhhJefatura::count())->toBe(0);
});

test('el comando carga por id solo si el nombre coincide y no pisa lo cargado', function () {
    $db = DB::connection('pgsql_mitiv');
    $db->table('oficinas')->delete();
    $db->table('oficinas')->insert([
        ['id' => 68, 'nombre' => 'INTERVENCION'],
        ['id' => 1, 'nombre' => 'SUB INTERVENCION'],
        ['id' => 8, 'nombre' => 'SECRETARIA GENERAL'],
        ['id' => 80, 'nombre' => 'DPTO. RECURSOS HUMANOS'],
        ['id' => 5, 'nombre' => 'OTRA COSA'],
        ['id' => 9, 'nombre' => 'DPTO. DESPACHO'],
    ]);
    RrhhOficinaPadre::create(['mitiv_oficina_id' => 9, 'padre_mitiv_oficina_id' => 68]);

    $this->artisan('rrhh:cargar-organigrama')->assertSuccessful();

    $padres = app(OrganigramaRrhh::class)->padres();
    expect($padres[1])->toBe(68)
        ->and($padres[8])->toBe(1)
        ->and($padres[80])->toBe(8)
        ->and($padres)->not->toHaveKey(5)
        ->and($padres[9])->toBe(68);
});
