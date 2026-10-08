<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipo de licencia del sistema viejo que los empleados pueden pedir desde Mi RRHH.
 *
 * @property int $id
 * @property int $tipo_ausencia_id
 * @property string $nombre
 * @property bool $permite_retroactivo
 * @property bool $requiere_adjunto
 */
class RrhhTipoPedible extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_tipos_pedibles';

    protected $fillable = ['tipo_ausencia_id', 'nombre', 'permite_retroactivo', 'requiere_adjunto'];

    protected function casts(): array
    {
        return ['permite_retroactivo' => 'boolean', 'requiere_adjunto' => 'boolean'];
    }
}
