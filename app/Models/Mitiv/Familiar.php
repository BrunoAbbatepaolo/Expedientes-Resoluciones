<?php

namespace App\Models\Mitiv;

/**
 * Integrante del grupo familiar. Tiene datos judiciales y de salud: las columnas se
 * eligen siempre por nivel (NivelDatoRrhh::columnasFamiliar()).
 *
 * @property int $id
 * @property int $empleado_id
 * @property string $apellido_nombre
 * @property int|null $parentesco_id
 */
class Familiar extends MitivModel
{
    protected $table = 'grupo_familiares';
}
