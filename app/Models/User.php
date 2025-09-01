<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $connection = 'mysql_admin'; // Usar la conexión nueva
    protected $table = 'users';
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
    public function permiso($permiso)
    {
        // Retorna true/false si el usuario tiene el permiso
        return $this->permisos()->where('nombre', $permiso)->exists();
    }

    public function togglePermiso($permiso)
    {
        if ($this->permiso($permiso)) {
            $this->permisos()->where('nombre', $permiso)->delete();
        } else {
            $this->permisos()->create(['nombre' => $permiso]);
        }
    }
    public function permisos()
    {
        return $this->hasMany(\App\Models\Permiso::class, 'user_id');
    }
}
