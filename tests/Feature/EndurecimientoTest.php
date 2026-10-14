<?php

use App\Livewire\Admin\PanelAdmin;
use App\Models\Audit;
use App\Models\Permiso;
use App\Models\User;
use App\Support\Csv;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

test('las planillas neutralizan lo que Excel tomaría como fórmula', function () {
    expect(Csv::celda('=1+1'))->toBe("'=1+1")
        ->and(Csv::celda('+54 381'))->toBe("'+54 381")
        ->and(Csv::celda("\tcmd"))->toBe("'\tcmd")
        ->and(Csv::celda("\rcmd"))->toBe("'\rcmd")
        ->and(Csv::celda('Perez'))->toBe('Perez')
        ->and(Csv::celda(12))->toBe(12);
});

test('la auditoría no guarda claves ni campos ocultos del modelo', function () {
    $modelo = new class extends Model
    {
        use Auditable;

        protected $hidden = ['token_api'];
    };
    $modelo->id = 7;

    $modelo->recordAudit('updated', ['password' => 'hash-viejo', 'token_api' => 'abc', 'nombre' => 'A'], ['remember_token' => 'zzz', 'nombre' => 'B']);

    $registro = Audit::latest('id')->first();
    expect(json_encode($registro->toArray()))->not->toContain('hash-viejo')->not->toContain('abc')->not->toContain('zzz')
        ->and($registro->new_values)->toBe(['nombre' => 'B']);
});

test('el panel Admin oculta claves aunque ya estén guardadas en un registro viejo', function () {
    $admin = User::factory()->create();
    Permiso::create(['user_id' => $admin->id, 'nombre' => 'admin_ver']);
    $viejo = Audit::create(['auditable_type' => User::class, 'auditable_id' => 1, 'event' => 'updated', 'old_values' => ['password' => 'HASH-EXPUESTO', 'nombre' => 'X'], 'new_values' => [], 'ip_address' => '10.0.0.1']);

    Livewire::actingAs($admin)->test(PanelAdmin::class)
        ->call('irA', 'auditoria')
        ->call('alternar', $viejo->id)
        ->assertDontSee('HASH-EXPUESTO');
});
