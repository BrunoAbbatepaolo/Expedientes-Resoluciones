<?php

namespace App\Livewire;

use App\Models\Expediente as ExpedienteOficina;
use App\Models\Resolucion;
use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ficha de un expediente del IPV: carátula, ubicación actual, línea de tiempo de
 * pases, relacionados y lo que SiRex sabe de él (registro en oficinas, resoluciones).
 */
#[Lazy]
class FichaIpv extends Component
{
    use AuthorizesOficina;

    #[Locked]
    public int $expedienteId;

    public function mount(int $id): void
    {
        $this->autorizarPermiso('expediente_ver');
        $this->expedienteId = $id;
    }

    public function placeholder(): View
    {
        return view('components.skeleton.ficha');
    }

    public function render(ExpedientesIpv $ipv): View
    {
        try {
            $ficha = $ipv->ficha($this->expedienteId);
        } catch (\Throwable $e) {
            report($e);

            return view('livewire.ficha-ipv', ['ficha' => null, 'error' => true]);
        }

        abort_if($ficha === null, 404);

        $numero = $ficha['expediente']->numero;

        return view('livewire.ficha-ipv', [
            'ficha' => $ficha,
            'error' => false,
            'registros' => ExpedienteOficina::with('oficinaById')->where('num_exp', $numero)->get(),
            'resoluciones' => auth()->user()->permiso('resolucion_ver')
                ? Resolucion::with('archivos')->where('numero_exp', $numero)->latest('fecha')->get()
                : collect(),
        ])->title('Expediente '.$numero);
    }
}
