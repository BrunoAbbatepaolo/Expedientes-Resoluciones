<?php

namespace App\Models\Mitiv;

/**
 * Tipo de licencia/ausencia del sistema viejo.
 *
 * @property int $id
 * @property string $descripcion
 * @property int|null $codigo
 * @property bool $afecta_vacaciones
 * @property bool $habilitado
 */
class TipoAusencia extends MitivModel
{
    protected $table = 'tipo_ausencias';

    protected function casts(): array
    {
        return ['afecta_vacaciones' => 'boolean', 'habilitado' => 'boolean'];
    }
}
