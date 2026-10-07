<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Inasistencia registrada en el sistema viejo (falta, llegada tarde, justificaciones).
 * La genera su proceso de reloj o RRHH a mano: es el registro oficial.
 *
 * @property int $id
 * @property int $empleado_id
 * @property int $tipo_inasistencia_id
 * @property Carbon $fecha
 * @property string|null $observacion
 * @property bool|null $habilitado
 */
class Inasistencia extends MitivModel
{
    protected $table = 'inasistencias';

    protected function casts(): array
    {
        return ['fecha' => 'date', 'habilitado' => 'boolean'];
    }

    /** @return BelongsTo<TipoInasistencia, $this> */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoInasistencia::class, 'tipo_inasistencia_id');
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('inasistencias.habilitado', true)->orWhereNull('inasistencias.habilitado'));
    }
}
