<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Base de los modelos de mitivdb (sistema viejo, en producción).
 * SiRex solo lee: cualquier intento de guardar o borrar lanza una excepción.
 * La sesión de base además se abre en modo solo lectura (AppServiceProvider).
 */
abstract class MitivModel extends Model
{
    protected $connection = 'pgsql_mitiv';

    protected $guarded = ['*'];

    public $timestamps = false;

    protected static function booted(): void
    {
        $bloquear = fn () => throw new LogicException('mitivdb es de solo lectura desde SiRex.');

        static::saving($bloquear);
        static::deleting($bloquear);
    }
}
