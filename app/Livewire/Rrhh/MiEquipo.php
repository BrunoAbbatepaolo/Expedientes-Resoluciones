<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Livewire\Rrhh\Concerns\NavegaMeses;
use App\Services\Rrhh\Contracts\Asistencias;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Hora;
use App\Services\Rrhh\Organigrama;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Para jefes: quién vino hoy, la grilla del mes y la gente de su oficina y de las
 * oficinas que dependen de ella.
 */
#[Title('Mi equipo')]
class MiEquipo extends Component
{
    use ConAlcance;
    use NavegaMeses;

    public const VISTAS = ['hoy' => 'Hoy', 'mes' => 'Mes', 'gente' => 'Mi gente'];

    #[Url]
    public string $vista = 'hoy';

    #[Url(as: 'oficina')]
    public ?int $oficinaId = null;

    public function mount(): void
    {
        $alcance = $this->alcance();
        $this->exigir($alcance->esJefe());

        if (! array_key_exists($this->vista, self::VISTAS)) {
            $this->vista = 'hoy';
        }
        if ($this->oficinaId !== null && ! $alcance->veOficina($this->oficinaId)) {
            $this->oficinaId = null;
        }
    }

    public function render(Asistencias $asistencias, Legajos $legajos, Organigrama $organigrama): View
    {
        $alcance = $this->alcance();
        $oficinas = $this->consultar(fn () => $organigrama->oficinas()->whereIn('id', $alcance->oficinasEquipo)->values(), collect());
        $mes = $this->mesActual();
        $datos = [];

        if (! $this->errorFuente) {
            $datos = $this->consultar(fn () => match ($this->vista) {
                'mes' => ['grilla' => $asistencias->grilla($alcance, $mes->year, $mes->month, $this->oficinaId ?? (int) $oficinas->first()?->id)],
                'gente' => ['gente' => $legajos->equipo($alcance, $this->oficinaId)],
                default => ['hoy' => $asistencias->dia($alcance, Hora::hoy(), $this->oficinaId)],
            }, []);
        }

        return view('livewire.rrhh.mi-equipo', $datos + [
            'oficinas' => $oficinas,
            'mesActual' => $mes,
            'error' => $this->errorFuente,
        ]);
    }
}
