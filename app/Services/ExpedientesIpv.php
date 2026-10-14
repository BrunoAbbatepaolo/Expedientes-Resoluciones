<?php

namespace App\Services;

use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\Expediente;
use App\Models\Oficina;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de expedientes de todo el IPV sobre mitivdb (sistema viejo). Solo lectura.
 *
 * Rendimiento (medido sobre ~450 mil expedientes y ~2,2 M de pases): se ordena por
 * expedientes.id (índice PK) y la ubicación actual sale de una subconsulta que usa el
 * índice (expediente_id, fecha_hora) de expediente_pases. Ordenar por fecha tarda >10 s.
 */
class ExpedientesIpv
{
    private function db(): Connection
    {
        return DB::connection('pgsql_mitiv');
    }

    /** ILIKE en PostgreSQL; LIKE en sqlite (tests), que ya ignora mayúsculas. */
    private function like(): string
    {
        return $this->db()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private static function escaparLike(string $texto): string
    {
        return addcslashes($texto, '%_\\');
    }

    /**
     * Subconsulta: último pase habilitado de cada expediente (e.id).
     */
    private function ultimoPase(string $columna): Builder
    {
        return $this->db()->table('expediente_pases as p')
            ->select($columna)
            ->join('expediente_oficinas as o', 'o.id', '=', 'p.oficina_id')
            ->whereColumn('p.expediente_id', 'e.id')
            ->where('p.habilitado', true)
            ->orderByDesc('p.fecha_hora')
            ->orderByDesc('p.id')
            ->limit(1);
    }

    /**
     * @param  array{texto?: ?string, oficina_id?: ?int, desde?: ?string, hasta?: ?string}  $filtros
     */
    public function buscar(array $filtros, int $porPagina = 15, string $pagina = 'page'): LengthAwarePaginator
    {
        return $this->consulta($filtros)
            ->paginate($porPagina, ['*'], $pagina)
            ->through(fn ($fila) => $this->conDias($fila));
    }

    /**
     * Primeros resultados sin contar el total (el COUNT es lo más caro en búsquedas
     * por texto). Para la búsqueda global del menú.
     *
     * @return Collection<int, \stdClass>
     */
    public function buscarRapido(string $texto, int $limite = 5): Collection
    {
        return $this->consulta(['texto' => $texto])->limit($limite)->get()->map(fn ($f) => $this->conDias($f));
    }

    /**
     * @param  array{texto?: ?string, oficina_id?: ?int, desde?: ?string, hasta?: ?string}  $filtros
     */
    private function consulta(array $filtros): Builder
    {
        $like = $this->like();

        $query = $this->db()->table('expedientes as e')
            ->join('causantes as c', 'c.id', '=', 'e.causante_id')
            ->leftJoin('tipo_asuntos as t', 't.id', '=', 'e.asunto_id')
            ->select([
                'e.id', 'e.numero', 'e.fecha', 'e.folio', 'e.asunto',
                'c.nombre as causante', 't.nombre as tipo_asunto',
            ])
            ->selectSub($this->ultimoPase('o.nombre'), 'ubicacion')
            ->selectSub($this->ultimoPase('p.fecha_hora'), 'ubicacion_desde')
            ->where('e.habilitado', true);

        $texto = trim((string) ($filtros['texto'] ?? ''));
        if ($texto !== '') {
            $escapado = self::escaparLike($texto);
            // Un número de expediente (ej. "7780/440-2026" o "7780") se busca por prefijo,
            // que usa el índice único; cualquier otro texto, por causante o asunto.
            if (preg_match('/^\d+(\/[\d\-]*)?$/', $texto)) {
                $query->where('e.numero', $like, $escapado.'%');
            } else {
                $query->where(function ($q) use ($like, $escapado) {
                    $q->where('c.nombre', $like, '%'.$escapado.'%')
                        ->orWhere('e.asunto', $like, '%'.$escapado.'%');
                });
            }
        }

        if (! empty($filtros['oficina_id'])) {
            $query->whereExists($this->estaEnOficina((int) $filtros['oficina_id']));
        }

        if (! empty($filtros['desde'])) {
            $query->where('e.fecha', '>=', $filtros['desde']);
        }
        if (! empty($filtros['hasta'])) {
            $query->where('e.fecha', '<=', $filtros['hasta']);
        }

        return $query->orderByDesc('e.id');
    }

    /**
     * WHERE EXISTS: el último pase del expediente e.id es hacia $oficinaId.
     */
    private function estaEnOficina(int $oficinaId): Builder
    {
        return $this->db()->table('expediente_pases as px')
            ->selectRaw('1')
            ->whereColumn('px.expediente_id', 'e.id')
            ->where('px.oficina_id', $oficinaId)
            ->where('px.habilitado', true)
            ->whereNotExists(fn ($q) => $this->pasePosterior($q));

    }

    /**
     * Subconsulta para WHERE NOT EXISTS: hay un pase de px.expediente_id posterior a px
     * (es decir, px NO es el último pase = la ubicación actual).
     */
    private function pasePosterior(Builder $q): void
    {
        $q->selectRaw('1')->from('expediente_pases as py')
            ->whereColumn('py.expediente_id', 'px.expediente_id')
            ->where('py.habilitado', true)
            ->where(function ($w) {
                $w->whereColumn('py.fecha_hora', '>', 'px.fecha_hora')
                    ->orWhere(fn ($x) => $x->whereColumn('py.fecha_hora', 'px.fecha_hora')->whereColumn('py.id', '>', 'px.id'));
            });
    }

    private function conDias(\stdClass $fila): \stdClass
    {
        $fila->dias_en_ubicacion = $fila->ubicacion_desde
            ? (int) Carbon::parse($fila->ubicacion_desde)->startOfDay()->diffInDays(now()->startOfDay())
            : null;

        return $fila;
    }

    /**
     * Ficha completa: expediente, línea de tiempo de pases (con días en cada oficina)
     * y expedientes relacionados.
     *
     * @return array{expediente: Expediente, pases: Collection, relacionados: Collection, ubicacion: ?object}|null
     */
    public function ficha(int $id): ?array
    {
        $expediente = Expediente::with([
            'causante.tipo', 'tipoAsunto', 'pases.oficina', 'pases.empleado',
            'relacionadosComoPrincipal.secundario', 'relacionadosComoSecundario.principal',
        ])->find($id);

        if (! $expediente) {
            return null;
        }

        $pases = $expediente->pases->values();
        $timeline = $pases->map(function ($pase, $i) use ($pases) {
            $siguiente = $pases->get($i + 1);
            $hasta = $siguiente->fecha_hora ?? now();

            return (object) [
                'oficina' => $pase->oficina->nombre ?? 'Sin especificar',
                'fecha_hora' => $pase->fecha_hora,
                'empleado' => $pase->empleado?->nombre_completo,
                'folio' => $pase->folio,
                'folio_actuales' => $pase->folio_actuales,
                'observacion' => $pase->observacion,
                'dias' => $pase->fecha_hora
                    ? (int) $pase->fecha_hora->copy()->startOfDay()->diffInDays(Carbon::parse($hasta)->startOfDay())
                    : null,
                'actual' => $siguiente === null,
            ];
        });

        $relacionados = $expediente->relacionadosComoPrincipal->map(fn ($r) => $r->secundario)
            ->merge($expediente->relacionadosComoSecundario->map(fn ($r) => $r->principal))
            ->filter()
            ->unique('id')
            ->values();

        return [
            'expediente' => $expediente,
            'pases' => $timeline,
            'relacionados' => $relacionados,
            'ubicacion' => $timeline->last(),
        ];
    }

    /**
     * Datos mínimos de un expediente por su número exacto, o null si no existe en mitivdb.
     * Lo usa Resoluciones para exigir que cada resolución esté vinculada a un expediente real.
     */
    public function resumenPorNumero(string $numero): ?\stdClass
    {
        $numero = trim($numero);
        if ($numero === '') {
            return null;
        }

        return $this->db()->table('expedientes as e')
            ->leftJoin('causantes as c', 'c.id', '=', 'e.causante_id')
            ->where('e.numero', $numero)
            ->where('e.habilitado', true)
            ->first(['e.id', 'e.numero', 'e.asunto', 'c.nombre as causante']);
    }

    public function idPorNumero(string $numero): ?int
    {
        return $this->db()->table('expedientes')->where('numero', trim($numero))->value('id');
    }

    /**
     * Ubicación actual de varios expedientes por número (para "Mi oficina").
     *
     * @param  array<int, string>  $numeros
     * @return Collection<array-key, \stdClass> keyed por número
     */
    public function ubicaciones(array $numeros): Collection
    {
        $numeros = array_values(array_filter(array_unique($numeros)));
        if ($numeros === []) {
            return collect();
        }

        return $this->db()->table('expedientes as e')
            ->select('e.id', 'e.numero')
            ->selectSub($this->ultimoPase('o.nombre'), 'ubicacion')
            ->selectSub($this->ultimoPase('p.fecha_hora'), 'ubicacion_desde')
            ->whereIn('e.numero', $numeros)
            ->get()
            ->map(fn ($f) => $this->conDias($f))
            ->keyBy('numero');
    }

    /**
     * Expedientes que hoy están en una oficina del IPV, del más demorado al más reciente.
     * $maxDias limita a los que llegaron hace como mucho N días (excluye el "archivo":
     * expedientes de hace décadas que nunca se volvieron a pasar).
     */
    public function enOficina(int $oficinaId, int $minDias = 0, int $limite = 50, ?int $maxDias = null): Collection
    {
        $query = $this->db()->table('expediente_pases as px')
            ->join('expedientes as e', 'e.id', '=', 'px.expediente_id')
            ->join('causantes as c', 'c.id', '=', 'e.causante_id')
            ->select('e.id', 'e.numero', 'e.asunto', 'c.nombre as causante', 'px.fecha_hora as ubicacion_desde')
            ->where('px.oficina_id', $oficinaId)
            ->where('px.habilitado', true)
            ->whereNotExists(fn ($q) => $this->pasePosterior($q));


        if ($minDias > 0) {
            $query->where('px.fecha_hora', '<=', now()->subDays($minDias));
        }
        if ($maxDias !== null) {
            $query->where('px.fecha_hora', '>=', now()->subDays($maxDias));
        }

        return $query->orderBy('px.fecha_hora')
            ->limit($limite)
            ->get()
            ->map(function ($f) {
                $f->ubicacion = null;

                return $this->conDias($f);
            });
    }

    /**
     * Métricas de una oficina del IPV para el tablero (cacheadas 5 minutos).
     *
     * "Activos" = llegaron dentro de la ventana de actividad; el resto es archivo histórico.
     *
     * @return array{en_oficina: int, activos: int, demorados: int, recibidos_30d: int}
     */
    public function resumenOficina(int $oficinaId, int $diasDemora = 30, int $ventana = 365): array
    {
        return Cache::remember("ipv.resumen.$oficinaId.$diasDemora.$ventana", now()->addMinutes(5), function () use ($oficinaId, $diasDemora, $ventana) {
            $enOficina = $this->db()->table('expediente_pases as px')
                ->where('px.oficina_id', $oficinaId)->where('px.habilitado', true)
                ->whereNotExists(fn ($q) => $this->pasePosterior($q));
            $activos = (clone $enOficina)->where('px.fecha_hora', '>=', now()->subDays($ventana));

            $desde = now()->subDays(30);

            return [
                'en_oficina' => (clone $enOficina)->count(),
                'activos' => (clone $activos)->count(),
                'demorados' => (clone $activos)->where('px.fecha_hora', '<=', now()->subDays($diasDemora))->count(),
                'recibidos_30d' => $this->db()->table('expediente_pases')
                    ->where('oficina_id', $oficinaId)->where('habilitado', true)
                    ->where('fecha_hora', '>=', $desde)->count(),
            ];
        });
    }

    /**
     * Oficina del IPV (expediente_oficinas.id) del usuario: primero el vínculo cargado
     * en su oficina de SiRex; si no hay, la oficina del IPV de su legajo (si es una sola).
     */
    public function oficinaIpvDe(User $user): ?int
    {
        $oficinaId = $user->oficinaAsignadaId();
        $vinculada = $oficinaId ? Oficina::whereKey($oficinaId)->value('mitiv_oficina_id') : null;
        if ($vinculada) {
            return (int) $vinculada;
        }

        if (! ctype_digit((string) $user->legajo)) {
            return null;
        }

        $ids = $this->db()->table('empleado_expediente_oficinas as eo')
            ->join('empleados as emp', 'emp.id', '=', 'eo.empleado_id')
            ->where('emp.legajo', (int) $user->legajo)
            ->where('eo.habilitado', true)
            ->pluck('eo.expediente_oficina_id')
            ->unique();

        return $ids->count() === 1 ? (int) $ids->first() : null;
    }

    /**
     * Personal activo de una oficina del IPV (solo datos de contacto no sensibles).
     *
     * @return Collection<int, Empleado>
     */
    public function personal(int $oficinaId): Collection
    {
        return Empleado::activos()
            ->join('empleado_expediente_oficinas as eo', 'eo.empleado_id', '=', 'empleados.id')
            ->where('eo.expediente_oficina_id', $oficinaId)
            ->where('eo.habilitado', true)
            ->orderBy('empleados.apellido')
            ->orderBy('empleados.nombre')
            ->get()
            ->unique('id')
            ->values();
    }

    /** Tope de pases que se leen por consulta de ingresos (una oficina grande en un año ronda unos pocos miles). */
    public const MAX_PASES_INGRESOS = 20000;

    /**
     * Expedientes que ENTRARON a una oficina entre dos fechas (inclusive), sin importar dónde estén hoy.
     * Un expediente cuenta una sola vez: se muestra su primera entrada del período y cuántas veces entró.
     *
     * @return array{filas: Collection<int, \stdClass>, truncado: bool}
     */
    public function ingresosAOficina(int $oficinaId, string $desde, string $hasta): array
    {
        $anterior = $this->db()->table('expediente_pases as pa')
            ->join('expediente_oficinas as oa', 'oa.id', '=', 'pa.oficina_id')
            ->select('oa.nombre')
            ->whereColumn('pa.expediente_id', 'p.expediente_id')
            ->where('pa.habilitado', true)
            ->where(function ($w) {
                $w->whereColumn('pa.fecha_hora', '<', 'p.fecha_hora')
                    ->orWhere(fn ($x) => $x->whereColumn('pa.fecha_hora', 'p.fecha_hora')->whereColumn('pa.id', '<', 'p.id'));
            })
            ->orderByDesc('pa.fecha_hora')->orderByDesc('pa.id')
            ->limit(1);

        $pases = $this->db()->table('expediente_pases as p')
            ->join('expedientes as e', 'e.id', '=', 'p.expediente_id')
            ->join('causantes as c', 'c.id', '=', 'e.causante_id')
            ->select('e.id', 'e.numero', 'e.asunto', 'c.nombre as causante', 'p.fecha_hora')
            ->selectSub($anterior, 'viene_de')
            ->where('p.oficina_id', $oficinaId)
            ->where('p.habilitado', true)
            ->where('e.habilitado', true)
            ->whereBetween('p.fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->orderBy('p.fecha_hora')->orderBy('p.id')
            ->limit(self::MAX_PASES_INGRESOS + 1)
            ->get();

        $truncado = $pases->count() > self::MAX_PASES_INGRESOS;

        $filas = $pases->take(self::MAX_PASES_INGRESOS)->groupBy('id')->map(function ($entradas) {
            /** @var \stdClass $primera */
            $primera = $entradas->first();
            $primera->veces = $entradas->count();

            return $primera;
        })->values();

        return ['filas' => $filas, 'truncado' => $truncado];
    }

    /**
     * Pases que SALIERON de una oficina (o de todas) entre dos fechas: de dónde a dónde, quién y cuándo.
     * Usa la vista `expediente_pases_origen` de mitivdb (origen = oficina del pase anterior del expediente);
     * el alta del expediente no cuenta porque no tiene origen.
     *
     * @return array{filas: Collection<int, \stdClass>, truncado: bool}
     */
    public function pasesEmitidos(?int $origenId, string $desde, string $hasta): array
    {
        $filas = $this->db()->table('expediente_pases_origen as p')
            ->join('expedientes as e', 'e.id', '=', 'p.expediente_id')
            ->join('causantes as c', 'c.id', '=', 'e.causante_id')
            ->join('expediente_oficinas as o', 'o.id', '=', 'p.origen_id')
            ->leftJoin('expediente_oficinas as d', 'd.id', '=', 'p.oficina_id')
            ->leftJoin('empleados as em', 'em.id', '=', 'p.empleado_id')
            ->select('e.id', 'e.numero', 'e.asunto', 'c.nombre as causante', 'p.fecha_hora', 'p.observacion',
                'o.nombre as origen', 'd.nombre as destino', 'em.apellido', 'em.nombre as nombre_empleado')
            ->where('p.habilitado', true)
            ->where('e.habilitado', true)
            ->when($origenId, fn ($q) => $q->where('p.origen_id', $origenId))
            ->whereBetween('p.fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->orderBy('p.fecha_hora')->orderBy('p.id')
            ->limit(self::MAX_PASES_INGRESOS + 1)
            ->get();

        $truncado = $filas->count() > self::MAX_PASES_INGRESOS;

        return ['filas' => $filas->take(self::MAX_PASES_INGRESOS)->values(), 'truncado' => $truncado];
    }

    /**
     * Valida el período de un informe de ingresos: fechas Y-m-d, desde <= hasta y no más de un año.
     * Devuelve el motivo del rechazo, o null si está bien.
     */
    public static function errorDePeriodo(?string $desde, ?string $hasta): ?string
    {
        $valida = fn (?string $f) => $f && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) && Carbon::hasFormat($f, 'Y-m-d') && Carbon::createFromFormat('Y-m-d', $f)->format('Y-m-d') === $f;

        if (! $valida($desde) || ! $valida($hasta)) {
            return 'Elegí las dos fechas.';
        }
        if ($desde > $hasta) {
            return 'La fecha "desde" no puede ser posterior a "hasta".';
        }
        if (Carbon::parse($desde)->diffInDays(Carbon::parse($hasta)) > 366) {
            return 'El período no puede superar un año.';
        }

        return null;
    }

    /** Oficinas del IPV (destinos de pase) habilitadas, para filtros y selects. */
    public function oficinas(): Collection
    {
        return Cache::remember('ipv.oficinas', now()->addHour(), fn () => $this->db()->table('expediente_oficinas')
            ->where('habilitado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']));
    }
}
