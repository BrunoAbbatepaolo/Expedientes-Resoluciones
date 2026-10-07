<?php

namespace App\Models\Mitiv;

/**
 * @property int $id
 * @property string $nombre
 * @property int|null $codigo
 * @property bool $afecta_presentismo
 */
class TipoInasistencia extends MitivModel
{
    protected $table = 'tipo_inasistencias';

    protected function casts(): array
    {
        return ['afecta_presentismo' => 'boolean', 'habilitado' => 'boolean'];
    }
}
