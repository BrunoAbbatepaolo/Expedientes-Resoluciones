<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Hora;
use App\Services\Rrhh\Organigrama;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Elección de parámetros de los reportes de RRHH (se abren en una pestaña para imprimir o bajar CSV). */
#[Title('Reportes')]
class Reportes extends Component
{
    use ConAlcance;

    public string $legajo = '';

    public ?int $oficinaPresentismo = null;

    public string $mes = '';

    public ?int $oficinaAdeudadas = null;

    public ?int $oficinaLicencias = null;

    public ?int $oficinaInasistencias = null;

    public int $mesCumple = 1;

    public string $al = '';

    public ?int $situacionAntiguedad = null;

    public string $estadoPersonal = 'activos';

    public ?int $oficinaPersonal = null;

    public string $desde = '';

    public string $hasta = '';

    public function mount(): void
    {
        $this->exigir($this->alcance()->rrhh);
        $hoy = Hora::hoy();
        $this->mes = $hoy->subMonth()->format('Y-m');
        $this->desde = $hoy->startOfYear()->toDateString();
        $this->hasta = $hoy->toDateString();
        $this->mesCumple = $hoy->month;
        $this->al = $hoy->toDateString();
    }

    public function render(Organigrama $organigrama, Legajos $legajos): View
    {
        return view('livewire.rrhh.reportes', [
            'oficinas' => $this->consultar(fn () => $organigrama->oficinas()->pluck('nombre', 'id'), collect()),
            'situaciones' => $this->consultar(fn () => $legajos->situaciones(), collect()),
            'error' => $this->errorFuente,
        ]);
    }
}
