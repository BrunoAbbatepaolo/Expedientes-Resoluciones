<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Registro de quién vio datos personales o sensibles de otro empleado.
 *
 * @property int $id
 * @property int $user_id
 * @property int $empleado_id
 * @property string $nivel
 * @property Carbon $created_at
 */
class RrhhAcceso extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_accesos';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'empleado_id', 'nivel'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
