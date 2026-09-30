<?php

namespace App\Livewire;

use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pestaña "Expedientes IPV": consulta de solo lectura de todos los expedientes del
 * instituto (fuente: sistema viejo / mitivdb). Los pases oficiales se siguen haciendo allá.
 */
#[Lazy]
#[Title('Expedientes IPV')]
class ConsultaIpv extends Component
{
    use AuthorizesOficina;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $texto = '';

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    public function mount(): void
    {
        $this->autorizarPermiso('expediente_ver');
    }

    public function placeholder(): View
    {
        return view('livewire.consulta-ipv-placeholder');
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['texto', 'oficinaId', 'desde', 'hasta'], true)) {
            $this->resetPage();
        }
    }

    public function limpiar(): void
    {
        $this->reset(['texto', 'oficinaId', 'desde', 'hasta']);
        $this->resetPage();
    }

    public function render(ExpedientesIpv $ipv): View
    {
        $expedientes = null;
        $oficinas = collect();
        $error = false;

        try {
            $oficinas = $ipv->oficinas();
            $expedientes = $ipv->buscar([
                'texto' => $this->texto,
                'oficina_id' => $this->oficinaId,
                'desde' => $this->desde ?: null,
                'hasta' => $this->hasta ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            $error = true;
        }

        return view('livewire.consulta-ipv', compact('expedientes', 'oficinas', 'error'));
    }
}
