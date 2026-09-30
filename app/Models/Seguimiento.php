<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Expediente del IPV que un usuario sigue.
 *
 * @property int $id
 * @property int $user_id
 * @property string $numero_exp
 * @property \Illuminate\Support\Carbon|null $ultimo_pase_visto
 */
class Seguimiento extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'seguimientos';

    protected $fillable = ['user_id', 'numero_exp', 'ultimo_pase_visto'];

    protected function casts(): array
    {
        return ['ultimo_pase_visto' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
