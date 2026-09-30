<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Destino de pases del sistema viejo (áreas, departamentos, organismos externos).
 *
 * @property int $id
 * @property string|null $nombre
 * @property bool $habilitado
 */
class OficinaExpediente extends MitivModel
{
    protected $table = 'expediente_oficinas';

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'empleado_expediente_oficinas', 'expediente_oficina_id', 'empleado_id')
            ->wherePivot('habilitado', true);
    }
}
