<?php

namespace App\Models\Mitiv;

use Illuminate\Support\Carbon;

/**
 * Pase a pasivo / activo de un empleado.
 *
 * @property int $id
 * @property int $empleado_id
 * @property Carbon|null $fecha
 * @property string|null $resolucion
 * @property string|null $motivo
 * @property string|null $tipo
 */
class EmpleadoMovimiento extends MitivModel
{
    protected $table = 'empleado_movimientos';

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
