<?php

namespace App\Services\Rrhh\Mitiv;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Ausencia;
use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\Inasistencia;
use App\Models\Mitiv\SaldoVacaciones;
use App\Models\Mitiv\TipoAusencia;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\Contracts\Licencias;
use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LicenciasMitiv implements Licencias
{
    use ConsultaMitiv;

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

    public function saldo(Alcance $alcance, int $empleadoId): Collection
    {
        $this->nivelDe($alcance, $empleadoId);

        return SaldoVacaciones::where('id', $empleadoId)->orderByDesc('anio')->get(['anio', 'saldo'])
            ->map(fn ($s) => (object) ['anio' => (int) $s->anio, 'saldo' => (int) $s->saldo]);
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

        $saldos = SaldoVacaciones::where('saldo', '>', 0)->get(['id', 'anio', 'saldo'])->groupBy('id');
        if ($saldos->isEmpty()) {
            return collect();
        }

        return Empleado::conNivel(NivelDatoRrhh::Basico)
            ->activos()
            ->with('oficinaRrhh:id,nombre')
            ->whereIn('empleados.id', $saldos->keys()->all())
            ->when($oficinaId, fn ($q) => $q->where('empleados.oficina_id', $oficinaId))
            ->get()
            ->map(function (Empleado $e) use ($saldos) {
                $anios = $saldos[$e->id]->sortBy('anio')->mapWithKeys(fn ($s) => [(int) $s->anio => (int) $s->saldo])->all();

                return ['empleado' => $e, 'total' => array_sum($anios), 'anios' => $anios];
            })
            ->sortByDesc('total')
            ->values();
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

    public function tipos(): Collection
    {
        return Cache::remember('rrhh.tipos_ausencia', now()->addHour(), fn () => TipoAusencia::where('habilitado', true)->orderBy('descripcion')->pluck('descripcion', 'id'));
    }
}
