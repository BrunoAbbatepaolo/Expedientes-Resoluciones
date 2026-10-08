<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Mitiv\Empleado;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ListaUsuario extends Component
{
    use AuthorizesOficina;
    use WithPagination;

    public string $search = '';

    public ?User $usuarioSeleccionado = null;

    private const PERMISOS_UI = [
        'expediente_ver',
        'expediente_editar',
        'resolucion_ver',
        'resolucion_editar',
        'lista_usuario_ver',
        'lista_usuario_editar',
        'rrhh_ver',
        'rrhh_sensibles',
        'rrhh_horarios',
    ];

    public array $permisos = [
        'rrhh_ver' => false,
        'rrhh_sensibles' => false,
        'rrhh_horarios' => false,
        'expediente_ver' => false,
        'expediente_editar' => false,
        'resolucion_ver' => false,
        'resolucion_editar' => false,
        'lista_usuario_ver' => false,
        'lista_usuario_editar' => false,
    ];

    #[Locked]
    public ?int $editingUserId = null;

    // Clave provisoria del último usuario creado, para que el admin se la comunique.
    #[Locked]
    public ?string $claveGenerada = null;

    public ?int $oficina_id = null;

    public array $oficinas = [];

    // --- Alta de usuario (solo empleados activos del IPV, fuente: sistema viejo) ---
    public string $buscarEmpleado = '';

    #[Locked]
    public ?int $empleadoId = null;

    public string $nuevoNombre = '';

    public string $nuevoApellido = '';

    public string $nuevoLegajo = '';

    public string $nuevoEmail = '';

    public ?int $nuevoOficinaId = null;

    protected array $queryString = ['search'];

    protected function rules(): array
    {
        return [
            'oficina_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $existe = Oficina::query()->where('id', $value)->exists();
                    if (! $existe) {
                        $fail('La oficina seleccionada no es válida.');
                    }
                },
            ],
        ];
    }

    protected function reglasNuevoUsuario(): array
    {
        return [
            'nuevoNombre' => ['required', 'string', 'max:255'],
            'nuevoApellido' => ['required', 'string', 'max:255'],
            'nuevoLegajo' => ['required', 'string', 'max:255', 'unique:mysql_admin.users,legajo'],
            'nuevoEmail' => ['required', 'string', 'email', 'max:255', 'unique:mysql_admin.users,email'],
            'nuevoOficinaId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! Oficina::query()->where('id', $value)->exists()) {
                        $fail('La oficina seleccionada no es válida.');
                    }
                },
            ],
        ];
    }

    public function mount(): void
    {
        $this->autorizarPermiso('lista_usuario_ver');

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

        if (! $this->usuarioSeleccionado) {
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
        if (! auth()->user()?->permiso('lista_usuario_editar')) {
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
        if (! auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para guardar.');

            return;
        }

        if (! $this->editingUserId) {
            $this->dispatch('toast', type: 'error', message: 'No hay usuario seleccionado.');

            return;
        }

        try {
            $this->validate();

            Permiso::setOficinaAsignada($this->editingUserId, (int) $this->oficina_id);

            $this->dispatch('toast', type: 'success', message: 'Oficina actualizada.');
            $this->reset(['editingUserId', 'oficina_id']);
            $this->dispatch('close-modal', 'modal-oficina');
        } catch (ValidationException $ve) {
            $this->dispatch('toast', type: 'error', message: 'Revise los campos.');
            throw $ve;
        } catch (\Throwable $e) {
            \Log::error('Error en guardarOficina()', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al guardar.');
        }
    }

    /**
     * Empleados activos que coinciden con lo tipeado (apellido, nombre o legajo).
     *
     * @return Collection<int, Empleado>
     */
    #[Computed]
    public function empleadosSugeridos(): Collection
    {
        $texto = trim($this->buscarEmpleado);
        if (mb_strlen($texto) < 2 || $this->empleadoId) {
            return collect();
        }

        return rescue(function () use ($texto) {
            $like = DB::connection('pgsql_mitiv')->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return Empleado::activos()
                ->where(function ($q) use ($texto, $like) {
                    ctype_digit($texto)
                        ? $q->whereRaw('CAST(legajo AS TEXT) LIKE ?', [$texto.'%'])
                        : $q->where('apellido', $like, '%'.addcslashes($texto, '%_\\').'%')
                            ->orWhere('nombre', $like, '%'.addcslashes($texto, '%_\\').'%');
                })
                ->orderBy('apellido')
                ->limit(8)
                ->get();
        }, collect());
    }

    public function seleccionarEmpleado(int $id): void
    {
        $empleado = rescue(fn () => Empleado::activos()->find($id));
        if (! $empleado) {
            $this->dispatch('toast', type: 'error', message: 'El empleado no está activo en el IPV.');

            return;
        }

        $this->empleadoId = $empleado->id;
        $this->nuevoNombre = $empleado->nombre;
        $this->nuevoApellido = $empleado->apellido;
        $this->nuevoLegajo = (string) $empleado->legajo;
        $this->nuevoEmail = (string) $empleado->email;
        $this->buscarEmpleado = $empleado->nombre_completo;
    }

    public function quitarEmpleado(): void
    {
        $this->reset(['empleadoId', 'buscarEmpleado', 'nuevoNombre', 'nuevoApellido', 'nuevoLegajo', 'nuevoEmail']);
    }

    public function crearUsuario(): void
    {
        if (! auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para crear usuarios.');

            return;
        }

        // Nombre, apellido y legajo salen del empleado activo (no de lo que mande el cliente).
        $empleado = $this->empleadoId ? rescue(fn () => Empleado::activos()->find($this->empleadoId)) : null;
        if (! $empleado) {
            $this->addError('buscarEmpleado', 'Elegí un empleado activo del IPV.');

            return;
        }
        $this->nuevoNombre = $empleado->nombre;
        $this->nuevoApellido = $empleado->apellido;
        $this->nuevoLegajo = (string) $empleado->legajo;

        try {
            $this->validate($this->reglasNuevoUsuario());

            $usuario = User::query()->create([
                'nombre' => $this->nuevoNombre,
                'apellido' => $this->nuevoApellido,
                'legajo' => $this->nuevoLegajo,
                'email' => $this->nuevoEmail,
                // Contraseña provisoria aleatoria: el usuario está obligado a cambiarla
                // en su primer login (ver App\Http\Middleware\EnsurePasswordIsChanged).
                'password' => $clave = Str::password(12, symbols: false),
                'require_password_change' => true,
            ]);

            Permiso::setOficinaAsignada($usuario->id, (int) $this->nuevoOficinaId);
            $this->claveGenerada = $usuario->email.': '.$clave;

            $this->dispatch('toast', type: 'success', message: 'Usuario creado. Deberá cambiar su contraseña al iniciar sesión.');
            $this->reset(['nuevoNombre', 'nuevoApellido', 'nuevoLegajo', 'nuevoEmail', 'nuevoOficinaId', 'empleadoId', 'buscarEmpleado']);
            $this->dispatch('close-modal', 'modal-crear-usuario');
        } catch (ValidationException $ve) {
            $this->dispatch('toast', type: 'error', message: 'Revise los campos.');
            throw $ve;
        } catch (\Throwable $e) {
            \Log::error('Error en crearUsuario()', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al crear el usuario.');
        }
    }

    public function guardarPermisos(): void
    {
        if (! auth()->user()?->permiso('lista_usuario_editar')) {
            $this->dispatch('toast', type: 'error', message: 'No tenés permiso para editar usuarios.');

            return;
        }

        if (! $this->usuarioSeleccionado) {
            $this->dispatch('toast', type: 'error', message: 'No hay usuario seleccionado.');

            return;
        }

        try {
            $userId = $this->usuarioSeleccionado->id;
            // Lista blanca: el array $permisos viene del cliente y podría traer claves
            // nuevas (ej. 'oficina_asignada'); solo se tocan los permisos de esta UI.
            $permisosUI = self::PERMISOS_UI;

            DB::connection((new Permiso)->getConnectionName())->transaction(function () use ($userId, $permisosUI) {
                Permiso::query()
                    ->where('user_id', $userId)
                    ->whereIn('nombre', $permisosUI)
                    ->delete();

                foreach ($permisosUI as $nombre) {
                    if (! empty($this->permisos[$nombre])) {
                        Permiso::query()->create([
                            'user_id' => $userId,
                            'nombre' => $nombre,
                            'oficina_id' => null,
                        ]);
                    }
                }
            });

            $this->dispatch('toast', type: 'success', message: 'Permisos guardados correctamente.');
            $this->dispatch('close-modal', 'modal-permisos');
        } catch (\Throwable $e) {
            \Log::error('Error en guardarPermisos()', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Ocurrió un error al guardar permisos.');
        }
    }

    public function ocultarClave(): void
    {
        $this->claveGenerada = null;
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
        // RRHH da acceso a datos personales de todo el personal: nunca por preset.
        foreach ($this->permisos as $key => $value) {
            $this->permisos[$key] = str_contains($key, '_ver') && ! str_starts_with($key, 'rrhh_');
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

        /** @var EloquentCollection<int, Permiso> $permisosOficina */
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

        // Legajos de esta página que siguen activos en el IPV. null = el sistema viejo
        // no respondió y no se marca a nadie.
        $legajosActivos = rescue(fn () => Empleado::activos()
            ->whereIn('legajo', collect($usuarios->items())->pluck('legajo')->filter(fn ($l) => ctype_digit((string) $l))->map(fn ($l) => (int) $l)->all())
            ->pluck('legajo')
            ->map(fn ($l) => (string) $l)
            ->all(), null);

        return view('livewire.lista-usuario', [
            'legajosActivos' => $legajosActivos,
            'usuarios' => $usuarios,
            'oficinaPorUsuario' => $oficinaPorUsuario,
            'oficinas' => $this->oficinas,
        ]);
    }
}
