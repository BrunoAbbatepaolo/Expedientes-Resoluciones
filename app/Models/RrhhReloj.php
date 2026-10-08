<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Reloj de fichadas que consulta SiRex (agente de relojes).
 *
 * @property int $id
 * @property string $nombre
 * @property string $ip
 * @property int $puerto
 * @property string $tipo
 * @property bool $activo
 * @property string|null $serie
 * @property string|null $modelo
 * @property string|null $firmware
 * @property Carbon|null $ultimo_contacto_at
 * @property string|null $ultima_fichada_at hora del reloj de la última fichada guardada (Y-m-d H:i:s)
 * @property int|null $registros_en_reloj
 * @property int|null $desvio_segundos hora del reloj menos hora real (positivo = adelantado)
 * @property int $fallas_seguidas
 * @property string|null $ultimo_error
 * @property Carbon|null $ultimo_error_at
 */
class RrhhReloj extends Model
{
    protected $connection = 'mysql_admin';

    protected $table = 'rrhh_relojes';

    protected $fillable = [
        'nombre', 'ip', 'puerto', 'tipo', 'activo', 'serie', 'modelo', 'firmware', 'ultimo_contacto_at',
        'ultima_fichada_at', 'registros_en_reloj', 'desvio_segundos', 'fallas_seguidas', 'ultimo_error', 'ultimo_error_at',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'ultimo_contacto_at' => 'datetime', 'ultimo_error_at' => 'datetime'];
    }
}
