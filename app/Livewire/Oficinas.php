<?php

namespace App\Livewire;

use App\Models\Oficina;
use App\Services\ExpedientesIpv;
use App\Traits\NotificaUi;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Directorio de oficinas: cada oficina de SiRex, su oficina vinculada en el sistema
 * del IPV (la usa el tablero y las alertas de demora) y su personal activo.
 */
class Oficinas extends Component
{
    use NotificaUi;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public $inputBusqueda = '';

    /** Oficina de SiRex cuyo personal se está viendo. */
    #[Locked]
    public ?int $expandida = null;

    /** Oficina de SiRex que se está vinculando con el IPV. */
    #[Locked]
    public ?int $vinculando = null;

    public string $buscarIpv = '';

    public function updatedInputBusqueda(): void
    {
        $this->resetPage();
    }

    public function alternarPersonal(int $id): void
    {
        $this->expandida = $this->expandida === $id ? null : $id;
    }

    public function vincular(int $id): void
    {
        abort_unless((bool) auth()->user()->permiso('lista_usuario_editar'), 403);

        $this->vinculando = $id;
        $this->buscarIpv = (string) Oficina::whereKey($id)->value('nombre');
    }

    public function cancelarVinculo(): void
    {
        $this->reset(['vinculando', 'buscarIpv']);
    }

    public function guardarVinculo(?int $mitivOficinaId, ExpedientesIpv $ipv): void
    {
        abort_unless((bool) auth()->user()->permiso('lista_usuario_editar'), 403);

        if ($mitivOficinaId !== null && ! $ipv->oficinas()->contains('id', $mitivOficinaId)) {
            $this->toast('error', 'Esa oficina no existe en el sistema del IPV.');

            return;
        }

        Oficina::whereKey($this->vinculando)->update(['mitiv_oficina_id' => $mitivOficinaId]);
        $this->cancelarVinculo();
        $this->toast('success', $mitivOficinaId ? 'Oficina vinculada con el IPV' : 'Vínculo quitado');
    }

    /**
     * Oficinas del IPV cuyo nombre contiene todas las palabras buscadas.
     */
    private function sugerenciasIpv(ExpedientesIpv $ipv): Collection
    {
        $palabras = collect(preg_split('/\s+/', mb_strtolower(trim($this->buscarIpv))))
            ->filter(fn ($p) => mb_strlen($p) >= 3);

        return $ipv->oficinas()
            ->filter(fn ($o) => $palabras->every(fn ($p) => str_contains(mb_strtolower($o->nombre), $p)))
            ->take(8)
            ->values();
    }

    public function render(ExpedientesIpv $ipv)
    {
        $oficinas = Oficina::query()
            ->when($this->inputBusqueda !== '', fn ($q) => $q->where('nombre', 'LIKE', '%'.$this->inputBusqueda.'%'))
            ->orderBy('nombre')
            ->paginate(20);

        $nombresIpv = rescue(fn () => $ipv->oficinas()->pluck('nombre', 'id'), collect());
        $expandidaIpv = $this->expandida ? $oficinas->firstWhere('id', $this->expandida)?->mitiv_oficina_id : null;

        return view('livewire.oficinas', [
            'oficinas' => $oficinas,
            'nombresIpv' => $nombresIpv,
            'personal' => $expandidaIpv ? rescue(fn () => $ipv->personal($expandidaIpv), collect()) : collect(),
            'sugerencias' => $this->vinculando ? rescue(fn () => $this->sugerenciasIpv($ipv), collect()) : collect(),
            'puedeEditar' => (bool) auth()->user()->permiso('lista_usuario_editar'),
        ]);
    }
}
