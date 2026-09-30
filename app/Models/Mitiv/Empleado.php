<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Empleado del IPV (fuente: sistema viejo).
 *
 * La tabla real tiene datos muy sensibles (claves, CUIL, salud, cuenta bancaria):
 * el scope global limita el SELECT a estas columnas, así nunca salen de la base.
 *
 * @property int $id
 * @property int $legajo
 * @property string $apellido
 * @property string $nombre
 * @property string|null $email
 * @property int $oficina_id
 * @property int $situacion_laboral_id
 * @property bool $habilitado
 * @property bool|null $espasivo
 */
class Empleado extends MitivModel
{
    protected $table = 'empleados';

    public const COLUMNAS_PUBLICAS = [
        'id', 'legajo', 'apellido', 'nombre', 'email', 'oficina_id',
        'situacion_laboral_id', 'habilitado', 'espasivo',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('columnas_publicas', function (Builder $query) {
            $query->select(array_map(fn ($c) => 'empleados.'.$c, self::COLUMNAS_PUBLICAS));
        });
    }

    protected function casts(): array
    {
        return ['habilitado' => 'boolean', 'espasivo' => 'boolean'];
    }

    /** Activos = habilitados y no pasivos (jubilados, bajas). */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('empleados.habilitado', true)
            ->where(fn ($q) => $q->where('empleados.espasivo', false)->orWhereNull('empleados.espasivo'));
    }

    public function situacionLaboral(): BelongsTo
    {
        return $this->belongsTo(SituacionLaboral::class, 'situacion_laboral_id');
    }

    public function oficinasExpediente(): BelongsToMany
    {
        return $this->belongsToMany(OficinaExpediente::class, 'empleado_expediente_oficinas', 'empleado_id', 'expediente_oficina_id')
            ->wherePivot('habilitado', true);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido.', '.$this->nombre, ', ');
    }
}
