<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $numero
 * @property string|null $fecha
 * @property string|null $hora
 * @property int|null $folio
 * @property int|null $tomo
 * @property string|null $asunto
 * @property string|null $resolucion
 * @property string|null $observacion
 * @property int $causante_id
 * @property int|null $asunto_id
 */
class Expediente extends MitivModel
{
    protected $table = 'expedientes';

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function causante(): BelongsTo
    {
        return $this->belongsTo(Causante::class, 'causante_id');
    }

    public function tipoAsunto(): BelongsTo
    {
        return $this->belongsTo(TipoAsunto::class, 'asunto_id');
    }

    /** @return HasMany<Pase, $this> */
    public function pases(): HasMany
    {
        return $this->hasMany(Pase::class, 'expediente_id')
            ->where('habilitado', true)
            ->orderBy('fecha_hora')
            ->orderBy('id');
    }

    /** @return HasMany<Relacionado, $this> */
    public function relacionadosComoPrincipal(): HasMany
    {
        return $this->hasMany(Relacionado::class, 'expediente_principal_id')->where('habilitado', true);
    }

    /** @return HasMany<Relacionado, $this> */
    public function relacionadosComoSecundario(): HasMany
    {
        return $this->hasMany(Relacionado::class, 'expediente_secundario_id')->where('habilitado', true);
    }
}
