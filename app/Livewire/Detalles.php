<?php

namespace App\Livewire;

use App\Models\Expediente;
use App\Traits\AuthorizesOficina;
use Livewire\Component;

class Detalles extends Component
{
    use AuthorizesOficina;

    public $id;

    public $expediente;

    public $pases = [];

    public function mount($id)
    {
        $this->id = $id;
        $this->expediente = Expediente::with([
            'pases.oficina',
            'pases.oficinaOrigen',
            'oficinaById',
        ])->findOrFail($id);
        $this->autorizarExpediente($this->expediente, 'expediente_ver');

        $this->pases = $this->expediente->pases->map(fn ($pase) => [
            'origen' => $pase->oficinaOrigen->nombre ?? 'Sin especificar',
            'destino' => $pase->oficina->nombre ?? 'Sin especificar',
            'fecha' => $pase->fecha,
            'hora' => $pase->hora,
            'observacion' => $pase->observacion,
            'importado' => $pase->importado,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.detalles');
    }
}
