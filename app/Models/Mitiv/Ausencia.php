<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Licencia/ausencia de un empleado (rango desde-hasta, ambos inclusive).
 *
 * @property int $id
 * @property int $empleado_id
 * @property Carbon $desde
 * @property Carbon $hasta
 * @property int $tipo_ausencias_id
 * @property int|null $anio
 * @property string|null $observacion
 * @property-read TipoAusencia|null $tipo
 * @property-read Empleado|null $empleado
 */
class Ausencia extends MitivModel
{
    protected $table = 'ausencias';

    protected function casts(): array
    {
        return ['desde' => 'date', 'hasta' => 'date'];
    }

    /** @return BelongsTo<TipoAusencia, $this> */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoAusencia::class, 'tipo_ausencias_id');
    }

    /** @return BelongsTo<Empleado, $this> */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /** Ausencias que se superponen con el rango [desde, hasta] (Y-m-d). */
    public function scopeEntre(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereDate('ausencias.desde', '<=', $hasta)->whereDate('ausencias.hasta', '>=', $desde);
    }
}
