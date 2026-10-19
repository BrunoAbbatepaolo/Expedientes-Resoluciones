<?php

namespace App\Livewire\Admin;

use App\Models\Firmante;
use App\Traits\NotificaUi;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Administración → Firmantes: la lista que aparece para elegir en cada resolución.
 * Desactivar en vez de borrar: las resoluciones guardadas conservan su copia del nombre y cargo.
 */
class Firmantes extends Component
{
    use NotificaUi;

    /** Null = alta nueva; si no, el firmante que se está editando. */
    #[Locked]
    public ?int $editandoId = null;

    public string $nombre = '';

    public string $cargo = '';

    public string $dependencia = '';

    public function mount(): void
    {
        $this->autorizar();
    }

    public function editar(int $id): void
    {
        $this->autorizar();
        $f = Firmante::findOrFail($id);
        $this->editandoId = $f->id;
        $this->nombre = $f->nombre;
        $this->cargo = $f->cargo;
        $this->dependencia = (string) $f->dependencia;
        $this->resetValidation();
    }

    public function cancelar(): void
    {
        $this->reset('editandoId', 'nombre', 'cargo', 'dependencia');
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->autorizar();
        $datos = $this->validate([
            'nombre' => 'required|string|max:120',
            'cargo' => 'required|string|max:120',
            'dependencia' => 'nullable|string|max:120',
        ], [
            'nombre.required' => 'Escribí el nombre del firmante (como sale en la resolución, p. ej. "Arq. HUGO CABRAL").',
            'cargo.required' => 'Escribí el cargo (p. ej. "INTERVENTOR").',
        ]);
        $datos = array_map(fn ($v) => trim((string) $v), $datos);

        if ($this->editandoId) {
            Firmante::findOrFail($this->editandoId)->update($datos);
            $this->toast('success', 'Firmante actualizado.');
        } else {
            Firmante::create($datos + ['activo' => true, 'orden' => (int) Firmante::max('orden') + 1]);
            $this->toast('success', 'Firmante agregado. Ya aparece en la lista de las resoluciones.');
        }

        $this->cancelar();
    }

    public function alternarActivo(int $id): void
    {
        $this->autorizar();
        $f = Firmante::findOrFail($id);
        $f->update(['activo' => ! $f->activo]);
        $this->toast('info', $f->activo ? 'Vuelve a aparecer en la lista.' : 'Ya no aparece en la lista (las resoluciones guardadas no cambian).');
    }

    /** Sube (-1) o baja (+1) un lugar en la lista. */
    public function mover(int $id, int $direccion): void
    {
        $this->autorizar();
        $lista = Firmante::orderBy('orden')->orderBy('nombre')->get()->values();
        $i = $lista->search(fn ($f) => $f->id === $id);
        $j = $i === false ? false : $i + ($direccion < 0 ? -1 : 1);
        if ($i === false || $j < 0 || $j >= $lista->count()) {
            return;
        }

        [$lista[$i], $lista[$j]] = [$lista[$j], $lista[$i]];
        foreach ($lista as $orden => $f) {
            if ($f->orden !== $orden + 1) {
                $f->update(['orden' => $orden + 1]);
            }
        }
    }

    private function autorizar(): void
    {
        abort_unless((bool) auth()->user()?->permiso('admin_ver'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.firmantes', [
            'firmantes' => Firmante::orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }
}
