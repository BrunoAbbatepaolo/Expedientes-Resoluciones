<?php

namespace App\Services\Rrhh;

use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use DateTimeInterface;

/**
 * Reglas de la licencia anual ordinaria, iguales a las del sistema viejo
 * (vista_antiguedad, vista_vacaciones y dias_habiles1 de mitivdb):
 *
 * - Antigüedad al cierre del período = tiempo desde el ingreso + años reconocidos
 *   (nacional, provincial, municipal), redondeada a años.
 * - Días que corresponden: primer año proporcional a los meses (20 × meses / 12);
 *   después 20 / 25 / 30 / 35 cada 5 años, tope 35.
 * - Se descuentan en días hábiles: lunes a viernes que no sean feriado.
 *
 * El período X se toma durante el año X + desfase (config sirex.rrhh_vacaciones_desfase) y
 * vence al terminar ese año: se muestra solo el período vigente.
 */
final class CalculoVacaciones
{
    /** Período cuyas vacaciones se están tomando este año. */
    public static function periodoVigente(): int
    {
        return Hora::hoy()->year - (int) config('sirex.rrhh_vacaciones_desfase');
    }

    /**
     * @param  array{anios?: int|null, meses?: int|null, dias?: int|null}  ...$reconocimientos
     * @return array{antiguedad: int, dias: int}
     */
    public static function corresponden(?DateTimeInterface $ingreso, int $periodo, array ...$reconocimientos): array
    {
        if ($ingreso === null || (int) $ingreso->format('Y') > $periodo) {
            return ['antiguedad' => 0, 'dias' => 0];
        }

        [$anio, $mes, $dia] = array_map('intval', explode('-', $ingreso->format('Y-n-j')));
        $meses = ($periodo * 12 + 12) - $anio * 12 - $mes - 1 - $dia / 30;
        foreach ($reconocimientos as $r) {
            $meses += ((int) ($r['anios'] ?? 0)) * 12 + (int) ($r['meses'] ?? 0) + intdiv((int) ($r['dias'] ?? 0), 30);
        }
        $antiguedad = (int) round($meses / 12);

        $dias = match (true) {
            $antiguedad === 0 => (int) round((12 - $mes) / 12 * 20),
            $antiguedad > 15 => 35,
            default => intdiv($antiguedad, 5) * 5 + 20,
        };

        return ['antiguedad' => $antiguedad, 'dias' => $dias];
    }

    /** @param  array<string, mixed>  $feriados  Y-m-d => algo (días no laborables) */
    public static function diasHabiles(DateTimeInterface $desde, DateTimeInterface $hasta, array $feriados): int
    {
        $dias = 0;
        foreach (CarbonPeriod::create(CarbonImmutable::instance($desde)->startOfDay(), CarbonImmutable::instance($hasta)->startOfDay()) as $d) {
            if ($d->isWeekday() && ! isset($feriados[$d->toDateString()])) {
                $dias++;
            }
        }

        return $dias;
    }
}
