<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $expediente_principal_id
 * @property int $expediente_secundario_id
 * @property string|null $resolucion
 */
class Relacionado extends MitivModel
{
    protected $table = 'expediente_relacionados';

    /** @return BelongsTo<Expediente, $this> */
    public function principal(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_principal_id');
    }

    /** @return BelongsTo<Expediente, $this> */
    public function secundario(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_secundario_id');
    }
}
