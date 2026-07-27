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

    public function permiso(string $permiso): bool
    {
        return $this->permisos()->where('nombre', $permiso)->exists();
    }

    public function togglePermiso(string $permiso): void
    {
        if ($this->permiso($permiso)) {
            $this->permisos()->where('nombre', $permiso)->delete();
        } else {
            $this->permisos()->create(['nombre' => $permiso]);
        }
    }

    /**
     * Devuelve la oficina asignada desde el permiso 'oficina_asignada'.
     */
    public function oficinaAsignadaId(): ?int
    {
        return $this->permisos()
            ->where('nombre', 'oficina_asignada')
            ->value('oficina_id');
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

        return $this->permisos()
            ->where('nombre', $permisoNombre)
            ->value('oficina_id');
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
