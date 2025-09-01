<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class ListaUsuario extends Component
{
    use WithPagination;

    public $search = '';
    public $usuarioSeleccionado;
    public $permisos = [
        'expediente_ver' => false,
        'expediente_editar' => false,
        'resolucion_ver' => false,
        'resolucion_editar' => false,
        'lista_usuario_ver' => false,
        'lista_usuario_editar' => false,
    ];

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function seleccionarUsuario($id)
    {
        $this->usuarioSeleccionado = User::find($id);
        foreach ($this->permisos as $permiso => $valor) {
            $this->permisos[$permiso] = $this->usuarioSeleccionado->permiso($permiso);
        }
    }

    public function nuevoUsuario()
    {
        // Placeholder para lógica futura de agregar usuario
        logger("Abrir modal para nuevo usuario");
    }

    public function guardarPermisos()
    {
        foreach ($this->permisos as $permiso => $valor) {
            if ($valor) {
                if (!$this->usuarioSeleccionado->permiso($permiso)) {
                    $this->usuarioSeleccionado->permisos()->create(['nombre' => $permiso]);
                }
            } else {
                $this->usuarioSeleccionado->permisos()->where('nombre', $permiso)->delete();
            }
        }
        session()->flash('message', 'Permisos actualizados correctamente');
        $this->modal('modal-permisos')->close();
    }
    public function render()
    {
        $usuarios = User::on('mysql_admin')
            ->where(function ($query) {
                // Dividimos la búsqueda en palabras
                $palabras = explode(' ', $this->search);

                foreach ($palabras as $palabra) {
                    $query->where(function ($q) use ($palabra) {
                        $q->where('nombre', 'like', "%{$palabra}%")
                            ->orWhere('apellido', 'like', "%{$palabra}%")
                            ->orWhere('email', 'like', "%{$palabra}%");
                    });
                }
            })
            ->paginate(20);

        return view('livewire.lista-usuario', [
            'usuarios' => $usuarios,
        ]);
    }
    public function togglePermiso($usuarioId, $permiso)
    {
        $usuario = User::find($usuarioId);
        $usuario->togglePermiso($permiso);
    }

    public function seleccionarTodos()
    {
        $this->permisos = array_fill_keys(array_keys($this->permisos), true);
    }

    public function limpiarSeleccion()
    {
        $this->permisos = array_fill_keys(array_keys($this->permisos), false);
    }

    public function aplicarPermisosSoloLectura()
    {
        foreach ($this->permisos as $key => $value) {
            $this->permisos[$key] = str_contains($key, '_ver');
        }
    }

    public function cancelar()
    {
        $this->permisos = $this->usuario->permisos->pluck('name')->flip()->map(fn() => true)->toArray();
        $this->dispatch('close-modal', 'modal-permisos');
    }
}
