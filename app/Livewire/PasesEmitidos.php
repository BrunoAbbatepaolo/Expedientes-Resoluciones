<?php

namespace App\Livewire;

use App\Services\ExpedientesIpv;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Informe "Pases emitidos": expedientes que salieron de una oficina (o de todas) en un período, con
 * origen, destino y quién hizo el pase. Fuente: mitivdb (solo lectura). Se puede imprimir o bajar en CSV.
 */
#[Title('Pases emitidos')]
class PasesEmitidos extends Component
{
    use AuthorizesOficina;
    use WithPagination;

    #[Url(as: 'origen', except: null)]
    public ?int $origenId = null;

    #[Url(except: '')]
    public string $desde = '';

    #[Url(except: '')]
    public string $hasta = '';

    public function mount(): void
    {
        $this->autorizarPermiso('expediente_ver');

        if ($this->desde === '' && $this->hasta === '') {
            $this->desde = now()->startOfMonth()->toDateString();
            $this->hasta = now()->toDateString();
        }
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['origenId', 'desde', 'hasta'], true)) {
            $this->resetPage();
        }
    }

    public function render(ExpedientesIpv $ipv): View
    {
        $this->autorizarPermiso('expediente_ver');

        $oficinas = collect();
        $filas = null;
        $truncado = false;
        $error = false;
        $aviso = ExpedientesIpv::errorDePeriodo($this->desde, $this->hasta);

        try {
            $oficinas = $ipv->oficinas();

            if ($aviso === null) {
                ['filas' => $todas, 'truncado' => $truncado] = $ipv->pasesEmitidos($this->origenId, $this->desde, $this->hasta);
                $pagina = $this->getPage();
                $filas = new LengthAwarePaginator($todas->forPage($pagina, 25)->values(), $todas->count(), 25, $pagina, ['path' => request()->url()]);
            }
        } catch (\Throwable $e) {
            report($e);
            $error = true;
        }

        return view('livewire.pases-emitidos', compact('oficinas', 'filas', 'truncado', 'aviso', 'error'));
    }
}
