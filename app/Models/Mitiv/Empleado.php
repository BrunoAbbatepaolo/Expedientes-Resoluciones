<?php

namespace App\Models\Mitiv;

use App\Enums\NivelDatoRrhh;
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
        return [
            'habilitado' => 'boolean',
            'espasivo' => 'boolean',
            'fecha_ingreso' => 'date',
            'fecha_nacimiento' => 'date',
            'es_discapacitado' => 'boolean',
        ];
    }

    /** Activos = habilitados y no pasivos (jubilados, bajas). */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('empleados.habilitado', true)
            ->where(fn ($q) => $q->where('empleados.espasivo', false)->orWhereNull('empleados.espasivo'));
    }

    /**
     * Reemplaza las columnas públicas por las de un nivel del legajo. Solo debe usarse
     * desde los servicios de RRHH, que deciden el nivel según el Alcance del usuario.
     */
    public function scopeConNivel(Builder $query, NivelDatoRrhh $nivel): Builder
    {
        return $query->withoutGlobalScope('columnas_publicas')
            ->select(array_map(fn ($c) => 'empleados.'.$c, $nivel->columnas()));
    }

    public function situacionLaboral(): BelongsTo
    {
        return $this->belongsTo(SituacionLaboral::class, 'situacion_laboral_id');
    }

    /** Oficina del catálogo de RRHH (mitivdb.oficinas). */
    public function oficinaRrhh(): BelongsTo
    {
        return $this->belongsTo(OficinaRrhh::class, 'oficina_id');
    }

    public function categoriaTitular(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_titular_id');
    }

    public function categoriaInterina(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_interina_id');
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
