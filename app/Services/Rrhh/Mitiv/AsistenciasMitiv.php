<?php

namespace App\Services\Rrhh\Mitiv;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Ausencia;
use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\Horario;
use App\Models\Mitiv\Inasistencia;
use App\Models\Mitiv\RegistroReloj;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\CalculoAsistencia;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Calendario;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\DiaAsistencia;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AsistenciasMitiv implements Asistencias
{
    use ConsultaMitiv;

    public function __construct(
        private CalculoAsistencia $calculo,
        private Calendario $calendario,
        private Legajos $legajos,
    ) {}

    public function mes(Alcance $alcance, int $empleadoId, int $anio, int $mes): array
    {
        $this->nivelDe($alcance, $empleadoId);

        $empleado = Empleado::conNivel(NivelDatoRrhh::Basico)->findOrFail($empleadoId);
        $desde = CarbonImmutable::create($anio, $mes, 1);

        return $this->calcular(collect([$empleado]), $desde, $desde->endOfMonth())[$empleadoId] ?? [];
    }

    public function dia(Alcance $alcance, CarbonImmutable $fecha, ?int $oficinaId = null): Collection
    {
        $empleados = $this->legajos->equipo($alcance, $oficinaId);
        $clave = 'rrhh.dia.'.md5(json_encode([$fecha->toDateString(), $empleados->pluck('id')->all()]));

        // El tablero del día se refresca seguido: 60 s de cache alcanzan (spec, sección 8).
        $dias = Cache::remember($clave, now()->addSeconds(60), fn () => $this->calcular($empleados, $fecha, $fecha));

        return $empleados->map(fn (Empleado $e) => ['empleado' => $e, 'dia' => $dias[$e->id][0]])->values();
    }

    public function grilla(Alcance $alcance, int $anio, int $mes, int $oficinaId): array
    {
        $empleados = $this->legajos->equipo($alcance, $oficinaId);
        $desde = CarbonImmutable::create($anio, $mes, 1);
        $hasta = $desde->endOfMonth();
        $dias = $this->calcular($empleados, $desde, $hasta);

        return [
            'dias' => array_map(fn ($d) => CarbonImmutable::instance($d), CarbonPeriod::create($desde, $hasta)->toArray()),
            'filas' => $empleados->map(fn (Empleado $e) => ['empleado' => $e, 'dias' => $dias[$e->id]])->values()->all(),
        ];
    }

    public function fichadas(Alcance $alcance, array $filtros, int $porPagina = 50): LengthAwarePaginator
    {
        $this->soloRrhh($alcance);

        $hoy = CarbonImmutable::today();
        $desde = ! empty($filtros['desde']) ? CarbonImmutable::parse($filtros['desde']) : $hoy->subDays(6);
        $hasta = ! empty($filtros['hasta']) ? CarbonImmutable::parse($filtros['hasta']) : $hoy;
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        if ($desde->diffInDays($hasta) >= self::MAX_DIAS_FICHADAS) {
            $hasta = $desde->addDays(self::MAX_DIAS_FICHADAS - 1);
        }

        $query = RegistroReloj::query()
            ->whereBetween('fecha_hora', [$desde->startOfDay()->toDateTimeString(), $hasta->endOfDay()->toDateTimeString()])
            ->when(! empty($filtros['reloj']), fn ($q) => $q->where('nombre_reloj', $filtros['reloj']));

        if (! empty($filtros['empleado_id'])) {
            $card = Empleado::whereKey((int) $filtros['empleado_id'])->value('card_id');
            $query->where('card_id', $card !== null ? (int) $card : -1);
        }

        $pagina = $query->orderByDesc('fecha_hora')->paginate($porPagina, ['id', 'card_id', 'fecha_hora', 'nombre_reloj']);

        $empleados = Empleado::conNivel(NivelDatoRrhh::Basico)
            ->whereIn('empleados.card_id', $pagina->getCollection()->pluck('card_id')->unique()->all())
            ->get()
            ->keyBy(fn (Empleado $e) => (int) $e->card_id);
        $pagina->getCollection()->each(fn (RegistroReloj $r) => $r->setRelation('empleado', $empleados[(int) $r->card_id] ?? null));

        return $pagina;
    }

    public function relojes(): array
    {
        return Cache::remember('rrhh.relojes', now()->addHour(), fn () => RegistroReloj::query()
            ->where('fecha_hora', '>=', CarbonImmutable::today()->subDays(30)->toDateTimeString())
            ->whereNotNull('nombre_reloj')
            ->distinct()
            ->orderBy('nombre_reloj')
            ->pluck('nombre_reloj')
            ->all());
    }

    /**
     * Cálculo de varios empleados en un rango con 5 consultas en total (sin N+1).
     *
     * @param  Collection<int, Empleado>  $empleados
     * @return array<int, list<DiaAsistencia>> empleado => días
     */
    private function calcular(Collection $empleados, CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        if ($empleados->isEmpty()) {
            return [];
        }

        $ids = $empleados->pluck('id')->all();
        $tarjetas = $empleados->filter(fn (Empleado $e) => $e->card_id !== null && $e->card_id !== '')
            ->mapWithKeys(fn (Empleado $e) => [$e->id => (int) $e->card_id]);
        [$d, $h] = [$desde->toDateString(), $hasta->toDateString()];

        $horarios = Horario::whereIn('empleado_id', $ids)
            ->where(fn ($q) => $q->where('habilitado', true)->orWhereNull('habilitado'))
            ->get()
            ->groupBy('empleado_id')
            ->map(fn (Collection $hs) => $hs->keyBy('dia'));

        $marcas = $tarjetas->isEmpty() ? collect() : RegistroReloj::query()
            ->whereIn('card_id', $tarjetas->unique()->values()->all())
            ->whereBetween('fecha_hora', [$desde->startOfDay()->toDateTimeString(), $hasta->endOfDay()->toDateTimeString()])
            ->orderBy('fecha_hora')
            ->get(['card_id', 'fecha_hora'])
            ->groupBy(fn (RegistroReloj $r) => (int) $r->card_id.'|'.$r->fecha_hora->toDateString());

        $licencias = Ausencia::with('tipo:id,descripcion')
            ->whereIn('empleado_id', $ids)
            ->entre($d, $h)
            ->get(['id', 'empleado_id', 'desde', 'hasta', 'tipo_ausencias_id'])
            ->groupBy('empleado_id');

        $inasistencias = Inasistencia::with('tipo:id,nombre')
            ->vigentes()
            ->whereIn('empleado_id', $ids)
            ->whereBetween('fecha', [$d, $h])
            ->get(['id', 'empleado_id', 'tipo_inasistencia_id', 'fecha'])
            ->keyBy(fn (Inasistencia $i) => $i->empleado_id.'|'.$i->fecha->toDateString());

        $feriados = $this->calendario->dias($desde, $hasta);
        $ahora = CarbonImmutable::now();
        $periodo = array_map(fn ($x) => CarbonImmutable::instance($x), CarbonPeriod::create($desde->startOfDay(), $hasta->startOfDay())->toArray());

        $resultado = [];
        foreach ($empleados as $empleado) {
            foreach ($periodo as $fecha) {
                $ymd = $fecha->toDateString();
                $tarjeta = $tarjetas[$empleado->id] ?? null;
                $licencia = ($licencias[$empleado->id] ?? collect())
                    ->first(fn (Ausencia $a) => $a->desde->toDateString() <= $ymd && $a->hasta->toDateString() >= $ymd);
                $inasistencia = $inasistencias[$empleado->id.'|'.$ymd] ?? null;

                $resultado[$empleado->id][] = $this->calculo->dia(
                    $fecha,
                    $horarios[$empleado->id][$fecha->dayOfWeekIso] ?? null,
                    $tarjeta === null ? [] : ($marcas[$tarjeta.'|'.$ymd] ?? collect())
                        ->map(fn (RegistroReloj $r) => CarbonImmutable::instance($r->fecha_hora))->values()->all(),
                    isset($feriados[$ymd]),
                    $licencia ? ($licencia->tipo?->descripcion ?? 'Licencia') : null,
                    $inasistencia ? (object) ['tipo_inasistencia_id' => $inasistencia->tipo_inasistencia_id, 'nombre' => $inasistencia->tipo?->nombre ?? 'Inasistencia'] : null,
                    $ahora,
                );
            }
        }

        return $resultado;
    }
}
