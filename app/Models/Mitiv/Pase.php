<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pase del sistema viejo: el expediente "entra" a oficina_id en fecha_hora.
 *
 * @property int $id
 * @property int $expediente_id
 * @property int $oficina_id
 * @property int|null $empleado_id
 * @property Carbon|null $fecha_hora
 * @property-read OficinaExpediente|null $oficina
 * @property-read Empleado|null $empleado
 * @property int|null $folio
 * @property int|null $folio_actuales
 * @property string|null $observacion
 */
class Pase extends MitivModel
{
    protected $table = 'expediente_pases';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    /** @return BelongsTo<OficinaExpediente, $this> */
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(OficinaExpediente::class, 'oficina_id');
    }

    /** @return BelongsTo<Empleado, $this> */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
