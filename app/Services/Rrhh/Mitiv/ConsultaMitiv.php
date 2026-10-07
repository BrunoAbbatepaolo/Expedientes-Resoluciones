<?php

namespace App\Services\Rrhh\Mitiv;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Empleado;
use App\Services\Rrhh\Alcance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/** Utilidades comunes de los servicios RRHH que leen mitivdb. */
trait ConsultaMitiv
{
    /** @var array<int, int> empleado => oficina */
    private array $oficinaDeEmpleado = [];

    /** Nivel con que el alcance ve a un empleado; 404 si no existe, 403 si no le corresponde. */
    protected function nivelDe(Alcance $alcance, int $empleadoId): NivelDatoRrhh
    {
        if (! isset($this->oficinaDeEmpleado[$empleadoId])) {
            $oficina = Empleado::whereKey($empleadoId)->value('oficina_id');
            if ($oficina === null && ! Empleado::whereKey($empleadoId)->exists()) {
                throw (new ModelNotFoundException)->setModel(Empleado::class, [$empleadoId]);
            }
            $this->oficinaDeEmpleado[$empleadoId] = (int) $oficina;
        }

        return $alcance->nivelPara($empleadoId, $this->oficinaDeEmpleado[$empleadoId])
            ?? throw new AuthorizationException('No tenés acceso a los datos de este empleado.');
    }

    protected function soloRrhh(Alcance $alcance): void
    {
        if (! $alcance->rrhh) {
            throw new AuthorizationException('Necesitás el permiso de RRHH.');
        }
    }

    /** ilike en PostgreSQL (sin distinguir mayúsculas), like en los tests con SQLite. */
    protected function like(): string
    {
        return DB::connection('pgsql_mitiv')->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    protected static function escaparLike(string $texto): string
    {
        return addcslashes($texto, '%_\\');
    }
}
