<?php

namespace App\Livewire\Rrhh\Concerns;

use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Url;

/** Mes elegido en pantalla (Y-m en la URL) con flechas de anterior/siguiente. */
trait NavegaMeses
{
    #[Url]
    public string $mes = '';

    public function mountNavegaMeses(): void
    {
        $this->mes = $this->mesActual()->format('Y-m');
    }

    public function moverMes(int $delta): void
    {
        $this->mes = $this->mesActual()->addMonths($delta)->format('Y-m');
    }

    protected function mesActual(): CarbonImmutable
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->mes)
            ? CarbonImmutable::createFromFormat('!Y-m', $this->mes)
            : Hora::hoy()->startOfMonth();
    }
}
