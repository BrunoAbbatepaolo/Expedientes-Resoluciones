<?php

namespace App\Services\Rrhh\Contracts;

use App\Models\Mitiv\Empleado;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\DiaAsistencia;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Asistencia calculada a partir de horarios, fichadas, licencias, feriados e inasistencias. */
interface Asistencias
{
    /** Máximo de días por consulta de fichadas crudas. */
    public const MAX_DIAS_FICHADAS = 31;

    /** @return list<DiaAsistencia> un elemento por día del mes */
    public function mes(Alcance $alcance, int $empleadoId, int $anio, int $mes): array;

    /**
     * Estado del día de los empleados visibles (o de una oficina visible).
     *
     * @return Collection<int, array{empleado: Empleado, dia: DiaAsistencia}>
     */
    public function dia(Alcance $alcance, CarbonImmutable $fecha, ?int $oficinaId = null): Collection;

    /**
     * Grilla empleados × días de una oficina.
     *
     * @return array{dias: list<CarbonImmutable>, filas: list<array{empleado: Empleado, dias: list<DiaAsistencia>}>}
     */
    public function grilla(Alcance $alcance, int $anio, int $mes, int $oficinaId): array;

    /**
     * Fichadas crudas (solo RRHH). Filtros: empleado_id o legajo, reloj, desde, hasta (Y-m-d);
     * el rango se recorta a MAX_DIAS_FICHADAS.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function fichadas(Alcance $alcance, array $filtros, int $porPagina = 50): LengthAwarePaginator;

    /** @return list<string> relojes que registraron fichadas en los últimos 30 días */
    public function relojes(): array;
}
