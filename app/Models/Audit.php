<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Audit extends Model
{
    // Sin $connection fija: usa la conexión por defecto, la misma donde la migración
    // crea la tabla. Con 'mysql' fijo los tests intentaban escribir en la base real.
    protected $table = 'audits';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Deja asentado un hecho de seguridad (permisos, altas de usuarios, cambios de oficina o de clave).
     * Nunca pasar contraseñas ni claves en $antes/$despues.
     *
     * @param  array<string, mixed>  $antes
     * @param  array<string, mixed>  $despues
     */
    public static function registrar(Model $modelo, string $evento, array $antes = [], array $despues = []): self
    {
        return static::create([
            'auditable_type' => $modelo::class,
            'auditable_id' => $modelo->getKey(),
            'event' => $evento,
            'old_values' => $antes,
            'new_values' => $despues,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
