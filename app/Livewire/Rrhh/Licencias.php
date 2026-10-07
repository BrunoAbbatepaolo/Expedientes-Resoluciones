<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Licencias as LicenciasRrhh;
use App\Services\Rrhh\Organigrama;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Licencias de todo el personal y vacaciones adeudadas (RRHH). */
#[Title('Licencias')]
class Licencias extends Component
{
    use ConAlcance;
    use WithPagination;

    public const VISTAS = ['listado' => 'Licencias', 'adeudadas' => 'Vacaciones adeudadas'];

    #[Url]
    public string $vista = 'listado';

    #[Url(as: 'q', except: '')]
    public string $texto = '';

    #[Url(as: 'tipo', except: null)]
    public ?int $tipoId = null;

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    public function mount(): void
    {
        $this->exigir($this->alcance()->rrhh);

        if (! array_key_exists($this->vista, self::VISTAS)) {
            $this->vista = 'listado';
        }
    }

    public function updated(string $propiedad): void
    {
        $this->resetPage();
    }

    public function render(LicenciasRrhh $licencias, Organigrama $organigrama): View
    {
        $alcance = $this->alcance();

        $datos = $this->consultar(fn () => [
            'oficinas' => $organigrama->oficinas(),
            'tipos' => $licencias->tipos(),
        ] + ($this->vista === 'adeudadas'
            ? ['adeudadas' => $licencias->adeudadas($alcance, $this->oficinaId)]
            : ['licencias' => $licencias->buscar($alcance, [
                'texto' => $this->texto,
                'tipo_id' => $this->tipoId,
                'oficina_id' => $this->oficinaId,
                'desde' => $this->desde ?: null,
                'hasta' => $this->hasta ?: null,
            ])]), ['oficinas' => collect(), 'tipos' => collect()]);

        return view('livewire.rrhh.licencias', $datos + ['error' => $this->errorFuente]);
    }
}
