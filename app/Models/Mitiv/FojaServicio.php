<?php

namespace App\Models\Mitiv;

use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $empleado_id
 * @property Carbon|null $fecha
 * @property string|null $resolucion_decreto
 * @property string|null $nro_res_dec
 * @property string|null $observacion
 */
class FojaServicio extends MitivModel
{
    protected $table = 'fojas_servicio';

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
