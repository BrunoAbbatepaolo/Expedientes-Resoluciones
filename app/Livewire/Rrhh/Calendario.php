<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Calendario as CalendarioRrhh;
use App\Services\Rrhh\Hora;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Feriados y días no laborables del año (lo ve cualquier usuario). */
#[Title('Calendario')]
class Calendario extends Component
{
    use ConAlcance;

    #[Url]
    public int $anio = 0;

    public function mount(): void
    {
        if ($this->anio < 2000 || $this->anio > 2100) {
            $this->anio = Hora::hoy()->year;
        }
    }

    public function render(CalendarioRrhh $calendario): View
    {
        $feriados = $this->consultar(fn () => $calendario->feriados($this->anio), collect());

        return view('livewire.rrhh.calendario', [
            'porMes' => $feriados->groupBy(fn ($f) => $f->desde->month),
            'error' => $this->errorFuente,
        ]);
    }
}
