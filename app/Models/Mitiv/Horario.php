<?php

namespace App\Models\Mitiv;

/**
 * Horario de un empleado para un día de la semana (dia: 1 = lunes … 7 = domingo).
 *
 * @property int $id
 * @property int $empleado_id
 * @property int $dia
 * @property string|null $desde_maniana
 * @property string|null $hasta_maniana
 * @property int|null $tolerancia_maniana
 * @property string|null $desde_tarde
 * @property string|null $hasta_tarde
 * @property int|null $tolerancia_tarde
 * @property bool|null $habilitado
 */
class Horario extends MitivModel
{
    protected $table = 'horarios';
}
