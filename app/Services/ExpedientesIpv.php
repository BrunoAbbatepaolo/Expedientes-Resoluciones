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

        return $query->orderByDesc('e.id')
            ->paginate($porPagina, ['*'], $pagina)
            ->through(fn ($fila) => $this->conDias($fila));
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

    private function conDias(object $fila): object
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

    public function idPorNumero(string $numero): ?int
    {
        return $this->db()->table('expedientes')->where('numero', trim($numero))->value('id');
    }

    /**
     * Ubicación actual de varios expedientes por número (para "Mi oficina").
     *
     * @param  array<int, string>  $numeros
     * @return Collection<string, object> keyed por número
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
     */
    public function enOficina(int $oficinaId, int $minDias = 0, int $limite = 50): Collection
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
     * @return array{en_oficina: int, demorados: int, recibidos_30d: int}
     */
    public function resumenOficina(int $oficinaId, int $diasDemora = 30): array
    {
        return Cache::remember("ipv.resumen.$oficinaId.$diasDemora", now()->addMinutes(5), function () use ($oficinaId, $diasDemora) {
            $enOficina = $this->db()->table('expediente_pases as px')
                ->where('px.oficina_id', $oficinaId)->where('px.habilitado', true)
                ->whereNotExists(fn ($q) => $this->pasePosterior($q));

            $desde = now()->subDays(30);

            return [
                'en_oficina' => (clone $enOficina)->count(),
                'demorados' => (clone $enOficina)->where('px.fecha_hora', '<=', now()->subDays($diasDemora))->count(),
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

    /** Oficinas del IPV (destinos de pase) habilitadas, para filtros y selects. */
    public function oficinas(): Collection
    {
        return Cache::remember('ipv.oficinas', now()->addHour(), fn () => $this->db()->table('expediente_oficinas')
            ->where('habilitado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']));
    }
}
