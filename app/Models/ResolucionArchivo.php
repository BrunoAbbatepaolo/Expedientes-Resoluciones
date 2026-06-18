<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolucionArchivo extends Model
{
    protected $table = 'resolucion_archivos';

    protected $fillable = [
        'resolucion_id',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'tipo',
        'tamano',
    ];

    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(Resolucion::class);
    }
}
