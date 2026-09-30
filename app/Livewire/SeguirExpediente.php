<?php

namespace App\Livewire;

use App\Models\Seguimiento;
use App\Services\ExpedientesIpv;
use App\Traits\NotificaUi;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Botón "Seguir / Siguiendo" de la ficha IPV. Al seguir, se marca el último pase como
 * visto; el tablero avisa cuando el expediente se mueve después de eso.
 */
class SeguirExpediente extends Component
{
    use NotificaUi;

    #[Locked]
    public string $numero;

    public bool $siguiendo = false;

    public function mount(string $numero): void
    {
        $this->numero = $numero;
        $this->siguiendo = Seguimiento::where('user_id', auth()->id())->where('numero_exp', $numero)->exists();
    }

    public function alternar(ExpedientesIpv $ipv): void
    {
        $actual = Seguimiento::where('user_id', auth()->id())->where('numero_exp', $this->numero)->first();

        if ($actual) {
            $actual->delete();
            $this->siguiendo = false;
            $this->toast('info', 'Dejaste de seguir el expediente');

            return;
        }

        $ubicacion = rescue(fn () => $ipv->ubicaciones([$this->numero])->get($this->numero), null, false);

        Seguimiento::create([
            'user_id' => auth()->id(),
            'numero_exp' => $this->numero,
            'ultimo_pase_visto' => $ubicacion?->ubicacion_desde,
        ]);

        $this->siguiendo = true;
        $this->toast('success', 'Te vamos a avisar en el panel cuando se mueva');
    }

    public function render(): View
    {
        return view('livewire.seguir-expediente');
    }
}
