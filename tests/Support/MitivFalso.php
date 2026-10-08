<?php

namespace Tests\Support;

use App\Enums\NivelDatoRrhh;
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
            // Columnas del legajo usadas por RRHH (ver NivelDatoRrhh).
            foreach (NivelDatoRrhh::Sensible->columnas() as $col) {
                if (! in_array($col, ['id', 'legajo', 'apellido', 'nombre', 'email', 'oficina_id', 'situacion_laboral_id', 'habilitado', 'espasivo'], true)) {
                    $t->string($col)->nullable();
                }
            }
            $t->string('clave_fiscal')->nullable();
            $t->string('clave_seguridad_social')->nullable();
            $t->text('roles')->nullable();
            foreach (['nacional', 'provincial', 'municipal'] as $ambito) {
                foreach (['anio', 'mes', 'dia'] as $parte) {
                    $t->integer("{$parte}_reconocimiento_{$ambito}")->nullable();
                }
            }
        });
        self::crearRrhh();
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

    /** Tablas del módulo RRHH (columnas reales relevadas el 2026-10-07). */
    private static function crearRrhh(): void
    {
        $s = Schema::connection('pgsql_mitiv');

        $s->create('oficinas', function (Blueprint $t) {
            $t->id();
            $t->integer('jerarquia_id')->default(1);
            $t->string('nombre');
            $t->integer('codigo')->nullable();
            $t->boolean('habilitado')->default(true);
        });
        foreach (['categorias', 'parentescos'] as $tabla) {
            $s->create($tabla, function (Blueprint $t) {
                $t->id();
                $t->string('nombre');
                $t->boolean('habilitado')->default(true);
            });
        }
        $s->create('tipo_ausencias', function (Blueprint $t) {
            $t->id();
            $t->string('descripcion');
            $t->integer('codigo')->nullable();
            $t->boolean('habilitado')->default(true);
            $t->boolean('afecta_vacaciones')->default(false);
        });
        $s->create('ausencias', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->date('desde');
            $t->date('hasta');
            $t->integer('tipo_ausencias_id');
            $t->integer('anio')->nullable();
            $t->string('observacion')->nullable();
            $t->integer('user_id')->nullable();
        });
        $s->create('tipo_inasistencias', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->boolean('habilitado')->default(true);
            $t->integer('codigo')->nullable();
            $t->boolean('afecta_presentismo')->default(true);
        });
        $s->create('inasistencias', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->integer('tipo_inasistencia_id');
            $t->date('fecha');
            $t->string('observacion')->nullable();
            $t->boolean('habilitado')->nullable()->default(true);
            $t->integer('anio')->nullable();
        });
        $s->create('registros_reloj', function (Blueprint $t) {
            $t->id();
            $t->integer('card_id');
            $t->dateTime('fecha_hora');
            $t->boolean('procesado')->default(false);
            $t->string('nombre_reloj')->nullable();
            $t->string('origen')->nullable();
            $t->timestamps();
        });
        $s->create('horarios', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->time('desde_maniana')->nullable();
            $t->time('hasta_maniana')->nullable();
            $t->integer('tolerancia_maniana')->nullable();
            $t->time('desde_tarde')->nullable();
            $t->time('hasta_tarde')->nullable();
            $t->integer('tolerancia_tarde')->nullable();
            $t->integer('dia');
            $t->boolean('habilitado')->nullable()->default(true);
        });
        $s->create('feriados', function (Blueprint $t) {
            $t->id();
            $t->dateTime('desde');
            $t->dateTime('hasta');
            $t->integer('anio')->nullable();
            $t->string('tipo')->nullable();
            $t->integer('tipo_feriado_id')->nullable();
        });
        $s->create('grupo_familiares', function (Blueprint $t) {
            $t->id();
            foreach (NivelDatoRrhh::Sensible->columnasFamiliar() as $col) {
                if ($col !== 'id') {
                    $t->string($col)->nullable();
                }
            }
        });
        $s->create('fojas_servicio', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->date('fecha')->nullable();
            $t->string('resolucion_decreto')->nullable();
            $t->string('nro_res_dec')->nullable();
            $t->string('observacion')->nullable();
            $t->string('sueldo')->nullable();
        });
        $s->create('empleado_movimientos', function (Blueprint $t) {
            $t->id();
            $t->integer('empleado_id');
            $t->date('fecha')->nullable();
            $t->string('resolucion')->nullable();
            $t->string('motivo')->nullable();
            $t->string('tipo')->nullable();
            $t->boolean('habilitado')->default(true);
        });
        $s->create('empleado_archivos', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->string('descripcion')->nullable();
            $t->binary('contenido')->nullable();
            $t->integer('tamanio')->nullable();
            $t->integer('empleado_id');
            $t->boolean('habilitado')->default(true);
            $t->string('mimetype')->nullable();
            $t->timestamps();
        });
        $s->create('operaciones_reloj', function (Blueprint $t) {
            $t->id();
            $t->string('operacion');
            $t->string('estado')->nullable();
            $t->timestamps();
        });
    }

    public static function oficinaRrhh(string $nombre): int
    {
        return DB::connection('pgsql_mitiv')->table('oficinas')->insertGetId(['nombre' => $nombre]);
    }

    /** Empleado activo con card_id = legajo (para cruzar fichadas). */
    public static function empleadoRrhh(array $datos = []): int
    {
        static $legajo = 5000;
        $legajo++;

        return self::empleado($datos + [
            'legajo' => $legajo,
            'card_id' => $legajo,
            'numero_documento' => '30'.$legajo,
            'enfermedad' => 'dato de salud',
            'clave_fiscal' => 'CLAVE-SECRETA',
        ]);
    }

    /** Mismo horario de lunes a viernes (o los días indicados). */
    public static function horarioSemana(int $empleadoId, ?string $desde = '08:00', ?string $hasta = '14:00', int $tol = 0, array $dias = [1, 2, 3, 4, 5], ?string $desdeTarde = null, ?string $hastaTarde = null): void
    {
        foreach ($dias as $dia) {
            DB::connection('pgsql_mitiv')->table('horarios')->insert([
                'empleado_id' => $empleadoId, 'dia' => $dia, 'tolerancia_maniana' => $tol,
                'desde_maniana' => $desde ? $desde.':00' : null, 'hasta_maniana' => $hasta ? $hasta.':00' : null,
                'desde_tarde' => $desdeTarde ? $desdeTarde.':00' : null, 'hasta_tarde' => $hastaTarde ? $hastaTarde.':00' : null,
            ]);
        }
    }

    public static function fichada(int $cardId, string $fechaHora, string $reloj = '192.168.7.197'): void
    {
        DB::connection('pgsql_mitiv')->table('registros_reloj')->insert([
            'card_id' => $cardId, 'fecha_hora' => $fechaHora, 'nombre_reloj' => $reloj,
            'origen' => 'SERVICIO_SINCRONIZADOR', 'created_at' => $fechaHora,
        ]);
    }

    public static function tipoAusencia(string $descripcion, bool $afectaVacaciones = false): int
    {
        $db = DB::connection('pgsql_mitiv');

        return $db->table('tipo_ausencias')->where('descripcion', $descripcion)->value('id')
            ?? $db->table('tipo_ausencias')->insertGetId(['descripcion' => $descripcion, 'afecta_vacaciones' => $afectaVacaciones]);
    }

    public static function ausencia(int $empleadoId, string $desde, string $hasta, string $tipo = 'LIC. ANUAL', ?string $observacion = null): int
    {
        return DB::connection('pgsql_mitiv')->table('ausencias')->insertGetId([
            'empleado_id' => $empleadoId, 'desde' => $desde, 'hasta' => $hasta,
            'tipo_ausencias_id' => self::tipoAusencia($tipo, $tipo === 'LIC. ANUAL'),
            'anio' => (int) substr($desde, 0, 4), 'observacion' => $observacion,
        ]);
    }

    /** Vacaciones "VAC ANUALES" (id 96, como en el sistema viejo) imputadas a un período. */
    public static function vacaciones(int $empleadoId, string $desde, string $hasta, int $periodo): void
    {
        $db = DB::connection('pgsql_mitiv');
        if (! $db->table('tipo_ausencias')->where('id', 96)->exists()) {
            $db->table('tipo_ausencias')->insert(['id' => 96, 'descripcion' => 'VAC ANUALES']);
        }
        $db->table('ausencias')->insert([
            'empleado_id' => $empleadoId, 'desde' => $desde, 'hasta' => $hasta, 'tipo_ausencias_id' => 96, 'anio' => $periodo,
        ]);
    }

    /** Tipos 82 (AUSENTE) y 7 (LLEGADA TARDE) con los mismos ids que el sistema viejo. */
    public static function inasistencia(int $empleadoId, string $fecha, int $tipoId = 82): void
    {
        $db = DB::connection('pgsql_mitiv');
        if (! $db->table('tipo_inasistencias')->where('id', $tipoId)->exists()) {
            $nombres = [82 => 'AUSENTE', 7 => 'LLEGADA TARDE', 33 => 'JUST.POR LLUVIA'];
            $db->table('tipo_inasistencias')->insert(['id' => $tipoId, 'nombre' => $nombres[$tipoId] ?? 'TIPO '.$tipoId]);
        }
        $db->table('inasistencias')->insert(['empleado_id' => $empleadoId, 'fecha' => $fecha, 'tipo_inasistencia_id' => $tipoId]);
    }

    public static function feriado(string $fecha, string $tipo = 'Feriado nacional'): void
    {
        DB::connection('pgsql_mitiv')->table('feriados')->insert([
            'desde' => $fecha.' 00:00:00', 'hasta' => $fecha.' 00:00:00', 'anio' => (int) substr($fecha, 0, 4), 'tipo' => $tipo,
        ]);
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
