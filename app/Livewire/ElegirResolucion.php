<?php

namespace App\Livewire;

use App\Services\Resoluciones\Tipos;
use App\Traits\AuthorizesOficina;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ElegirResolucion extends Component
{
    use AuthorizesOficina;

    public array $tipos = [];

    public string $busqueda = '';

    public function mount()
    {
        $this->autorizarPermiso('resolucion_editar');

        $tipos = array_map(fn (string $t) => ['nombre' => $t, 'display' => Tipos::nombre($t)], [...Tipos::TODOS, 'Otros']);

        // 6 de los 9 tipos no tienen plantilla en resources/views/prototipos/ todavía;
        // se muestran igual (el modo "Personalizado" no depende de la plantilla) pero
        // marcados para no sorprender al usuario. Ver implementacion_futuro.md 1.3.
        $this->tipos = array_map(
            fn (array $tipo) => $tipo + ['plantillaDisponible' => View::exists("prototipos.{$tipo['nombre']}")],
            $tipos
        );
    }

    #[Computed]
    public function getTiposFiltradosProperty(): array
    {
        if (empty($this->busqueda)) {
            return $this->tipos;
        }

        return array_filter($this->tipos, function ($tipo) {
            return str_contains(
                mb_strtolower($tipo['nombre'].' '.$tipo['display']),
                mb_strtolower($this->busqueda)
            );
        });
    }

    public function seleccionar($tipo)
    {
        return redirect()->route('resoluciones.crear', ['tipo' => $tipo]);
    }

    public function render()
    {
        return view('livewire.elegir-resolucion');
    }
}
