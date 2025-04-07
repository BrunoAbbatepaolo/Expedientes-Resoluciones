<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expediente;
use Illuminate\Support\Facades\App;

use App\Models\Oficina;

class Detalles extends Component
{
    public $id;
    public $expediente;
    public $pases = [];

    public function mount($id){
        $this->id = $id;
        $this->expediente = Expediente::find($id);
        
        // Primer pase (siempre existe)
        $this->pases[] = [
            'oficina' => 'Computos',
            'fecha' => $this->expediente->fecha_ingreso,
            'observaciones' => 'Carga inicial'
        ];

        // Segundo pase (solo si hay salida)
        if($this->expediente->fecha_salida && $this->expediente->ofi_salida) {
            $oficina = Oficina::where('codigo', $this->expediente->ofi_salida)->first();
            
            $this->pases[] = [
                'oficina' => $oficina->nombre ?? 'Oficina desconocida',
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