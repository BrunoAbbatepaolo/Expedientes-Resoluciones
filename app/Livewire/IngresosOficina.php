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
 * Informe "Ingresos a una oficina": expedientes que entraron a una oficina en un período, sin
 * importar dónde están hoy. Fuente: pases de mitivdb (solo lectura). Se puede bajar en CSV.
 */
#[Title('Ingresos a oficina')]
class IngresosOficina extends Component
{
    use AuthorizesOficina;
    use WithPagination;

    #[Url(as: 'oficina', except: null)]
    public ?int $oficinaId = null;

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
        if (in_array($propiedad, ['oficinaId', 'desde', 'hasta'], true)) {
            $this->resetPage();
        }
    }

    public function render(ExpedientesIpv $ipv): View
    {
        // Cada request revalida el permiso (lo pueden quitar con la pantalla abierta).
        $this->autorizarPermiso('expediente_ver');

        $oficinas = collect();
        $filas = null;
        $truncado = false;
        $aviso = null;
        $error = false;

        try {
            $oficinas = $ipv->oficinas();

            if ($this->oficinaId) {
                $aviso = ExpedientesIpv::errorDePeriodo($this->desde, $this->hasta);

                if ($aviso === null) {
                    ['filas' => $todas, 'truncado' => $truncado] = $ipv->ingresosAOficina($this->oficinaId, $this->desde, $this->hasta);
                    $pagina = $this->getPage();
                    $filas = new LengthAwarePaginator($todas->forPage($pagina, 25)->values(), $todas->count(), 25, $pagina, ['path' => request()->url()]);
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $error = true;
        }

        return view('livewire.ingresos-oficina', compact('oficinas', 'filas', 'truncado', 'aviso', 'error'));
    }
}
