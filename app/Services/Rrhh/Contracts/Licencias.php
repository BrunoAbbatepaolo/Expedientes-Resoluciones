<?php

namespace App\Services\Rrhh\Contracts;

use App\Models\Mitiv\Ausencia;
use App\Models\Mitiv\Empleado;
use App\Services\Rrhh\Alcance;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Licencias, inasistencias registradas y saldo de vacaciones. */
interface Licencias
{
    /** Licencias de un empleado (observación oculta si el alcance no la ve). @return Collection<int, Ausencia> */
    public function deEmpleado(Alcance $alcance, int $empleadoId, ?int $anio = null): Collection;

    /** Inasistencias registradas de un empleado en un año. */
    public function inasistenciasDe(Alcance $alcance, int $empleadoId, int $anio): Collection;

    /** Saldo de vacaciones por año (más reciente primero). @return Collection<int, object{anio: int, saldo: int}> */
    public function saldo(Alcance $alcance, int $empleadoId): Collection;

    /** Próxima licencia (o la que está en curso). */
    public function proxima(Alcance $alcance, int $empleadoId, CarbonImmutable $desde): ?Ausencia;

    /**
     * Listado general (solo RRHH). Filtros: tipo_id, desde, hasta, oficina_id, texto.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function buscar(Alcance $alcance, array $filtros, int $porPagina = 25): LengthAwarePaginator;

    /**
     * Empleados activos con vacaciones adeudadas (solo RRHH), mayor saldo primero.
     *
     * @return Collection<int, array{empleado: Empleado, total: int, anios: array<int, int>}>
     */
    public function adeudadas(Alcance $alcance, ?int $oficinaId = null): Collection;

    /**
     * Cantidad de licencias y días por tipo en un período (solo RRHH).
     *
     * @return Collection<int, \stdClass> cada uno con tipo, cantidad y dias
     */
    public function porTipo(Alcance $alcance, CarbonImmutable $desde, CarbonImmutable $hasta, ?int $oficinaId = null): Collection;

    /** @return Collection<int, string> id => descripción */
    public function tipos(): Collection;
}
