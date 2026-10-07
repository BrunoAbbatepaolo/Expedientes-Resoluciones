<?php

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Ausencia;
use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\EmpleadoArchivo;
use Illuminate\Support\Facades\DB;
use Tests\Support\MitivFalso;

beforeEach(fn () => MitivFalso::crear());

test('el nivel básico no trae datos personales, sensibles ni claves', function () {
    $id = MitivFalso::empleadoRrhh();

    $fila = Empleado::conNivel(NivelDatoRrhh::Basico)->find($id)->getAttributes();

    expect($fila)->toHaveKeys(['legajo', 'oficina_id', 'card_id'])
        ->not->toHaveKeys(['numero_documento', 'enfermedad', 'clave_fiscal', 'password', 'clave_seguridad_social', 'roles']);
});

test('el nivel sensible trae salud pero nunca claves ni contraseña', function () {
    $id = MitivFalso::empleadoRrhh();

    $fila = Empleado::conNivel(NivelDatoRrhh::Sensible)->find($id)->getAttributes();

    expect($fila)->toHaveKeys(['numero_documento', 'enfermedad'])
        ->not->toHaveKeys(['clave_fiscal', 'password', 'clave_seguridad_social', 'roles']);
});

test('ninguna lista de columnas incluye campos prohibidos', function () {
    $prohibidos = ['password', 'clave_fiscal', 'clave_seguridad_social', 'roles'];

    foreach (NivelDatoRrhh::cases() as $nivel) {
        expect(array_intersect($prohibidos, [...$nivel->columnas(), ...$nivel->columnasFamiliar()]))->toBeEmpty();
    }
});

test('los modelos de RRHH del sistema viejo son de solo lectura', function () {
    $empleado = MitivFalso::empleadoRrhh();
    MitivFalso::ausencia($empleado, '2026-10-01', '2026-10-05');

    $ausencia = Ausencia::first();
    $ausencia->observacion = 'cambio';

    expect(fn () => $ausencia->save())->toThrow(LogicException::class);
});

test('los archivos del legajo no traen el contenido por defecto', function () {
    $empleado = MitivFalso::empleadoRrhh();
    DB::connection('pgsql_mitiv')->table('empleado_archivos')->insert(['nombre' => 'dni.pdf', 'empleado_id' => $empleado, 'contenido' => 'PDF']);

    expect(EmpleadoArchivo::first()->getAttributes())->not->toHaveKey('contenido');
});
