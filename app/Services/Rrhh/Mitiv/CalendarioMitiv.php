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
    public function feriados(int $anio): Collection
    {
        return Cache::remember("rrhh.feriados.$anio", now()->addHour(), fn () => Feriado::query()
            ->where('desde', '<=', "$anio-12-31 23:59:59")
            ->where('hasta', '>=', "$anio-01-01 00:00:00")
            ->orderBy('desde')
            ->get(['id', 'desde', 'hasta', 'tipo']));
    }

    public function dias(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $dias = [];
        foreach (range($desde->year, $hasta->year) as $anio) {
            foreach ($this->feriados($anio) as $feriado) {
                foreach (CarbonPeriod::create($feriado->desde->startOfDay(), $feriado->hasta->startOfDay()) as $dia) {
                    if ($dia->betweenIncluded($desde->startOfDay(), $hasta->endOfDay())) {
                        $dias[$dia->toDateString()] = (string) ($feriado->tipo ?: 'Feriado');
                    }
                }
            }
        }

        return $dias;
    }

    public function proximo(CarbonImmutable $desde): ?Feriado
    {
        return $this->feriados($desde->year)
            ->concat($this->feriados($desde->year + 1))
            ->first(fn (Feriado $f) => $f->hasta->gte($desde->startOfDay()));
    }
}
