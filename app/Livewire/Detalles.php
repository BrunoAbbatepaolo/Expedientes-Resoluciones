<?php

namespace App\Livewire;

use App\Models\Expediente;
use App\Models\Oficina;

class Detalles extends Component
{
    public $id;

    public $expediente;
    public $pases = [];

    public function mount($id)
    {
        $this->id = $id;
        $this->expediente = Expediente::with([
            'pases.oficina',
            'pases.oficinaOrigen',
            'oficinaById'
        ])->findOrFail($id);

        // Primer pase (siempre existe)
        $this->pases[] = [
            'oficina' => 'Computos',
            'fecha' => $this->expediente->fecha_ingreso,
            'observaciones' => 'Ingreso a la Oficina'
        ];

        // Pase de salida si existe
        $paseSalida = null;
        if ($this->expediente->fecha_salida && $this->expediente->ofi_salida) {
            $oficina = Oficina::where('codigo', $this->expediente->ofi_salida)->first();

            $this->pases[] = [
                'oficina' => $this->expediente->oficina->nombre ?? 'Oficina desconocida',
                'fecha' => $this->expediente->fecha_salida,
                'observaciones' => 'Traslado de expediente'
            ];
        }
    }

    public function render()
    {
        return view('livewire.detalles');
    }
}