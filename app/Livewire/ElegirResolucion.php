<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;

class ElegirResolucion extends Component
{
    public array $tipos = [];

    public string $busqueda = '';

    public function mount()
    {
        $this->tipos = [
            ['nombre' => 'Cancelaciones'],
            ['nombre' => 'Resciciones'],
            ['nombre' => 'Transferencias'],
            ['nombre' => 'Transferencia-Cancelacion'],
            ['nombre' => 'Rectificacion'],
            ['nombre' => 'AplicarPagos'],
            ['nombre' => 'ReconocimientoCuotaPagadaDosVeces', 'display' => 'Reconocimiento de Cuota Pagada Dos Veces'],
            ['nombre' => 'ReconocimientoCuotaPagadaNoCargada', 'display' => 'Recon. de cta. pagadas y no cargadas'],
            ['nombre' => 'Otros'],
        ];
    }

    #[Computed]
    public function getTiposFiltradosProperty(): array
    {
        if (empty($this->busqueda)) {
            return $this->tipos;
        }

        return array_filter($this->tipos, function ($tipo) {
            return str_contains(
                strtolower($tipo['nombre']),
                strtolower($this->busqueda)
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
