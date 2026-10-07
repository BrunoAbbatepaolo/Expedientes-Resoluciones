<?php

namespace App\Services\Rrhh\Contracts;

use App\Models\Mitiv\Feriado;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/** Feriados y días no laborables. */
interface Calendario
{
    /** @return Collection<int, Feriado> */
    public function feriados(int $anio): Collection;

    /** Días no laborables del rango. @return array<string, string> Y-m-d => tipo */
    public function dias(CarbonImmutable $desde, CarbonImmutable $hasta): array;

    public function proximo(CarbonImmutable $desde): ?Feriado;
}
