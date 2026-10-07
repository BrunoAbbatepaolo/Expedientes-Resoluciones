<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Builder;

/**
 * Oficina del catálogo de RRHH del sistema viejo (mitivdb.oficinas): a esta apunta
 * empleados.oficina_id. No confundir con OficinaExpediente (expediente_oficinas).
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $codigo
 * @property bool $habilitado
 */
class OficinaRrhh extends MitivModel
{
    protected $table = 'oficinas';

    protected function casts(): array
    {
        return ['habilitado' => 'boolean'];
    }

    public function scopeHabilitadas(Builder $query): Builder
    {
        return $query->where('oficinas.habilitado', true);
    }
}
