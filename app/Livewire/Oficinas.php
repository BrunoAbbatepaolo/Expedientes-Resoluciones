<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Oficinas extends Component
{
    use WithPagination;

    public $BusquedaOficina = '';
    public $inputBusqueda = '';

    public function render()
    {
        $oficinas = $this->cargarOficinas();
        return view('livewire.oficinas', [
            'oficinas' => $oficinas
        ]);
    }

    public function cargarOficinas()
    {
        $query = \App\Models\Oficina::query();
        // Tus filtros existentes
        if (!empty($this->inputBusqueda)) {
            $query->where(function ($q) {
                $q->where('nombre', 'LIKE', '%' . $this->inputBusqueda . '%');
            });
        }

        return $query->orderBy('nombre', 'asc')->paginate(20);
    }
    public function inputBusqueda() {}
}
