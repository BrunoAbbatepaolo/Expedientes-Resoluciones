<?php

namespace App\Livewire\Rrhh;

use App\Enums\NivelDatoRrhh;
use App\Livewire\Rrhh\Concerns\ConAlcance;
use App\Models\Mitiv\Empleado;
use App\Models\RrhhJefatura;
use App\Models\User;
use App\Services\Rrhh\MenuRrhh;
use App\Services\Rrhh\Organigrama as OrganigramaRrhh;
use App\Traits\NotificaUi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Árbol de oficinas de RRHH: de quién depende cada una y quiénes son sus jefes. Lo ve
 * RRHH; lo edita quien administra usuarios.
 */
#[Title('Organigrama')]
class Organigrama extends Component
{
    use ConAlcance;
    use NotificaUi;

    #[Locked]
    public ?int $editando = null;

    public ?int $padreId = null;

    public ?int $nuevoJefeId = null;

    public function mount(): void
    {
        $this->exigir($this->alcance()->rrhh || $this->puedeEditar());
    }

    public function editar(int $oficinaId, OrganigramaRrhh $organigrama): void
    {
        $this->exigir($this->puedeEditar());
        $this->editando = $oficinaId;
        $this->padreId = $organigrama->padres()[$oficinaId] ?? null;
        $this->nuevoJefeId = null;
    }

    public function cerrar(): void
    {
        $this->reset(['editando', 'padreId', 'nuevoJefeId']);
    }

    public function guardarPadre(OrganigramaRrhh $organigrama): void
    {
        $this->exigir($this->puedeEditar() && $this->editando !== null);

        try {
            $organigrama->asignarPadre($this->editando, $this->padreId ?: null);
            $this->toast('success', 'Dependencia guardada');
        } catch (InvalidArgumentException $e) {
            $this->addError('padreId', $e->getMessage());
        }
    }

    public function agregarJefe(): void
    {
        $this->exigir($this->puedeEditar() && $this->editando !== null);
        $this->validate(['nuevoJefeId' => 'required|integer|exists:mysql_admin.users,id'], [], ['nuevoJefeId' => 'jefe']);

        RrhhJefatura::firstOrCreate(['user_id' => $this->nuevoJefeId, 'mitiv_oficina_id' => $this->editando]);
        MenuRrhh::olvidarJefe($this->nuevoJefeId);
        $this->nuevoJefeId = null;
        $this->toast('success', 'Jefe asignado');
    }

    public function quitarJefe(int $jefaturaId): void
    {
        $this->exigir($this->puedeEditar());

        $jefatura = RrhhJefatura::findOrFail($jefaturaId);
        $jefatura->delete();
        MenuRrhh::olvidarJefe($jefatura->user_id);
        $this->toast('success', 'Jefe quitado');
    }

    public function render(OrganigramaRrhh $organigrama): View
    {
        $arbol = $this->consultar(fn () => $organigrama->arbol(), []);
        $personas = $this->consultar(fn () => Cache::remember('rrhh.personas_por_oficina', now()->addMinutes(10), fn () => Empleado::conNivel(NivelDatoRrhh::Basico)
            ->activos()
            ->get(['empleados.oficina_id'])
            ->countBy('oficina_id')
            ->all()), []);
        $jefes = RrhhJefatura::with('user:id,nombre,apellido')->get()->groupBy('mitiv_oficina_id');
        $editable = $this->puedeEditar();

        return view('livewire.rrhh.organigrama', [
            'arbol' => $arbol,
            'padres' => $organigrama->padres(),
            'personas' => $personas,
            'jefes' => $jefes,
            'editable' => $editable,
            'usuarios' => $editable && $this->editando ? User::orderBy('apellido')->orderBy('nombre')->get(['id', 'nombre', 'apellido']) : collect(),
            'error' => $this->errorFuente,
        ]);
    }

    private function puedeEditar(): bool
    {
        return (bool) auth()->user()?->permiso('lista_usuario_editar');
    }
}
