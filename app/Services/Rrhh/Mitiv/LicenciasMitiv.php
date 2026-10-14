<?php

namespace App\Services\Rrhh\Mitiv;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Ausencia;
use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\Inasistencia;
use App\Models\Mitiv\TipoAusencia;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\CalculoVacaciones;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LicenciasMitiv implements Licencias
{
    use ConsultaMitiv;

    public function __construct(private Calendario $calendario) {}

    public function deEmpleado(Alcance $alcance, int $empleadoId, ?int $anio = null): Collection
    {
        $this->nivelDe($alcance, $empleadoId);

        $licencias = Ausencia::with('tipo:id,descripcion,afecta_vacaciones')
            ->where('empleado_id', $empleadoId)
            ->when($anio, fn ($q) => $q->entre("$anio-01-01", "$anio-12-31"))
            ->orderByDesc('desde')
            ->get(['id', 'empleado_id', 'desde', 'hasta', 'tipo_ausencias_id', 'observacion']);

        if (! $alcance->veObservacion($empleadoId)) {
            $licencias->each(fn (Ausencia $a) => $a->setAttribute('observacion', null));
        }

        return $licencias;
    }

    public function inasistenciasDe(Alcance $alcance, int $empleadoId, int $anio): Collection
    {
        $this->nivelDe($alcance, $empleadoId);

        return Inasistencia::with('tipo:id,nombre')
            ->vigentes()
            ->where('empleado_id', $empleadoId)
            ->whereBetween('fecha', ["$anio-01-01", "$anio-12-31"])
            ->orderByDesc('fecha')
            ->get(['id', 'empleado_id', 'tipo_inasistencia_id', 'fecha']);
    }

    public function vacaciones(Alcance $alcance, int $empleadoId, ?int $periodo = null): \stdClass
    {
        $this->nivelDe($alcance, $empleadoId);

        return $this->calcularVacaciones([$empleadoId], $periodo ?? CalculoVacaciones::periodoVigente(), conDetalle: true)[$empleadoId];
    }

    public function proxima(Alcance $alcance, int $empleadoId, CarbonImmutable $desde): ?Ausencia
    {
        $this->nivelDe($alcance, $empleadoId);

        return Ausencia::with('tipo:id,descripcion')
            ->where('empleado_id', $empleadoId)
            ->where('hasta', '>=', $desde->toDateString())
            ->orderBy('desde')
            ->first(['id', 'desde', 'hasta', 'tipo_ausencias_id']);
    }

    public function buscar(Alcance $alcance, array $filtros, int $porPagina = 25): LengthAwarePaginator
    {
        $this->soloRrhh($alcance);

        $desde = ($filtros['desde'] ?? null) ?: Hora::ahora()->startOfMonth()->toDateString();
        $hasta = ($filtros['hasta'] ?? null) ?: Hora::ahora()->endOfMonth()->toDateString();

        $query = Ausencia::with(['tipo:id,descripcion', 'empleado'])
            ->entre($desde, $hasta)
            ->when(! empty($filtros['tipo_id']), fn ($q) => $q->where('tipo_ausencias_id', (int) $filtros['tipo_id']));

        $texto = trim((string) ($filtros['texto'] ?? ''));
        if ($texto !== '' || ! empty($filtros['oficina_id'])) {
            $empleados = Empleado::query()
                ->when(! empty($filtros['oficina_id']), fn ($q) => $q->where('oficina_id', (int) $filtros['oficina_id']))
                ->when($texto !== '', fn ($q) => ctype_digit($texto)
                    ? $q->where('legajo', (int) $texto)
                    : $q->where('apellido', $this->like(), '%'.self::escaparLike($texto).'%'))
                ->pluck('id');
            $query->whereIn('empleado_id', $empleados->all() ?: [0]);
        }

        return $query->orderByDesc('desde')->paginate($porPagina);
    }

    public function adeudadas(Alcance $alcance, ?int $oficinaId = null): Collection
    {
        $this->soloRrhh($alcance);

        $empleados = Empleado::conNivel(NivelDatoRrhh::Basico)
            ->activos()
            ->with('oficinaRrhh:id,nombre')
            ->when($oficinaId, fn ($q) => $q->where('empleados.oficina_id', $oficinaId))
            ->get();
        $vacaciones = $this->calcularVacaciones($empleados->pluck('id')->all(), CalculoVacaciones::periodoVigente());

        return $empleados
            ->map(fn (Empleado $e) => ['empleado' => $e, 'vacaciones' => $vacaciones[$e->id]])
            ->filter(fn ($fila) => $fila['vacaciones']->pendientes > 0)
            ->sortByDesc(fn ($fila) => $fila['vacaciones']->pendientes)
            ->values();
    }

    /**
     * Vacaciones de un período para varios empleados con 4 consultas en total.
     *
     * @param  list<int>  $ids
     * @return array<int, \stdClass>
     */
    private function calcularVacaciones(array $ids, int $periodo, bool $conDetalle = false): array
    {
        if ($ids === []) {
            return [];
        }

        $antiguedad = ['fecha_ingreso', 'anio_reconocimiento_nacional', 'mes_reconocimiento_nacional', 'dia_reconocimiento_nacional',
            'anio_reconocimiento_provincial', 'mes_reconocimiento_provincial', 'dia_reconocimiento_provincial',
            'anio_reconocimiento_municipal', 'mes_reconocimiento_municipal', 'dia_reconocimiento_municipal'];
        $empleados = Empleado::withoutGlobalScope('columnas_publicas')
            ->whereIn('empleados.id', $ids)
            ->get(['empleados.id', ...array_map(fn ($c) => 'empleados.'.$c, $antiguedad)])
            ->keyBy('id');

        $tiposVacaciones = $this->tiposVacaciones();
        $tiposAnticipo = (array) config('sirex.rrhh_tipos_anticipo_vacaciones');

        $ausencias = Ausencia::with('tipo:id,descripcion')
            ->whereIn('empleado_id', $ids)
            ->where('anio', $periodo)
            ->whereIn('tipo_ausencias_id', [...$tiposVacaciones, ...$tiposAnticipo] ?: [0])
            ->orderBy('desde')
            ->get(['id', 'empleado_id', 'desde', 'hasta', 'tipo_ausencias_id'])
            ->groupBy('empleado_id');

        // La inasistencia "anticipo de licencia" no tiene período: se imputa al que se está
        // tomando el año en que ocurrió.
        $desfase = (int) config('sirex.rrhh_vacaciones_desfase');
        $anticiposInasistencia = Inasistencia::vigentes()
            ->whereIn('empleado_id', $ids)
            ->where('tipo_inasistencia_id', (int) config('sirex.rrhh_inasistencia_anticipo_id'))
            ->whereBetween('fecha', [($periodo + $desfase).'-01-01', ($periodo + $desfase).'-12-31'])
            ->get(['empleado_id'])
            ->countBy('empleado_id');

        $todas = $ausencias->flatten();
        $noHabiles = $todas->isEmpty() ? [] : $this->calendario->diasNoHabiles(
            CarbonImmutable::instance($todas->min('desde')), CarbonImmutable::instance($todas->max('hasta')),
        );

        $resultado = [];
        foreach ($ids as $id) {
            $e = $empleados[$id] ?? null;
            $corresponden = CalculoVacaciones::corresponden(
                $e?->fecha_ingreso,
                $periodo,
                ['anios' => $e?->anio_reconocimiento_nacional, 'meses' => $e?->mes_reconocimiento_nacional, 'dias' => $e?->dia_reconocimiento_nacional],
                ['anios' => $e?->anio_reconocimiento_provincial, 'meses' => $e?->mes_reconocimiento_provincial, 'dias' => $e?->dia_reconocimiento_provincial],
                ['anios' => $e?->anio_reconocimiento_municipal, 'meses' => $e?->mes_reconocimiento_municipal, 'dias' => $e?->dia_reconocimiento_municipal],
            );

            $detalle = ($ausencias[$id] ?? collect())->each(fn (Ausencia $a) => $a->setAttribute(
                'dias_habiles', CalculoVacaciones::diasHabiles($a->desde, $a->hasta, $noHabiles),
            ));
            $esAnticipo = fn (Ausencia $a) => in_array((int) $a->tipo_ausencias_id, $tiposAnticipo, true);
            $tomadas = (int) $detalle->reject($esAnticipo)->sum('dias_habiles');
            $anticipadas = (int) $detalle->filter($esAnticipo)->sum('dias_habiles') + (int) ($anticiposInasistencia[$id] ?? 0);

            $v = new \stdClass;
            $v->periodo = $periodo;
            $v->antiguedad = $corresponden['antiguedad'];
            $v->corresponden = $corresponden['dias'];
            $v->tomadas = $tomadas;
            $v->anticipadas = $anticipadas;
            $v->pendientes = $corresponden['dias'] - $tomadas - $anticipadas;
            $v->detalle = $conDetalle ? $detalle->values() : collect();
            $resultado[$id] = $v;
        }

        return $resultado;
    }

    public function porTipo(Alcance $alcance, CarbonImmutable $desde, CarbonImmutable $hasta, ?int $oficinaId = null): Collection
    {
        $this->soloRrhh($alcance);

        $query = Ausencia::with('tipo:id,descripcion')->entre($desde->toDateString(), $hasta->toDateString());
        if ($oficinaId) {
            $query->whereIn('empleado_id', Empleado::where('oficina_id', $oficinaId)->pluck('id')->all() ?: [0]);
        }

        return $query->get(['id', 'desde', 'hasta', 'tipo_ausencias_id'])
            ->groupBy('tipo_ausencias_id')
            ->map(function (Collection $grupo) use ($desde, $hasta) {
                $dias = $grupo->sum(function (Ausencia $a) use ($desde, $hasta) {
                    $inicio = $a->desde->max($desde);
                    $fin = $a->hasta->min($hasta);

                    return $fin->gte($inicio) ? (int) $inicio->diffInDays($fin) + 1 : 0;
                });

                $fila = new \stdClass;
                $fila->tipo = (string) ($grupo->first()->tipo->descripcion ?? 'Sin tipo');
                $fila->cantidad = $grupo->count();
                $fila->dias = (int) $dias;

                return $fila;
            })
            ->sortByDesc('dias')
            ->values();
    }

    public function inasistenciasEntre(Alcance $alcance, CarbonImmutable $desde, CarbonImmutable $hasta, ?int $oficinaId = null): Collection
    {
        $this->soloRrhh($alcance);

        $empleados = Empleado::query()->conNivel(NivelDatoRrhh::Basico)->with('oficinaRrhh:id,nombre')
            ->when($oficinaId, fn ($q) => $q->where('empleados.oficina_id', $oficinaId))
            ->get()->keyBy('id');

        return Inasistencia::vigentes()->with('tipo:id,nombre')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->whereIn('empleado_id', $empleados->keys()->all() ?: [0])
            ->get(['id', 'empleado_id', 'tipo_inasistencia_id'])
            ->groupBy(fn (Inasistencia $i) => $i->empleado_id.'|'.$i->tipo_inasistencia_id)
            ->map(function (Collection $grupo) use ($empleados) {
                $e = $empleados[$grupo->first()->empleado_id];

                return self::fila([
                    'legajo' => $e->legajo,
                    'apellido' => $e->apellido,
                    'nombre' => $e->nombre,
                    'oficina' => $e->oficinaRrhh->nombre ?? '',
                    'tipo' => (string) ($grupo->first()->tipo->nombre ?? 'Sin tipo'),
                    'cantidad' => $grupo->count(),
                ]);
            })
            ->sortBy([['apellido', 'asc'], ['nombre', 'asc'], ['tipo', 'asc']])
            ->values();
    }

    public function esTipoVacaciones(int $tipoAusenciaId): bool
    {
        return in_array($tipoAusenciaId, $this->tiposVacaciones(), true);
    }

    public function tieneLicenciaEntre(int $empleadoId, CarbonImmutable $desde, CarbonImmutable $hasta): bool
    {
        // Los regímenes (CONT, planta justificada, etc.) duran meses: no impiden pedir licencia.
        $regimenes = [...(array) config('sirex.rrhh_tipos_sin_fichar'), ...(array) config('sirex.rrhh_tipos_ignorados')];

        return Ausencia::where('empleado_id', $empleadoId)
            ->entre($desde->toDateString(), $hasta->toDateString())
            ->whereNotIn('tipo_ausencias_id', $regimenes ?: [0])
            ->exists();
    }

    /** @return list<int> tipos que descuentan vacaciones (configurados + afecta_vacaciones) */
    private function tiposVacaciones(): array
    {
        return Cache::remember('rrhh.tipos_vacaciones', now()->addHour(), fn () => TipoAusencia::where('afecta_vacaciones', true)
            ->pluck('id')->map(fn ($id) => (int) $id)->merge(config('sirex.rrhh_tipos_vacaciones'))->unique()->values()->all());
    }

    public function tipos(): Collection
    {
        return Cache::remember('rrhh.tipos_ausencia', now()->addHour(), fn () => TipoAusencia::where('habilitado', true)->orderBy('descripcion')->pluck('descripcion', 'id'));
    }
}
