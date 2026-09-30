<?php

use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function usuarioDeOficina(Oficina $oficina, array $permisos): User
{
    $user = User::factory()->create();
    Permiso::setOficinaAsignada($user->id, $oficina->id);

    foreach ($permisos as $permiso) {
        Permiso::create(['user_id' => $user->id, 'nombre' => $permiso]);
    }

    return $user;
}

function resolucionConArchivo(Oficina $oficina): ResolucionArchivo
{
    Storage::disk('local')->put('resoluciones/r1.pdf', '%PDF-1.4 prueba');
    $resolucion = Resolucion::factory()->create(['oficina_id' => $oficina->id]);

    return ResolucionArchivo::create([
        'resolucion_id' => $resolucion->id,
        'nombre_original' => 'dni.pdf',
        'nombre_archivo' => 'r1.pdf',
        'ruta' => 'resoluciones/r1.pdf',
        'tipo' => 'application/pdf',
        'tamano' => 15,
    ]);
}

beforeEach(fn () => Storage::fake('local'));

test('un usuario de la misma oficina puede ver el PDF', function () {
    $oficina = Oficina::factory()->create();
    $archivo = resolucionConArchivo($oficina);

    $this->actingAs(usuarioDeOficina($oficina, ['resolucion_ver']))
        ->get(route('resoluciones.archivo', $archivo))
        ->assertOk();
});

test('un usuario de otra oficina no puede ver el PDF', function () {
    $archivo = resolucionConArchivo(Oficina::factory()->create());

    $this->actingAs(usuarioDeOficina(Oficina::factory()->create(), ['resolucion_ver']))
        ->get(route('resoluciones.archivo', $archivo))
        ->assertForbidden();
});

test('sin permiso resolucion_ver no se puede ver el PDF', function () {
    $oficina = Oficina::factory()->create();
    $archivo = resolucionConArchivo($oficina);

    $this->actingAs(usuarioDeOficina($oficina, []))
        ->get(route('resoluciones.archivo', $archivo))
        ->assertForbidden();
});

test('la vista previa solo sirve archivos de la sesión propia', function () {
    $oficina = Oficina::factory()->create();
    Storage::disk('local')->put('temp/t1.pdf', '%PDF-1.4');

    $this->actingAs(usuarioDeOficina($oficina, ['resolucion_editar']))
        ->withSession(['archivos_pdf_resolucion' => ['temp/t1.pdf']])
        ->get(route('resoluciones.descargarPdf', 0))
        ->assertOk();

    $this->actingAs(usuarioDeOficina($oficina, ['resolucion_editar']))
        ->withSession(['archivos_pdf_resolucion' => []])
        ->get(route('resoluciones.descargarPdf', 0))
        ->assertNotFound();
});
