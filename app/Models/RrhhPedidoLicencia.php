<?php

namespace App\Models;

use App\Enums\EstadoPedido;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pedido de licencia hecho por un empleado desde Mi RRHH.
 *
 * @property int $id
 * @property int $empleado_id
 * @property int $oficina_id
 * @property int $user_id
 * @property int $tipo_ausencia_id
 * @property string $tipo_nombre
 * @property bool $es_vacaciones
 * @property Carbon $desde
 * @property Carbon $hasta
 * @property int $dias_habiles
 * @property string|null $observacion
 * @property string|null $adjunto_ruta
 * @property string|null $adjunto_nombre
 * @property EstadoPedido $estado
 * @property int|null $jefe_id
 * @property Carbon|null $jefe_at
 * @property string|null $jefe_motivo
 * @property int|null $rrhh_id
 * @property Carbon|null $rrhh_at
 * @property string|null $rrhh_motivo
 * @property Carbon $created_at
 * @property-read User|null $user
 * @property-read User|null $jefe
 * @property-read User|null $rrhh
 */
class RrhhPedidoLicencia extends Model
{
    use Auditable;

    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_pedidos_licencia';

    protected $fillable = [
        'empleado_id', 'oficina_id', 'user_id', 'tipo_ausencia_id', 'tipo_nombre', 'es_vacaciones',
        'desde', 'hasta', 'dias_habiles', 'observacion', 'adjunto_ruta', 'adjunto_nombre', 'estado',
        'jefe_id', 'jefe_at', 'jefe_motivo', 'rrhh_id', 'rrhh_at', 'rrhh_motivo',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPedido::class,
            'es_vacaciones' => 'boolean',
            'desde' => 'date',
            'hasta' => 'date',
            'jefe_at' => 'datetime',
            'rrhh_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function jefe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    /** @return BelongsTo<User, $this> */
    public function rrhh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rrhh_id');
    }
}
