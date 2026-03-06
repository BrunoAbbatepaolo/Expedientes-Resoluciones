<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permiso extends Model
{
    // Importante: esta tabla vive en mysql_admin
    protected $connection = 'mysql_admin';
    protected $table = 'permisos';

    protected $fillable = [
        'user_id',
        'nombre',
        'oficina_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Evitá usar esta relación si 'Oficina' está en otra conexión.
    // Mantenla solo si SABÉS lo que hacés; lo recomendado: NO usarla.
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id', 'id');
    }

    public static function oficinaAsignada(?int $userId): ?self
    {
        if (!$userId) return null;

        return static::query()
            ->where('user_id', $userId)
            ->where('nombre', 'oficina_asignada')
            ->first();
    }

    public static function setOficinaAsignada(int $userId, int $oficinaId): self
    {
        return static::query()->updateOrCreate(
            ['user_id' => $userId, 'nombre' => 'oficina_asignada'],
            ['oficina_id' => $oficinaId]
        );
    }
}
