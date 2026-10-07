<?php

namespace App\Models\Mitiv;

use Illuminate\Database\Eloquent\Builder;

/**
 * Archivo adjunto al legajo. El contenido (bytea) no se trae salvo al descargar
 * (withoutGlobalScope('sin_contenido')).
 *
 * @property int $id
 * @property int $empleado_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $tamanio
 * @property string|null $mimetype
 */
class EmpleadoArchivo extends MitivModel
{
    protected $table = 'empleado_archivos';

    public const METADATOS = ['id', 'empleado_id', 'nombre', 'descripcion', 'tamanio', 'mimetype', 'habilitado', 'created_at'];

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('sin_contenido', function (Builder $query) {
            $query->select(array_map(fn ($c) => 'empleado_archivos.'.$c, self::METADATOS));
        });
    }

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'habilitado' => 'boolean'];
    }
}
