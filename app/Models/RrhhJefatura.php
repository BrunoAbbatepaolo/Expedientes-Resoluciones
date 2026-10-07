<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un usuario es jefe de una oficina del catálogo RRHH (y de sus dependientes).
 *
 * @property int $id
 * @property int $user_id
 * @property int $mitiv_oficina_id
 */
class RrhhJefatura extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_jefaturas';

    protected $fillable = ['user_id', 'mitiv_oficina_id'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
