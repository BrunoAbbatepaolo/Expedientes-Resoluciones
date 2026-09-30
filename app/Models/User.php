<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mysql_admin';

    protected $table = 'users';

    protected $fillable = [
        'nombre',
        'apellido',
        'legajo',
        'email',
        'password',
        'profile_photo_path',
        'require_password_change',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'require_password_change' => 'boolean',
        ];
    }

    // --- Permisos ---
    public function permisos()
    {
        return $this->hasMany(\App\Models\Permiso::class, 'user_id');
    }

    /**
     * Permisos del usuario cargados una sola vez por request: el menú y las vistas
     * llaman a permiso() decenas de veces (antes era una consulta por llamada).
     *
     * @var \Illuminate\Support\Collection<int, Permiso>|null
     */
    private ?\Illuminate\Support\Collection $permisosEnMemoria = null;

    /** @return \Illuminate\Support\Collection<int, Permiso> */
    private function permisosCargados(): \Illuminate\Support\Collection
    {
        return $this->permisosEnMemoria ??= $this->permisos()->get(['id', 'user_id', 'nombre', 'oficina_id'])->toBase();
    }

    /** Descarta los permisos en memoria (después de modificarlos en el mismo request). */
    public function olvidarPermisos(): void
    {
        $this->permisosEnMemoria = null;
    }

    public function permiso(string $permiso): bool
    {
        return $this->permisosCargados()->contains('nombre', $permiso);
    }

    /**
     * Devuelve la oficina asignada desde el permiso 'oficina_asignada'.
     */
    private Oficina|false|null $oficinaEnMemoria = null;

    /** Oficina asignada (una consulta por request; la usan el menú y el panel). */
    public function oficinaAsignada(): ?Oficina
    {
        if ($this->oficinaEnMemoria === null) {
            $id = $this->oficinaAsignadaId();
            $this->oficinaEnMemoria = ($id ? Oficina::find($id) : null) ?? false;
        }

        return $this->oficinaEnMemoria ?: null;
    }

    public function oficinaAsignadaId(): ?int
    {
        $id = $this->permisosCargados()->firstWhere('nombre', 'oficina_asignada')?->oficina_id;

        return $id !== null ? (int) $id : null;
    }

    /**
     * Mantengo este método por compatibilidad.
     * Primero busca 'oficina_asignada'; si no, intenta en el permiso indicado.
     */
    public function oficinaIdPara(string $permisoNombre = 'expediente_ver'): ?int
    {
        $oficina = $this->oficinaAsignadaId();
        if ($oficina) {
            return (int) $oficina;
        }

        $id = $this->permisosCargados()->firstWhere('nombre', $permisoNombre)?->oficina_id;

        return $id !== null ? (int) $id : null;
    }

    /**
     * Pases pendientes de aceptar en la bandeja de "Entrantes" de mi oficina.
     */
    public function pasesPendientesCount(): int
    {
        $oficinaId = $this->oficinaAsignadaId();

        if (! $oficinaId) {
            return 0;
        }

        return Pase::where('oficina_destino_id', $oficinaId)
            ->where('estado', 'pendiente')
            ->count();
    }

    public function initials(): string
    {
        $iniNombre = $this->nombre ? mb_substr($this->nombre, 0, 1) : '';
        $iniApellido = $this->apellido ? mb_substr($this->apellido, 0, 1) : '';

        return strtoupper($iniNombre.$iniApellido);
    }

    public function getNameAttribute(): string
    {
        return trim(($this->nombre ?? '').' '.($this->apellido ?? ''));
    }
}
