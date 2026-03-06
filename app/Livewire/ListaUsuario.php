<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ListaUsuario extends Component
{
    use WithPagination;

    public string $search = '';
    public ?User $usuarioSeleccionado = null;

    public array $permisos = [
        'expediente_ver' => false,
        'expediente_editar' => false,
        'resolucion_ver' => false,
        'resolucion_editar' => false,
        'lista_usuario_ver' => false,
        'lista_usuario_editar' => false,
    ];

    public ?int $editingUserId = null;
    public ?int $oficina_id = null;
    public array $oficinas = [];

    protected array $queryString = ['search'];

    protected function rules(): array
    {
        return [
            'oficina_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $existe = Oficina::query()->where('id', $value)->exists();
                    if (!$existe) {
                        $fail('La oficina seleccionada no es válida.');
                    }
                },
            ],
        ];
    }

    public function mount(): void
    {
        $this->oficinas = Oficina::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Oficina $o) => ['id' => $o->id, 'nombre' => $o->nombre])
            ->toArray();

        $this->cargarPermisosUsuarioActual();
    }

    private function cargarPermisosUsuarioActual(): void
    {
        $user = auth()->user();

        if ($user) {
            foreach (array_keys($this->permisos) as $permiso) {
                $this->permisos[$permiso] = (bool) $user->permiso($permiso);
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function seleccionarUsuario(int $id): void
    {
        $this->usuarioSeleccionado = User::on('mysql_admin')->find($id);

        if (!$this->usuarioSeleccionado) {
            $this->dispatch('toast', type: 'error', message: 'Usuario no encontrado.');
            return;
        }

        foreach (array_keys($this->permisos) as $permiso) {
            $this->permisos[$permiso] = Permiso::query()
                ->where('user_id', $this->usuarioSeleccionado->id)
                ->where('nombre', $permiso)
                ->exists();
        }
    }

    public function editarOficina(int $userId): void
    {
        if (!auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para editar usuarios.');
            return;
        }

        $this->editingUserId = $userId;
        $perm = Permiso::oficinaAsignada($userId);
        $this->oficina_id = $perm?->oficina_id;

        $this->dispatch('open-modal', 'modal-oficina');
    }

    public function guardarOficina(): void
    {
        if (!auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para guardar.');
            return;
        }

        if (!$this->editingUserId) {
            $this->dispatch('toast', type: 'error', message: 'No hay usuario seleccionado.');
            return;
        }

        try {
            $this->validate();

            Permiso::setOficinaAsignada($this->editingUserId, (int) $this->oficina_id);

            $this->dispatch('toast', type: 'success', message: 'Oficina actualizada.');
            $this->reset(['editingUserId', 'oficina_id']);
            $this->dispatch('close-modal', 'modal-oficina');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $this->dispatch('toast', type: 'error', message: 'Revise los campos.');
            throw $ve;
        } catch (\Throwable $e) {
            \Log::error('Error en guardarOficina()', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al guardar.');
        }
    }

    public function guardarPermisos(): void
    {
        if (!auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para editar usuarios.');
            return;
        }

        if (!$this->usuarioSeleccionado) {
            $this->dispatch('toast', type: 'error', message: 'No hay usuario seleccionado.');
            return;
        }

        try {
            $userId = $this->usuarioSeleccionado->id;
            $permisosUI = array_keys($this->permisos);

            // Borrar solo los permisos administrables desde esta UI
            Permiso::query()
                ->where('user_id', $userId)
                ->whereIn('nombre', $permisosUI)
                ->delete();

            // Insertar nuevamente los que quedaron en true
            foreach ($this->permisos as $nombre => $activo) {
                if ($activo) {
                    Permiso::query()->create([
                        'user_id' => $userId,
                        'nombre' => $nombre,
                        'oficina_id' => null,
                    ]);
                }
            }

            $this->dispatch('toast', type: 'success', message: 'Permisos guardados correctamente.');
            $this->dispatch('close-modal', 'modal-permisos');
        } catch (\Throwable $e) {
            \Log::error('Error en guardarPermisos()', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al guardar permisos.');
        }
    }

    public function togglePermiso(int $usuarioId, string $permiso): void
    {
        $usuario = User::on('mysql_admin')->find($usuarioId);

        if (!$usuario) {
            $this->dispatch('toast', type: 'error', message: 'Usuario no encontrado.');
            return;
        }

        $usuario->togglePermiso($permiso);
    }

    public function seleccionarTodos(): void
    {
        $this->permisos = array_fill_keys(array_keys($this->permisos), true);
    }

    public function limpiarSeleccion(): void
    {
        $this->permisos = array_fill_keys(array_keys($this->permisos), false);
    }

    public function aplicarPermisosSoloLectura(): void
    {
        foreach ($this->permisos as $key => $value) {
            $this->permisos[$key] = str_contains($key, '_ver');
        }
    }

    public function cancelar(): void
    {
        if ($this->usuarioSeleccionado) {
            $permisosActuales = Permiso::query()
                ->where('user_id', $this->usuarioSeleccionado->id)
                ->whereIn('nombre', array_keys($this->permisos))
                ->pluck('nombre')
                ->toArray();

            foreach (array_keys($this->permisos) as $permiso) {
                $this->permisos[$permiso] = in_array($permiso, $permisosActuales, true);
            }
        }

        $this->dispatch('close-modal', 'modal-permisos');
    }

    public function render()
    {
        /** @var LengthAwarePaginator $usuarios */
        $usuarios = User::on('mysql_admin')
            ->when($this->search !== '', function ($query) {
                $palabras = array_filter(explode(' ', $this->search));

                foreach ($palabras as $palabra) {
                    $query->where(function ($q) use ($palabra) {
                        $q->where('nombre', 'like', "%{$palabra}%")
                            ->orWhere('apellido', 'like', "%{$palabra}%")
                            ->orWhere('email', 'like', "%{$palabra}%");
                    });
                }
            })
            ->orderBy('nombre')
            ->paginate(20);

        $userIds = collect($usuarios->items())->pluck('id')->all();

        /** @var EloquentCollection $permisosOficina */
        $permisosOficina = Permiso::query()
            ->whereIn('user_id', $userIds)
            ->where('nombre', 'oficina_asignada')
            ->get(['user_id', 'oficina_id']);

        $oficinaIds = $permisosOficina->pluck('oficina_id')->filter()->unique()->values()->all();

        /** @var EloquentCollection $oficinasById */
        $oficinasById = Oficina::query()
            ->whereIn('id', $oficinaIds)
            ->get(['id', 'nombre'])
            ->keyBy('id');

        /** @var Collection<int,string> $oficinaPorUsuario */
        $oficinaPorUsuario = collect();

        foreach ($permisosOficina as $perm) {
            $oficinaPorUsuario[$perm->user_id] = $perm->oficina_id
                ? ($oficinasById[$perm->oficina_id]->nombre ?? 'Sin asignar')
                : 'Sin asignar';
        }

        return view('livewire.lista-usuario', [
            'usuarios'          => $usuarios,
            'oficinaPorUsuario' => $oficinaPorUsuario,
            'oficinas'          => $this->oficinas,
        ]);
    }
}