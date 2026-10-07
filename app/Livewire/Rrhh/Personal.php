<?php

namespace App\Livewire\Rrhh;

use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Services\Rrhh\Contracts\Legajos;
use App\Services\Rrhh\Organigrama;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Listado de todo el personal (RRHH) con búsqueda y filtros. */
#[Title('Personal')]
class Personal extends Component
{
    use ConAlcance;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $texto = '';

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

    #[Url(as: 'situacion', except: null)]
    public ?int $situacionId = null;

    #[Url(as: 'categoria', except: null)]
    public ?int $categoriaId = null;

    #[Url(except: 'activos')]
    public string $estado = 'activos';

    public function mount(): void
    {
        $this->exigir($this->alcance()->rrhh);
    }

    public function updated(string $propiedad): void
    {
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset(['texto', 'oficinaId', 'situacionId', 'categoriaId', 'estado']);
        $this->resetPage();
    }

    public function render(Legajos $legajos, Organigrama $organigrama): View
    {
        $alcance = $this->alcance();

        $datos = $this->consultar(fn () => [
            'empleados' => $legajos->buscar($alcance, [
                'texto' => $this->texto,
                'oficina_id' => $this->oficinaId,
                'situacion_laboral_id' => $this->situacionId,
                'categoria_id' => $this->categoriaId,
                'estado' => $this->estado,
            ]),
            'oficinas' => $organigrama->oficinas(),
            'situaciones' => $legajos->situaciones(),
            'categorias' => $legajos->categorias(),
        ], ['empleados' => null, 'oficinas' => collect(), 'situaciones' => collect(), 'categorias' => collect()]);

        return view('livewire.rrhh.personal', $datos + ['error' => $this->errorFuente]);
    }
}
