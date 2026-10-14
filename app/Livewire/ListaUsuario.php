<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Audit;
use App\Models\Mitiv\Empleado;
use App\Models\Oficina;
use App\Models\Permiso;
use App\Models\User;
use App\Support\CatalogoPermisos;
use App\Traits\AuthorizesOficina;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    // Claves de CatalogoPermisos::claves() (un test comprueba que coinciden).
    public array $permisos = [
        'rrhh_ver' => false,
        'rrhh_sensibles' => false,
        'rrhh_horarios' => false,
        'rrhh_confirmar_pedidos' => false,
        'expediente_ver' => false,
        'expediente_editar' => false,
        'resolucion_ver' => false,
        'resolucion_editar' => false,
        'lista_usuario_ver' => false,
        'lista_usuario_editar' => false,
        'admin_ver' => false,
    ];

    // Permisos que tenía el usuario al abrir la ventana: sirve para mostrar qué se va a cambiar.
    #[Locked]
    public array $permisosOriginales = [];

    #[Locked]
    public ?int $editingUserId = null;

    // Clave provisoria del último usuario creado, para que el admin se la comunique.
    #[Locked]
    public ?string $claveGenerada = null;

    #[Locked]
    public string $claveTitulo = 'Usuario creado.';

    // --- Cuenta de un usuario existente (email y restablecer contraseña) ---
    #[Locked]
    public ?int $cuentaUserId = null;

    #[Locked]
    public string $cuentaNombre = '';

    public string $cuentaEmail = '';

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

        $this->permisosOriginales = $this->permisos;
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

            $oficinaAnterior = Permiso::oficinaAsignada($this->editingUserId)?->oficina_id;

            Permiso::setOficinaAsignada($this->editingUserId, (int) $this->oficina_id);

            if ($usuarioEditado = User::on('mysql_admin')->find($this->editingUserId)) {
                Audit::registrar($usuarioEditado, 'oficina', ['oficina_id' => $oficinaAnterior], ['oficina_id' => (int) $this->oficina_id]);
            }

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
            Audit::registrar($usuario, 'usuario_creado', [], [
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'legajo' => $usuario->legajo,
                'email' => $usuario->email,
                'oficina_id' => (int) $this->nuevoOficinaId,
            ]);
            $this->claveTitulo = 'Usuario creado.';
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
            // Se cierran las dependencias: "editar" lleva consigo "ver" aunque el cliente no lo mande.
            $permisosUI = CatalogoPermisos::claves();
            $activos = CatalogoPermisos::normalizar(array_keys(array_filter($this->permisos)));

            // Solo quien ya tiene admin_ver puede otorgarlo o quitarlo: si no, queda como estaba
            // (lista_usuario_editar solo no alcanza para darse el panel de Administración).
            if (! auth()->user()?->permiso('admin_ver')) {
                $teniaAdmin = Permiso::query()->where('user_id', $userId)->where('nombre', 'admin_ver')->exists();
                $activos = array_values(array_diff($activos, ['admin_ver']));
                if ($teniaAdmin) {
                    $activos[] = 'admin_ver';
                }
            }

            $antes = [];

            DB::connection((new Permiso)->getConnectionName())->transaction(function () use ($userId, $permisosUI, $activos, &$antes) {
                $antes = Permiso::query()
                    ->where('user_id', $userId)
                    ->whereIn('nombre', $permisosUI)
                    ->pluck('nombre')
                    ->all();

                Permiso::query()
                    ->where('user_id', $userId)
                    ->whereIn('nombre', $permisosUI)
                    ->delete();

                foreach ($activos as $nombre) {
                    Permiso::query()->create([
                        'user_id' => $userId,
                        'nombre' => $nombre,
                        'oficina_id' => null,
                    ]);
                }
            });

            // Rastro de quién cambió qué permisos (solo si hubo cambios).
            $agregados = array_values(array_diff($activos, $antes));
            $quitados = array_values(array_diff($antes, $activos));
            if ($agregados !== [] || $quitados !== []) {
                Audit::registrar($this->usuarioSeleccionado, 'permisos', ['permisos' => array_values($antes)], [
                    'permisos' => $activos,
                    'agregados' => $agregados,
                    'quitados' => $quitados,
                ]);
            }

            $this->permisos = array_fill_keys(array_keys($this->permisos), false);
            foreach ($activos as $nombre) {
                $this->permisos[$nombre] = true;
            }
            $this->permisosOriginales = $this->permisos;

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

    /** Abre la ventana de cuenta (email y restablecer contraseña) de un usuario. */
    public function abrirCuenta(int $userId): void
    {
        if (! $this->puedeEditarUsuarios()) {
            return;
        }

        $usuario = User::on('mysql_admin')->find($userId);
        if (! $usuario) {
            $this->dispatch('toast', type: 'error', message: 'Usuario no encontrado.');

            return;
        }

        $this->resetValidation();
        $this->cuentaUserId = $usuario->id;
        $this->cuentaNombre = trim($usuario->apellido.', '.$usuario->nombre);
        $this->cuentaEmail = (string) $usuario->email;

        $this->dispatch('open-modal', 'modal-cuenta');
    }

    public function guardarEmail(): void
    {
        if (! $this->puedeEditarUsuarios() || ! ($usuario = $this->usuarioDeCuenta())) {
            return;
        }

        $this->cuentaEmail = trim($this->cuentaEmail);

        try {
            $this->validate([
                'cuentaEmail' => ['required', 'string', 'email', 'max:255', Rule::unique('mysql_admin.users', 'email')->ignore($usuario->id)],
            ], [
                'cuentaEmail.required' => 'Escribí el email.',
                'cuentaEmail.email' => 'Ese email no es válido. Revisá que tenga la forma nombre@dominio.com.',
                'cuentaEmail.unique' => 'Ese email ya lo usa otro usuario.',
            ], ['cuentaEmail' => 'email']);
        } catch (ValidationException $ve) {
            $this->dispatch('toast', type: 'error', message: $ve->validator->errors()->first());
            throw $ve;
        }

        $anterior = (string) $usuario->email;
        if ($anterior === $this->cuentaEmail) {
            $this->dispatch('toast', type: 'info', message: 'El email es el mismo, no hay nada que cambiar.');

            return;
        }

        $usuario->update(['email' => $this->cuentaEmail]);
        Audit::registrar($usuario, 'email', ['email' => $anterior], ['email' => $this->cuentaEmail]);

        $this->dispatch('toast', type: 'success', message: 'Email actualizado.');
        $this->dispatch('close-modal', 'modal-cuenta');
    }

    /**
     * Deja al usuario con una contraseña provisoria nueva: tendrá que cambiarla al entrar
     * y se le cierran las sesiones abiertas.
     */
    public function restablecerClave(): void
    {
        if (! $this->puedeEditarUsuarios() || ! ($usuario = $this->usuarioDeCuenta())) {
            return;
        }

        if ($usuario->id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'Tu propia contraseña se cambia desde Configuración > Contraseña.');

            return;
        }

        $clave = Str::password(12, symbols: false);
        $usuario->forceFill([
            'password' => $clave,
            'require_password_change' => true,
            'remember_token' => null,
        ])->save();

        $this->cerrarSesionesDe($usuario);
        Audit::registrar($usuario, 'clave_restablecida');

        $this->claveTitulo = 'Contraseña restablecida.';
        $this->claveGenerada = $usuario->email.': '.$clave;

        $this->dispatch('toast', type: 'success', message: 'Contraseña restablecida. Deberá cambiarla al iniciar sesión.');
        $this->dispatch('close-modal', 'modal-cuenta');
    }

    private function puedeEditarUsuarios(): bool
    {
        if (auth()->user()?->permiso('lista_usuario_editar')) {
            return true;
        }

        $this->dispatch('toast', type: 'error', message: 'No tenés permiso para editar usuarios.');

        return false;
    }

    private function usuarioDeCuenta(): ?User
    {
        $usuario = $this->cuentaUserId ? User::on('mysql_admin')->find($this->cuentaUserId) : null;

        if (! $usuario) {
            $this->dispatch('toast', type: 'error', message: 'Usuario no encontrado.');
        }

        return $usuario;
    }

    /** Con sesiones en base de datos se pueden cerrar las del usuario; con otros drivers no hay forma. */
    private function cerrarSesionesDe(User $usuario): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        try {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $usuario->id)
                ->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function ocultarClave(): void
    {
        $this->claveGenerada = null;
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
