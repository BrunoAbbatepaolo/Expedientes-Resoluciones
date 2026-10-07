<?php

namespace App\Models\Mitiv;

use Illuminate\Support\Carbon;

/**
 * Feriado o día no laborable (rango desde-hasta).
 *
 * @property int $id
 * @property Carbon $desde
 * @property Carbon $hasta
 * @property int|null $anio
 * @property string|null $tipo
 */
class Feriado extends MitivModel
{
    protected $table = 'feriados';

    protected function casts(): array
    {
        return ['desde' => 'datetime', 'hasta' => 'datetime'];
    }
}
