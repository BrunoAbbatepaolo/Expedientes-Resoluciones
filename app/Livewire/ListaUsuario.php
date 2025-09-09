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

    /** Permisos de tu UI (se respetan tal cual) */
    public array $permisos = [
        'expediente_ver' => false,
        'expediente_editar' => false,
        'resolucion_ver' => false,
        'resolucion_editar' => false,
        'lista_usuario_ver' => false,
        'lista_usuario_editar' => false,
    ];

    /** Estado para edición de oficina */
    public ?int $editingUserId = null;

    // Removemos el atributo #[Validate] y usamos rules() en su lugar
    public ?int $oficina_id = null;

    /** Catálogo de oficinas para el <select> */
    public array $oficinas = [];

    protected array $queryString = ['search'];

    protected function rules(): array
    {
        return [
            'oficina_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // Verificamos manualmente que la oficina existe en mysql_legui
                    $existe = \App\Models\Oficina::query()->where('id', $value)->exists();
                    if (!$existe) {
                        $fail('La oficina seleccionada no es válida.');
                    }
                },
            ],
        ];
    }

    public function mount(): void
    {
        // Cargamos todas las oficinas
        $this->oficinas = Oficina::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn(Oficina $o) => ['id' => $o->id, 'nombre' => $o->nombre])
            ->toArray();

        // IMPORTANTE: Cargar los permisos del usuario autenticado al inicio
        $this->cargarPermisosUsuarioActual();
    }

    private function cargarPermisosUsuarioActual(): void
    {
        $user = auth()->user();
        if ($user) {
            foreach ($this->permisos as $permiso => $valor) {
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

        foreach ($this->permisos as $permiso => $valor) {
            $this->permisos[$permiso] = (bool) $this->usuarioSeleccionado->permiso($permiso);
        }
    }

    public function editarOficina(int $userId): void
    {
        \Log::info('editarOficina() invocado', [
            'userId' => $userId,
            'perm_editar' => $this->permisos['lista_usuario_editar'] ?? 'no_definido',
            'auth_user_perm' => auth()->user()?->permiso('lista_usuario_editar'),
        ]);

        // Verificar permiso del usuario autenticado, no del array $permisos
        if (!auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para editar usuarios.');
            return;
        }

        $this->editingUserId = $userId;
        $perm = Permiso::oficinaAsignada($userId);
        $this->oficina_id = $perm?->oficina_id;

        \Log::info('editarOficina() configurado', [
            'editingUserId' => $this->editingUserId,
            'oficina_id' => $this->oficina_id,
        ]);

        $this->dispatch('open-modal', 'modal-oficina');
    }

    public function guardarOficina(): void
    {
        \Log::info('guardarOficina() invocado', [
            'editingUserId' => $this->editingUserId,
            'oficina_id'    => $this->oficina_id,
            'auth_user_perm' => auth()->user()?->permiso('lista_usuario_editar'),
        ]);

        // Verificar permiso del usuario autenticado
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

            // Persistir
            \App\Models\Permiso::setOficinaAsignada($this->editingUserId, (int) $this->oficina_id);

            \Log::info('guardarOficina() OK', [
                'editingUserId' => $this->editingUserId,
                'oficina_id'    => $this->oficina_id,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Oficina actualizada.');
            $this->reset(['editingUserId', 'oficina_id']);
            $this->dispatch('close-modal', 'modal-oficina');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Log::warning('Validación fallida en guardarOficina()', ['errors' => $ve->errors()]);
            $this->dispatch('toast', type: 'error', message: 'Revise los campos.');
            throw $ve;
        } catch (\Throwable $e) {
            \Log::error('Error en guardarOficina()', [
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al guardar.');
        }
    }


    /** --------------------------------------------------------------- */

    public function togglePermiso(int $usuarioId, string $permiso): void
    {
        // Asumimos que User::togglePermiso existe en tu modelo
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
            $this->permisos = $this->usuarioSeleccionado
                ->permisos
                ->pluck('name')
                ->flip()
                ->map(fn() => true)
                ->toArray();
        }

        $this->dispatch('close-modal', 'modal-permisos');
    }

    public function render()
    {
        // 1) Paginamos usuarios desde la conexión mysql_admin (sin JOIN cross-DB)
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

        // 2) Armamos mapas con permisos/oficinas en la conexión por defecto
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
