<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema mínimo de mitivdb en sqlite en memoria (ver phpunit.xml), con las columnas
 * que usa SiRex. Nunca se toca la base real del sistema viejo desde los tests.
 */
class MitivFalso
{
    public static function crear(): void
    {
        $s = Schema::connection('pgsql_mitiv');

        $s->create('tipo_causantes', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
        });
        $s->create('causantes', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->integer('tipo_causante_id')->default(1);
            $t->boolean('habilitado')->default(true);
        });
        $s->create('tipo_asuntos', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
        });
        $s->create('expedientes', function (Blueprint $t) {
            $t->id();
            $t->string('numero')->unique();
            $t->date('fecha')->nullable();
            $t->time('hora')->nullable();
            $t->integer('folio')->nullable();
            $t->integer('tomo')->nullable();
            $t->text('asunto')->nullable();
            $t->string('resolucion')->nullable();
            $t->text('observacion')->nullable();
            $t->integer('causante_id');
            $t->integer('asunto_id')->nullable();
            $t->boolean('habilitado')->default(true);
        });
        $s->create('expediente_oficinas', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->boolean('habilitado')->default(true);
        });
        $s->create('situacion_laborales', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
        });
        $s->create('empleados', function (Blueprint $t) {
            $t->id();
            $t->integer('legajo');
            $t->string('apellido');
            $t->string('nombre');
            $t->string('email')->nullable();
            $t->integer('oficina_id')->default(1);
            $t->integer('situacion_laboral_id')->default(1);
            $t->boolean('habilitado')->default(true);
            $t->boolean('espasivo')->nullable();
            // Columna sensible de la tabla real: sirve para verificar que nunca se lee.
            $t->string('password')->nullable();
        });
        $s->create('empleado_expediente_oficinas', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->integer('expediente_oficina_id');
            $t->boolean('habilitado')->default(true);
        });
        $s->create('expediente_pases', function (Blueprint $t) {
            $t->id();
            $t->integer('expediente_id');
            $t->integer('oficina_id');
            $t->integer('empleado_id')->nullable();
            $t->dateTime('fecha_hora')->nullable();
            $t->integer('folio')->nullable();
            $t->integer('folio_actuales')->nullable();
            $t->text('observacion')->nullable();
            $t->boolean('habilitado')->default(true);
        });
        $s->create('expediente_relacionados', function (Blueprint $t) {
            $t->id();
            $t->integer('expediente_principal_id');
            $t->integer('expediente_secundario_id');
            $t->string('resolucion')->nullable();
            $t->boolean('habilitado')->default(true);
        });
    }

    public static function oficina(string $nombre): int
    {
        return DB::connection('pgsql_mitiv')->table('expediente_oficinas')->insertGetId(['nombre' => $nombre]);
    }

    public static function empleado(array $datos = []): int
    {
        static $legajo = 1000;

        return DB::connection('pgsql_mitiv')->table('empleados')->insertGetId($datos + [
            'legajo' => ++$legajo,
            'apellido' => 'PEREZ',
            'nombre' => 'JUAN',
            'email' => "empleado{$legajo}@ipv.test",
            'password' => 'secreto',
        ]);
    }

    /**
     * Crea un expediente con sus pases. $pases = [[oficinaId, 'Y-m-d H:i'], ...].
     */
    public static function expediente(string $numero, string $causante = 'GARCIA, ANA', array $pases = [], array $datos = []): int
    {
        $db = DB::connection('pgsql_mitiv');
        $causanteId = $db->table('causantes')->insertGetId(['nombre' => $causante]);

        $id = $db->table('expedientes')->insertGetId($datos + [
            'numero' => $numero,
            'fecha' => '2026-09-01',
            'folio' => 3,
            'asunto' => 'SOLICITA ADJUDICACION',
            'causante_id' => $causanteId,
        ]);

        foreach ($pases as [$oficinaId, $fechaHora]) {
            $db->table('expediente_pases')->insert([
                'expediente_id' => $id,
                'oficina_id' => $oficinaId,
                'fecha_hora' => $fechaHora,
                'folio' => 3,
            ]);
        }

        return $id;
    }
}
