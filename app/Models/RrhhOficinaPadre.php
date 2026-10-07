<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dependencia entre oficinas del catálogo RRHH (mitivdb.oficinas). Ver Organigrama.
 *
 * @property int $id
 * @property int $mitiv_oficina_id
 * @property int|null $padre_mitiv_oficina_id
 */
class RrhhOficinaPadre extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_oficina_padres';

    protected $fillable = ['mitiv_oficina_id', 'padre_mitiv_oficina_id'];
}
