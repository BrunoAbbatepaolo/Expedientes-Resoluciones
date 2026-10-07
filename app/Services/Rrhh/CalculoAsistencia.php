<?php

namespace App\Services\Rrhh;

use App\Enums\EstadoDia;
use Carbon\CarbonImmutable;

/**
 * Estado de asistencia de un día, sin acceso a datos (todo entra por parámetro).
 *
 * Reemplaza la lógica del proceso de reloj del sistema viejo para mostrar: evalúa los
 * dos turnos y usa tolerancias configurables. Lo que el viejo ya registró como
 * inasistencia es el dato oficial y manda sobre lo calculado.
 */
class CalculoAsistencia
{
    public function __construct(
        private int $toleranciaDefault,
        private int $tipoAusente,
        private int $tipoTarde,
    ) {}

    /**
     * @param  object|null  $horario  fila de horarios (desde/hasta/tolerancia de mañana y tarde)
     * @param  list<CarbonImmutable>  $marcas  fichadas del día, en orden
     * @param  object|null  $inasistencia  {tipo_inasistencia_id, nombre}
     */
    public function dia(
        CarbonImmutable $fecha,
        ?object $horario,
        array $marcas,
        bool $feriado,
        ?string $licencia,
        ?object $inasistencia,
        CarbonImmutable $ahora,
    ): DiaAsistencia {
        $fecha = $fecha->startOfDay();
        $horas = array_map(fn (CarbonImmutable $m) => $m->format('H:i'), $marcas);
        $texto = $this->textoHorario($horario);
        $nuevo = fn (EstadoDia $estado, string $motivo = '', int $tarde = 0) => new DiaAsistencia($fecha, $estado, $horas, $tarde, $motivo, $texto);

        if ($fecha->gt($ahora->startOfDay())) {
            return $nuevo(EstadoDia::Futuro);
        }

        if ($inasistencia !== null) {
            return match ((int) $inasistencia->tipo_inasistencia_id) {
                $this->tipoAusente => $nuevo(EstadoDia::Falta, 'Ausente (registrado por RRHH)'),
                $this->tipoTarde => $nuevo(EstadoDia::Tarde, 'Llegada tarde (registrada por RRHH)', $this->minutosTarde($fecha, $horario, $marcas)),
                default => $nuevo(EstadoDia::Justificado, (string) $inasistencia->nombre),
            };
        }

        if ($licencia !== null) {
            return $nuevo(EstadoDia::Licencia, $licencia);
        }

        if ($feriado) {
            return $nuevo(EstadoDia::Feriado, 'Feriado o día no laborable');
        }

        [$entrada, $tolerancia] = $this->entrada($fecha, $horario);
        if ($entrada === null) {
            return $nuevo(EstadoDia::Franco, 'Sin horario asignado para este día');
        }

        $limite = $entrada->addMinutes($tolerancia);
        if ($marcas === []) {
            return $fecha->isSameDay($ahora) && $ahora->lte($limite)
                ? $nuevo(EstadoDia::Pendiente, 'Todavía dentro del horario de entrada')
                : $nuevo(EstadoDia::Falta, 'Sin fichadas (no registrado aún por RRHH)');
        }

        if ($marcas[0]->gt($limite)) {
            $minutos = (int) $entrada->diffInMinutes($marcas[0]);

            return $nuevo(EstadoDia::Tarde, "Entró {$minutos} min después de las {$entrada->format('H:i')}", $minutos);
        }

        if ($this->faltaTurnoTarde($fecha, $horario, $marcas, $ahora)) {
            return $nuevo(EstadoDia::Incompleto, 'Sin fichada del turno tarde');
        }

        return $nuevo(EstadoDia::Presente);
    }

    /** @return array{0: CarbonImmutable|null, 1: int} */
    private function entrada(CarbonImmutable $fecha, ?object $horario): array
    {
        if ($horario === null) {
            return [null, 0];
        }

        [$hora, $tol] = ! empty($horario->desde_maniana)
            ? [$horario->desde_maniana, (int) ($horario->tolerancia_maniana ?? 0)]
            : [$horario->desde_tarde ?? null, (int) ($horario->tolerancia_tarde ?? 0)];

        if (empty($hora)) {
            return [null, 0];
        }

        return [$fecha->setTimeFromTimeString($hora), $tol > 0 ? $tol : $this->toleranciaDefault];
    }

    /** @param  list<CarbonImmutable>  $marcas */
    private function minutosTarde(CarbonImmutable $fecha, ?object $horario, array $marcas): int
    {
        [$entrada] = $this->entrada($fecha, $horario);

        return $entrada !== null && $marcas !== [] && $marcas[0]->gt($entrada)
            ? (int) $entrada->diffInMinutes($marcas[0])
            : 0;
    }

    /**
     * Turno partido (sale a la mañana y vuelve a la tarde): tiene que haber alguna fichada
     * desde el fin del turno mañana, una vez vencida la entrada de la tarde.
     *
     * @param  list<CarbonImmutable>  $marcas
     */
    private function faltaTurnoTarde(CarbonImmutable $fecha, ?object $horario, array $marcas, CarbonImmutable $ahora): bool
    {
        if (empty($horario->desde_maniana) || empty($horario->hasta_maniana) || empty($horario->desde_tarde)
            || $horario->hasta_maniana >= $horario->desde_tarde) {
            return false;
        }

        $finManiana = $fecha->setTimeFromTimeString($horario->hasta_maniana);
        $tol = (int) ($horario->tolerancia_tarde ?? 0) ?: $this->toleranciaDefault;
        if ($ahora->lte($fecha->setTimeFromTimeString($horario->desde_tarde)->addMinutes($tol))) {
            return false;
        }

        foreach ($marcas as $m) {
            if ($m->gt($finManiana->addMinutes(30))) {
                return false;
            }
        }

        return true;
    }

    private function textoHorario(?object $horario): ?string
    {
        if ($horario === null) {
            return null;
        }

        $tramos = [];
        foreach ([['desde_maniana', 'hasta_maniana'], ['desde_tarde', 'hasta_tarde']] as [$d, $h]) {
            if (! empty($horario->{$d})) {
                $tramos[] = substr($horario->{$d}, 0, 5).'–'.substr((string) ($horario->{$h} ?? ''), 0, 5);
            }
        }

        return $tramos !== [] ? implode(' y ', $tramos) : null;
    }
}
