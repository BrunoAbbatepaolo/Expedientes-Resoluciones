<?php

namespace App\Models\Mitiv;

use Illuminate\Support\Carbon;

/**
 * Bitácora del servicio lector de relojes (CHEQUEO_ESTADO_SERVICIO es su latido).
 *
 * @property int $id
 * @property string $operacion
 * @property string|null $estado
 * @property Carbon $created_at
 */
class OperacionReloj extends MitivModel
{
    protected $table = 'operaciones_reloj';

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
