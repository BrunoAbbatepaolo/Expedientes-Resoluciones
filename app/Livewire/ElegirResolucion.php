<?php

namespace App\Livewire;

use Livewire\Component;

class ElegirResolucion extends Component
{
    public array $tipos = [];

    public function mount()
    {
        $this->tipos = [
            ['nombre' => 'Cancelaciones'],
            ['nombre' => 'Resciciones'],
            ['nombre' => 'Transferencias'],
            ['nombre' => 'Transferencia-Cancelacion'],
            ['nombre' => 'Rectificacion'],
            ['nombre' => 'Otros'],
        ];
    }

    public function seleccionar($tipo)
    {
        // Redirigí a una vista de creación con ese prototipo
        return redirect()->route('resoluciones.crear', ['tipo' => $tipo]);
    }

    public function render()
    {
        return view('livewire.elegir-resolucion');
    }
}
