<?php

namespace App\Services\Rrhh\Contracts;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Empleado;
use App\Services\Rrhh\Alcance;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Legajos de empleados. Todo método aplica el Alcance (403 si no corresponde). */
interface Legajos
{
    /** Legajo completo del propio usuario (null si su legajo no existe en la fuente). */
    public function propio(Alcance $alcance): ?Empleado;

    /**
     * Listado con datos básicos. Filtros: texto, oficina_id, situacion_laboral_id,
     * categoria_id, estado (activos | pasivos | todos; por defecto activos).
     *
     * @param  array<string, mixed>  $filtros
     */
    public function buscar(Alcance $alcance, array $filtros, int $porPagina = 20): LengthAwarePaginator;

    /**
     * Ficha con las columnas del nivel que corresponde. Registra el acceso si ve datos
     * personales o sensibles de otra persona.
     *
     * @return array{empleado: Empleado, nivel: NivelDatoRrhh, propio: bool, familia: Collection, fojas: Collection, movimientos: Collection, archivos: Collection}
     */
    public function ficha(Alcance $alcance, int $empleadoId, bool $registrarAcceso = true): array;

    /** Archivo del legajo con su contenido (solo nivel personal o superior). */
    public function archivo(Alcance $alcance, int $empleadoId, int $archivoId): ?object;

    /** Empleados activos visibles (o de una oficina visible), con datos básicos. @return Collection<int, Empleado> */
    public function equipo(Alcance $alcance, ?int $oficinaId = null): Collection;

    /** Para la búsqueda global: apellido, nombre o legajo. @return Collection<int, Empleado> */
    public function buscarRapido(Alcance $alcance, string $texto, int $limite = 5): Collection;

    /**
     * Cumpleaños de los empleados activos en un mes (solo RRHH). Devuelve solo día y mes, nunca el año ni la edad.
     *
     * @return Collection<int, \stdClass> cada uno con dia, legajo, apellido, nombre y oficina
     */
    public function cumpleanios(Alcance $alcance, int $mes): Collection;

    /**
     * Antigüedad de los empleados activos al día `$al` (solo RRHH): tiempo en el IPV más los reconocimientos
     * (nacional, provincial y municipal). Más antiguos primero.
     *
     * @return Collection<int, \stdClass> cada uno con legajo, apellido, nombre, situacion, ingreso, antiguedad_ipv y antiguedad_total
     */
    public function antiguedad(Alcance $alcance, CarbonImmutable $al, ?int $situacionId = null): Collection;

    /** @return Collection<int, string> id => nombre */
    public function situaciones(): Collection;

    /** @return Collection<int, string> id => nombre */
    public function categorias(): Collection;
}
