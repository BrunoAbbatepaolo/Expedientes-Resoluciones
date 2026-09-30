<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $nombre
 * @property int $tipo_causante_id
 */
class Causante extends MitivModel
{
    protected $table = 'causantes';

    // Documento, domicilio y teléfono no se exponen en SiRex.
    protected $visible = ['id', 'nombre', 'tipo_causante_id'];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoCausante::class, 'tipo_causante_id');
    }
}
