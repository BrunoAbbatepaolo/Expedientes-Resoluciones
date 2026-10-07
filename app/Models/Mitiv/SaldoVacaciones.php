<?php

namespace App\Models\Mitiv;

/**
 * Vista del sistema viejo con el saldo de vacaciones por año (id = empleado).
 *
 * @property int $id
 * @property int $anio
 * @property int $saldo
 */
class SaldoVacaciones extends MitivModel
{
    protected $table = 'vista_saldo_vacaciones';

    public $incrementing = false;
}
