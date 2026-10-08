<?php

namespace App\Services\Rrhh\Mitiv;

use App\Models\Mitiv\Feriado;
use App\Services\Rrhh\Contracts\Calendario;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CalendarioMitiv implements Calendario
{
    /** El sistema viejo no descuenta este tipo de feriado al contar días hábiles (dias_habiles1). */
    private const TIPO_NO_DESCUENTA = 4;

    public function feriados(int $anio): Collection
    {
        return Cache::remember("rrhh.feriados.$anio", now()->addHour(), fn () => Feriado::query()
            ->where('desde', '<=', "$anio-12-31 23:59:59")
            ->where('hasta', '>=', "$anio-01-01 00:00:00")
            ->orderBy('desde')
            ->get(['id', 'desde', 'hasta', 'tipo', 'tipo_feriado_id']));
    }

    public function dias(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        return $this->expandir($desde, $hasta, fn () => true);
    }

    public function diasNoHabiles(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        return $this->expandir($desde, $hasta, fn (Feriado $f) => (int) $f->tipo_feriado_id !== self::TIPO_NO_DESCUENTA);
    }

    public function proximo(CarbonImmutable $desde): ?Feriado
    {
        return $this->feriados($desde->year)
            ->concat($this->feriados($desde->year + 1))
            ->first(fn (Feriado $f) => $f->hasta->gte($desde->startOfDay()));
    }

    /** @return array<string, string> */
    private function expandir(CarbonImmutable $desde, CarbonImmutable $hasta, callable $incluir): array
    {
        $dias = [];
        foreach (range($desde->year, $hasta->year) as $anio) {
            foreach ($this->feriados($anio)->filter($incluir) as $feriado) {
                foreach (CarbonPeriod::create($feriado->desde->startOfDay(), $feriado->hasta->startOfDay()) as $dia) {
                    if ($dia->betweenIncluded($desde->startOfDay(), $hasta->endOfDay())) {
                        $dias[$dia->toDateString()] = (string) ($feriado->tipo ?: 'Feriado');
                    }
                }
            }
        }

        return $dias;
    }
}
