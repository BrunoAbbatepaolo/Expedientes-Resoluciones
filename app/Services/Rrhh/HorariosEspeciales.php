<?php

namespace App\Services\Rrhh;

use App\Models\Mitiv\Horario;
use App\Models\RrhhHorarioEspecial;
use App\Services\Rrhh\Mitiv\ConsultaMitiv;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Horarios especiales: se guardan en SiRex (no en el sistema viejo) y solo los modifica
 * quien tiene el permiso rrhh_horarios (jefatura de RRHH). Los ve quien ve al empleado.
 */
class HorariosEspeciales
{
    use ConsultaMitiv;

    /** Horario semanal cargado en el sistema viejo. @return Collection<int, Horario> por día (1–7) */
    public function semanaSistemaViejo(Alcance $alcance, int $empleadoId): Collection
    {
        $this->nivelDe($alcance, $empleadoId);

        return Horario::where('empleado_id', $empleadoId)
            ->where(fn ($q) => $q->where('habilitado', true)->orWhereNull('habilitado'))
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');
    }

    /** @return Collection<int, RrhhHorarioEspecial> */
    public function delEmpleado(Alcance $alcance, int $empleadoId): Collection
    {
        $this->nivelDe($alcance, $empleadoId);

        return RrhhHorarioEspecial::with('user:id,nombre,apellido')
            ->where('empleado_id', $empleadoId)
            ->orderByDesc('vigente_desde')
            ->get();
    }

    /**
     * Especiales que aplican en algún día del rango, por empleado (para el cálculo).
     *
     * @param  list<int>  $empleados
     * @return Collection<array-key, mixed> empleado => colección de RrhhHorarioEspecial
     */
    public function vigentesEntre(array $empleados, CarbonImmutable $desde, CarbonImmutable $hasta): Collection
    {
        if ($empleados === []) {
            return collect();
        }

        return RrhhHorarioEspecial::whereIn('empleado_id', $empleados)
            ->entre($desde->toDateString(), $hasta->toDateString())
            ->get()
            ->groupBy('empleado_id');
    }

    /** @param  array<string, mixed>  $datos */
    public function crear(Alcance $alcance, int $empleadoId, array $datos): RrhhHorarioEspecial
    {
        $this->exigirPermiso($alcance);
        $this->nivelDe($alcance, $empleadoId);

        $v = Validator::make($datos, [
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['integer', 'between:1,7'],
            'desde_maniana' => ['required', 'date_format:H:i'],
            'hasta_maniana' => ['required', 'date_format:H:i', 'after:desde_maniana'],
            'desde_tarde' => ['nullable', 'date_format:H:i', 'required_with:hasta_tarde'],
            'hasta_tarde' => ['nullable', 'date_format:H:i', 'required_with:desde_tarde', 'after:desde_tarde'],
            'tolerancia' => ['nullable', 'integer', 'between:0,60'],
            'vigente_desde' => ['required', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
            'motivo' => ['required', 'string', 'max:255'],
        ], [], [
            'dias' => 'días', 'desde_maniana' => 'entrada', 'hasta_maniana' => 'salida',
            'desde_tarde' => 'entrada de la tarde', 'hasta_tarde' => 'salida de la tarde',
            'vigente_desde' => 'vigente desde', 'vigente_hasta' => 'vigente hasta',
        ])->validate();

        $dias = array_values(array_unique(array_map('intval', $v['dias'])));
        $superpuesto = RrhhHorarioEspecial::where('empleado_id', $empleadoId)
            ->entre($v['vigente_desde'], $v['vigente_hasta'] ?? '9999-12-31')
            ->get()
            ->contains(fn (RrhhHorarioEspecial $h) => array_intersect($dias, array_map('intval', $h->dias)) !== []);
        if ($superpuesto) {
            throw ValidationException::withMessages(['dias' => 'Ya hay un horario especial para alguno de esos días en ese período. Finalizalo antes de cargar otro.']);
        }

        return RrhhHorarioEspecial::create([
            'empleado_id' => $empleadoId,
            'dias' => $dias,
            'desde_maniana' => $v['desde_maniana'],
            'hasta_maniana' => $v['hasta_maniana'],
            'desde_tarde' => $v['desde_tarde'] ?? null,
            'hasta_tarde' => $v['hasta_tarde'] ?? null,
            'tolerancia' => $v['tolerancia'] ?? null,
            'vigente_desde' => $v['vigente_desde'],
            'vigente_hasta' => $v['vigente_hasta'] ?? null,
            'motivo' => trim($v['motivo']),
            'user_id' => $alcance->userId,
        ]);
    }

    /** Termina un horario especial: si todavía no empezó se borra; si no, vence ayer. */
    public function finalizar(Alcance $alcance, int $horarioId): void
    {
        $this->exigirPermiso($alcance);
        $horario = RrhhHorarioEspecial::findOrFail($horarioId);
        $hoy = Hora::hoy();

        if ($horario->vigente_desde->toDateString() >= $hoy->toDateString()) {
            $horario->delete();

            return;
        }

        $horario->update(['vigente_hasta' => $hoy->subDay()->toDateString()]);
    }

    private function exigirPermiso(Alcance $alcance): void
    {
        if (! $alcance->horarios) {
            throw new AuthorizationException('Solo la jefatura de RRHH puede modificar horarios.');
        }
    }
}
