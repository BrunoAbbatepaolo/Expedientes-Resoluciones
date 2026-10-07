<?php

namespace App\Models\Mitiv;

use Illuminate\Support\Carbon;

/**
 * Fichada de reloj (la carga el lector de relojes en el sistema viejo). Se cruza con
 * empleados.card_id.
 *
 * @property int $id
 * @property int $card_id
 * @property Carbon $fecha_hora
 * @property string|null $nombre_reloj
 * @property string|null $origen
 */
class RegistroReloj extends MitivModel
{
    protected $table = 'registros_reloj';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
