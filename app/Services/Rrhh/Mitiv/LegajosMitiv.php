<?php

namespace App\Services\Rrhh\Mitiv;

use App\Enums\NivelDatoRrhh;
use App\Models\Mitiv\Categoria;
use App\Models\Mitiv\Empleado;
use App\Models\Mitiv\EmpleadoArchivo;
use App\Models\Mitiv\EmpleadoMovimiento;
use App\Models\Mitiv\Familiar;
use App\Models\Mitiv\FojaServicio;
use App\Models\Mitiv\Parentesco;
use App\Models\Mitiv\SituacionLaboral;
use App\Models\RrhhAcceso;
use App\Services\Rrhh\Alcance;
use App\Services\Rrhh\Contracts\Legajos;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LegajosMitiv implements Legajos
{
    use ConsultaMitiv;

    private const RELACIONES = ['oficinaRrhh:id,nombre', 'situacionLaboral:id,nombre', 'categoriaTitular:id,nombre', 'categoriaInterina:id,nombre'];

    public function propio(Alcance $alcance): ?Empleado
    {
        if ($alcance->empleadoId === null) {
            return null;
        }

        return Empleado::conNivel(NivelDatoRrhh::Sensible)->with(self::RELACIONES)->find($alcance->empleadoId);
    }

    public function buscar(Alcance $alcance, array $filtros, int $porPagina = 20): LengthAwarePaginator
    {
        if (! $alcance->rrhh && ! $alcance->esJefe()) {
            throw new AuthorizationException('Necesitás el permiso de RRHH o ser jefe.');
        }

        $query = $this->visibles($alcance)
            ->conNivel(NivelDatoRrhh::Basico)
            ->with(['oficinaRrhh:id,nombre', 'situacionLaboral:id,nombre', 'categoriaTitular:id,nombre']);

        match ($filtros['estado'] ?? 'activos') {
            'pasivos' => $query->where('empleados.espasivo', true),
            'todos' => null,
            default => $query->activos(),
        };

        $texto = trim((string) ($filtros['texto'] ?? ''));
        if ($texto !== '') {
            $this->filtrarTexto($query, $texto, $alcance->rrhh);
        }

        foreach (['oficina_id' => 'oficina_id', 'situacion_laboral_id' => 'situacion_laboral_id', 'categoria_id' => 'categoria_titular_id'] as $filtro => $columna) {
            if (! empty($filtros[$filtro])) {
                $query->where('empleados.'.$columna, (int) $filtros[$filtro]);
            }
        }

        return $query->orderBy('empleados.apellido')->orderBy('empleados.nombre')->paginate($porPagina);
    }

    public function ficha(Alcance $alcance, int $empleadoId): array
    {
        $nivel = $this->nivelDe($alcance, $empleadoId);
        $propio = $empleadoId === $alcance->empleadoId;
        $personal = $nivel->incluye(NivelDatoRrhh::Personal);

        $empleado = Empleado::conNivel($nivel)->with(self::RELACIONES)->findOrFail($empleadoId);

        $familia = collect();
        if ($personal) {
            $parentescos = Parentesco::pluck('nombre', 'id');
            $familia = Familiar::where('empleado_id', $empleadoId)
                ->where(fn ($q) => $q->where('habilitado', true)->orWhereNull('habilitado'))
                ->get($nivel->columnasFamiliar())
                ->each(fn (Familiar $f) => $f->setAttribute('parentesco', $parentescos[$f->parentesco_id] ?? null));
        }

        if (! $propio && $personal) {
            RrhhAcceso::create(['user_id' => $alcance->userId, 'empleado_id' => $empleadoId, 'nivel' => $nivel->etiqueta()]);
        }

        return [
            'empleado' => $empleado,
            'nivel' => $nivel,
            'propio' => $propio,
            'familia' => $familia,
            'fojas' => $personal
                ? FojaServicio::where('empleado_id', $empleadoId)->orderByDesc('fecha')->get(['id', 'fecha', 'resolucion_decreto', 'nro_res_dec', 'observacion'])
                : collect(),
            'movimientos' => $personal
                ? EmpleadoMovimiento::where('empleado_id', $empleadoId)->orderByDesc('fecha')->get(['id', 'fecha', 'resolucion', 'motivo', 'tipo'])
                : collect(),
            'archivos' => $personal
                ? EmpleadoArchivo::where('empleado_id', $empleadoId)->where('habilitado', true)->orderByDesc('created_at')->get()
                : collect(),
        ];
    }

    public function archivo(Alcance $alcance, int $empleadoId, int $archivoId): ?object
    {
        if (! $this->nivelDe($alcance, $empleadoId)->incluye(NivelDatoRrhh::Personal)) {
            throw new AuthorizationException('No tenés acceso a los archivos de este legajo.');
        }

        return EmpleadoArchivo::withoutGlobalScope('sin_contenido')
            ->where('empleado_id', $empleadoId)
            ->where('habilitado', true)
            ->find($archivoId, ['id', 'nombre', 'mimetype', 'contenido']);
    }

    public function equipo(Alcance $alcance, ?int $oficinaId = null): Collection
    {
        if ($oficinaId !== null && ! $alcance->veOficina($oficinaId)) {
            throw new AuthorizationException('No tenés acceso a esa oficina.');
        }

        $query = $oficinaId !== null ? Empleado::query()->where('empleados.oficina_id', $oficinaId) : $this->visibles($alcance);

        return $query->conNivel(NivelDatoRrhh::Basico)
            ->activos()
            ->with('oficinaRrhh:id,nombre')
            ->orderBy('empleados.apellido')
            ->orderBy('empleados.nombre')
            ->get();
    }

    public function buscarRapido(Alcance $alcance, string $texto, int $limite = 5): Collection
    {
        $texto = trim($texto);
        if ((! $alcance->rrhh && ! $alcance->esJefe()) || mb_strlen($texto) < 3) {
            return collect();
        }

        $query = $this->visibles($alcance)->conNivel(NivelDatoRrhh::Basico)->activos()->with('oficinaRrhh:id,nombre');
        $this->filtrarTexto($query, $texto, false);

        return $query->orderBy('empleados.apellido')->limit($limite)->get();
    }

    public function situaciones(): Collection
    {
        return Cache::remember('rrhh.situaciones', now()->addHour(), fn () => SituacionLaboral::orderBy('nombre')->pluck('nombre', 'id'));
    }

    public function categorias(): Collection
    {
        return Cache::remember('rrhh.categorias', now()->addHour(), fn () => Categoria::orderBy('nombre')->pluck('nombre', 'id'));
    }

    /** @return Builder<Empleado> */
    private function visibles(Alcance $alcance): Builder
    {
        $query = Empleado::query();
        $oficinas = $alcance->oficinasVisibles();

        return $oficinas === null ? $query : $query->whereIn('empleados.oficina_id', $oficinas !== [] ? $oficinas : [0]);
    }

    /** Legajo exacto si son dígitos (o DNI, solo para RRHH); si no, cada palabra en apellido o nombre. */
    private function filtrarTexto(Builder $query, string $texto, bool $conDni): void
    {
        if (ctype_digit($texto)) {
            $query->where(function ($q) use ($texto, $conDni) {
                $q->where('empleados.legajo', (int) $texto);
                if ($conDni) {
                    $q->orWhere('empleados.numero_documento', $texto);
                }
            });

            return;
        }

        $like = $this->like();
        foreach (preg_split('/[\s,]+/', $texto, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
            $patron = '%'.self::escaparLike($palabra).'%';
            $query->where(fn ($q) => $q->where('empleados.apellido', $like, $patron)->orWhere('empleados.nombre', $like, $patron));
        }
    }
}
